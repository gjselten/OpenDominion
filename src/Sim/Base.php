<?php

namespace OpenDominion\Sim;

use OpenDominion\Models\Round;
use OpenDominion\Models\Dominion;

use OpenDominion\Services\Dominion\TickService;
use OpenDominion\Services\Dominion\QueueService;

use OpenDominion\Services\Dominion\Actions\DailyBonusesActionService;
use OpenDominion\Services\Dominion\Actions\ReleaseActionService;
use OpenDominion\Services\Dominion\Actions\SpellActionService;
use OpenDominion\Services\Dominion\Actions\ExploreActionService;
use OpenDominion\Services\Dominion\Actions\ConstructActionService;
use OpenDominion\Services\Dominion\Actions\ImproveActionService;
use OpenDominion\Services\Dominion\Actions\BankActionService;
use OpenDominion\Services\Dominion\Actions\Military\TrainActionService;
use OpenDominion\Services\Dominion\Actions\TechActionService;
use OpenDominion\Services\Dominion\Actions\RezoneActionService;
use OpenDominion\Services\Dominion\Actions\DestroyActionService;

use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\Actions\TrainingCalculator;
use OpenDominion\Calculators\Dominion\Actions\ExplorationCalculator;
use OpenDominion\Calculators\Dominion\Actions\ConstructionCalculator;
use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\PopulationCalculator;
use OpenDominion\Calculators\Dominion\ImprovementCalculator;

use GameException;
use Exception;

use DB;

class Base
{

  public function __construct() {
    DB::disableQueryLog();
    DB::connection()->disableQueryLog();

    set_time_limit(500);

    print "START OF SIM<br /><br />";

    $this->round = Round::firstOrFail();
    $this->dominion = $this->createOopDom();
    $this->ticker = new TickService();
    $id = $this->dominion->id;

    $this->queueService = app(QueueService::class);
    $this->dailyBonusesActionService = app(DailyBonusesActionService::class);
    $this->militaryTrainActionService = app(TrainActionService::class);
    $this->releaseActionService = app(ReleaseActionService::class);
    $this->spellActionService = app(SpellActionService::class);
    $this->exploreActionService = app(ExploreActionService::class);
    $this->constructionActionService = app(ConstructActionService::class);
    $this->improveActionService = app(ImproveActionService::class);
    $this->techActionService = app(TechActionService::class);
    $this->rezoneActionService = app(RezoneActionService::class);
    $this->destroyActionService = app(DestroyActionService::class);
    $this->bankActionService = app(BankActionService::class);


    $this->improvementCalculator = app(ImprovementCalculator::class);
    $this->trainingCalculator = app(TrainingCalculator::class);
    $this->explorationCalculator = app(ExplorationCalculator::class);
    $this->constructionCalculator = app(ConstructionCalculator::class);
    $this->landCalculator = app(LandCalculator::class);
    $this->populationCalculator = app(PopulationCalculator::class);

    $this->militaryCalculator = app(MilitaryCalculator::class);

    $ref = new \ReflectionClass($this);
    $dir = dirname($ref->getFilename());
    $filename = $dir . '/' . 'log.txt';
    $this->logfile = fopen($filename, "w");
    fwrite($this->logfile, 'Started sim ' . get_class($this) . " at " . now() . "\n");
  }

  public function runSim() {
    for($tick = 0; $tick < $this->ticks_to_run(); $tick++) {
      print "tick $tick: platinum: {$this->dominion->resource_platinum} <br />";

      $this->setup($tick);
      $this->castSpells($tick);
      $this->takeLandBonus($tick);
      $this->takePlatinumBonus($tick);
      $this->destroy($tick);
      $this->tech($tick);
      $this->build($tick);
      $this->train($tick);
      $this->explore($tick);
      $this->invest($tick);
      $this->warnings($tick);
      $this->release($tick);

      if($tick % 12 === 0) {
        $this->showState($tick);
      }

      $this->tick($tick);
    }

    print "DONE<br /><br />";
    $this->castSpells($tick);
    $this->showState($tick);

    return $this->dominion;
  }

  function get_buildings_to_construct($tick, $max_afford) {
    throw new Exception("Child classes must implement get_buildings_to_construct()");
  }
  function get_units_to_train($tick) {
    throw new Exception("Child classes must implement get_units_to_train()");
  }
  function get_acres_to_explore($tick, $max_afford) {
    throw new Exception("Child classes must implement get_acres_to_explore()");
  }
  function invest_in_improvements($tick) {
    throw new Exception("Child classes must implement invest_in_improvements()");
  }
  function pick_tech($tick) {
    throw new Exception("Child classes must implement pick_tech()");
  }
  function createOopDom() {
    throw new Exception("Child classes must implement createOopDom");
  }
  function get_self_spells_to_cast($tick) {
    throw new Exception("Child classes must implement get_self_spells_to_cast()");
  }
  function get_incoming_acres_by_landtype() {
    throw new Exception("Child classes must implement get_incoming_acres_by_landtype()");
  }
  function get_incoming_buildings() {
    throw new Exception("Child classes must implement get_incoming_buildings()");
  }

  function warnings($tick) {
    if($this->dominion->military_draftees <= 10) {
      print "WARNING (tick $tick): OUT OF DRAFTEES. DRAFTEES: {$this->dominion->military_draftees}. PLATINUM: {$this->dominion->resource_platinum}<br />";
    }

    $barren = array_sum($this->landCalculator->getBarrenLandByLandType($this->dominion));
    if($barren > array_sum($this->buildings_to_build)) {
      print "WARNING (tick $tick): $barren BARREN ACRES. LUMBER: {$this->dominion->resource_lumber}. " . print_r($this->landCalculator->getBarrenLandByLandType($this->dominion), true) . "<br />";
    }

    if($this->dominion->resource_food <= 1000) {
      print "WARNING (tick $tick): (ALMOST) OUT OF FOOD. FOOD: {$this->dominion->resource_food}<br />";
      print "FOOD TOO LOW. QUITTING SIM.";
      exit();
    }

    $draftrate = $this->populationCalculator->getPopulationMilitaryPercentage($this->dominion, 2);
    if($draftrate > 85) {
      print "WARNING (tick $tick): HIGH DRAFTRATE: $draftrate<br />";
    }

    $employment = round($this->populationCalculator->getEmploymentPercentage($this->dominion), 1);
    if($employment < 95) {
      print "WARNING (tick $tick): SEVERE UNEMPLOYMENT $employment<br />";
    }

    if($this->dominion->resource_platinum > 400000) {
      print "WARNING (tick $tick): PLAT UNSPEND: {$this->dominion->resource_platinum}<br />";
    }
  }

  function takeLandBonus($tick) {
    if ($tick % 24 !== 0) {
      return;
    }

    try {
      print "tick $tick: taking daily land bonus<br />";
      $result = $this->dailyBonusesActionService->claimLand($this->dominion);
    } catch (Exception $e) {
      print "ERROR: TAKING DAILY LAND BONUS FAILED: " . $e->getMessage();
      exit();
    }
  }

  function takePlatinumBonus($tick) {
    if ((($tick+1) % 24) !== 0) {
      return;
    }

    try {
      print "tick $tick: daily platinum bonus: taking daily platinum bonus\n";
      $result = $this->dailyBonusesActionService->claimPlatinum($this->dominion);
    } catch (Exception $e) {
      print "ERROR: TAKING DAILY PLATINUM BONUS FAILED: " . $e->getMessage();
      exit();
    }
  }

  function destroy($tick) {
    return;
  }

  function castSpells($tick) {
    // $spellCalculator->getActiveSpells($dominion);
    if($tick % 12 !== 0) {
      // quicker than actually checking if spell is active)
      return;
    }

    // print "spells: casting spells\n";

    $spells = $this->get_self_spells_to_cast($tick);
    try {
      foreach($spells as $spell)
        $result = $this->spellActionService->castSpell(
            $this->dominion,
            $spell,
            null
        );

    } catch (Exception $e) {
        print "ERROR: CASTING SPELL FAILED: " . $e->getMessage();
        exit();
    }
  }

  function tech($tick) {
    $selected_tech = $this->pick_tech($tick);

    try {
      if($selected_tech !== null) {
        $result = $this->techActionService->unlock(
            $this->dominion,
            $selected_tech
        );
      }
    }
    catch(Exception $e) {
      print "ERROR: GETTING TECH: " . $e->getMessage() . "SELECTED TECH: $selected_tech";
      exit();
    }
  }

  function build($tick) {
    $max_afford = $this->constructionCalculator->getMaxAfford($this->dominion);
    $this->buildings_to_build = $this->get_buildings_to_construct($tick, $max_afford);
    $nr_to_build = array_sum($this->buildings_to_build);

    if($nr_to_build === 0) {
      // print "build: building nothing.<br />";
      return;
    }

    print "tick $tick: build: " . print_r(array_filter($this->buildings_to_build), true) . "<br />";
    try {
      $result = $this->constructionActionService->construct($this->dominion, $this->buildings_to_build);
    } catch(Exception $e) {
      print "CONSTRUCTION FAILED: {$e->getMessage()}" . print_r($this->buildings_to_build, true) . print_r($this->landCalculator->getBarrenLandByLandType($this->dominion), true);
      exit();
    }
  }

  function train($tick) {
    $military_to_train = $this->get_units_to_train($tick);

    $nr_to_train = array_sum($military_to_train);
    if($nr_to_train === 0) {
      // print "train: training nothing.<br />";
      return;
    }

    try {
      print "tick $tick: training " . print_r(array_filter($military_to_train), true) . "<br />";
      $result = $this->militaryTrainActionService->train($this->dominion, $military_to_train);
    }
    catch(Exception $e) {
      print "ERROR: TRAINING FAILED: " . $e->getMessage();
      print_r($military_to_train);
      exit();
    }
  }

  function explore($tick) {
    $max_afford = $this->explorationCalculator->getMaxAfford($this->dominion);

    if($max_afford <= 0) {
      // print "explore: no resources available to explore<br />";
      return;
    }

    $acres_to_explore = $this->get_acres_to_explore($tick, $max_afford);

    if(array_sum($acres_to_explore) == 0) {
      return;
    }

    try {
      print "tick $tick: explore: explore {$max_afford} acres." . print_r(array_filter($acres_to_explore), true) . "<br />";
      $result = $this->exploreActionService->explore($this->dominion, $acres_to_explore);
    } catch(Exception $e) {
      print "ERROR: EXPLORING FAILED IN TICK $tick: " . $e->getMessage();
      print_r($acres_to_explore);
      exit();
    }
  }

  function invest($tick) {
    $investment = $this->get_investment_into_caste($tick);

    if(array_sum($investment) === 0) {
      return;
    }

    try {
        // print "tick $tick: investment: " . print_r(array_filter($investment), true) . "<br />";
        $result = $this->improveActionService->improve(
            $this->dominion,
            'gems',
            $investment
        );
    } catch (Exception $e) {
      print "ERROR: INVESTING FAILED: {$e->getMessage()}." . print_r($investment, true) . "<br />";
      exit();
    }
  }


  function release($tick) {
    $draftees_to_release = $this->dominion->military_draftees;
    if($draftees_to_release <= 0) {
      // print "release: no draftees to release.\n";
      return;
    }

    // print "release: release all ({$draftees_to_release}) draftees.\n";
    try {
      print "tick $tick: release: $draftees_to_release draftees<br />";
      $result = $this->releaseActionService->release($this->dominion, ['draftees' => $draftees_to_release]);
    } catch(Exception $e) {
      print "ERROR: RELEASING FAILED: {$e->getMessage()}\n";
      exit();
    }
  }

  function tick($tick) {
    if ($tick % 24 === 0) {
      $this->dominion->update([
          'daily_platinum' => false,
          'daily_land' => false,
      ], [
          'event' => 'tick',
      ]);
    }
    $this->ticker->performTick($this->round, $this->dominion);
  }

  function specDp() {
    return 3;
  }
  function eliteDp() {
    return 6;
  }
  function specOp() {
    return 3;
  }
  function eliteOp() {
    return 6;
  }

  function showState($tick) {

    $dp_elites = $this->dominion->military_unit3 + $this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit3");
    $dp_specs = $this->dominion->military_unit2 + $this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit2");
    $dp_mods = round($this->militaryCalculator->getDefensivePowerMultiplier($this->dominion) * 100 - 100, 2);
    $raw_dp = round($dp_elites * $this->eliteDp() + $dp_specs * $this->specDp());
    $mod_dp = round($raw_dp * (1 + $dp_mods / 100));

    $op_elites = $this->dominion->military_unit4 + $this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit4");
    $op_specs = $this->dominion->military_unit1 + $this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit1");
    $op_mods = round($this->militaryCalculator->getOffensivePowerMultiplier($this->dominion) * 100 - 100, 2);
    $raw_op = round($op_elites * $this->eliteOp() + $op_specs * $this->specOp());
    $mod_op = round($raw_op * (1 + $op_mods / 100));

    $incoming_buildings = $this->get_incoming_buildings();
    $incoming_land = $this->get_incoming_acres_by_landtype();
    $science = $this->improvementCalculator->getImprovementMultiplierBonus($this->dominion, 'science') * 100;
    $keep = $this->improvementCalculator->getImprovementMultiplierBonus($this->dominion, 'keep') * 100;
    $walls = $this->improvementCalculator->getImprovementMultiplierBonus($this->dominion, 'walls') * 100;
    $forges = $this->improvementCalculator->getImprovementMultiplierBonus($this->dominion, 'forges') * 100;
    $day = floor(($tick + 72) / 24) + 1;
    $hour = $tick % 24;
    $unlocked_techs = $this->dominion->techs->pluck('name')->all();

    print "
    <div style='border: 1px solid #000000; margin-top: 10px;'>
      <h3>Overview - tick {$tick} (Day $day Hour $hour)</h3>
      <table>
      <tr>
      <td style='vertical-align: top; padding-right: 10px;'>
        <table>
          <tr><td>Land</td><td>{$this->landCalculator->getTotalLand($this->dominion)}</td></tr>
          <tr><td>Peasants</td><td>{$this->dominion->peasants}</td></tr>
          <tr><td>Jobs</td><td> {$this->populationCalculator->getEmploymentJobs($this->dominion)}</td></tr>
          <tr><td>Employment</td><td>" . round($this->populationCalculator->getEmploymentPercentage($this->dominion), 2) . "%</td></tr>
          <tr><td></td><td></td></tr>
          <tr><td>Platinum</td><td>{$this->dominion->resource_platinum}</td></tr>
          <tr><td>Food</td><td>{$this->dominion->resource_food}</td></tr>
          <tr><td>Lumber</td><td>{$this->dominion->resource_lumber}</td></tr>
          <tr><td>Ore</td><td>{$this->dominion->resource_ore}</td></tr>
          <tr><td>Mana</td><td>{$this->dominion->resource_mana}</td></tr>
          <tr><td>Research Points</td><td>{$this->dominion->resource_tech}</td></tr>
        </table>
      </td>
      <td style='vertical-align: top; padding-right: 10px;'>
        <table>
          <tr><td>DP</td><td>{$mod_dp}</td></tr>
          <tr><td>OP</td><td>{$mod_op}</td></tr>
          <tr><td>DP mods</td><td>{$dp_mods}%</td></tr>
          <tr><td>OP mods</td><td>{$op_mods}%</td></tr>
          <tr><td>OP raw</td><td>{$raw_op}</td></tr>
          <tr><td>Draftees</td><td>{$this->dominion->military_draftees}</td></tr>
          <tr><td>Unit 2</td><td>{$this->dominion->military_unit2} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit2")})</td></tr>
          <tr><td>Unit 3</td><td>{$this->dominion->military_unit3} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit3")})</td></tr>
          <tr><td>Unit 4</td><td>{$this->dominion->military_unit4} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit4")})</td></tr>
          <tr><td>Wizards</td><td>{$this->dominion->military_wizards} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_wizards")})</td></tr>
          <tr><td>Archmages</td><td>{$this->dominion->military_archmages} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_archmages")})</td></tr>
          <tr><td>Draftrate</td><td>" . round($this->populationCalculator->getPopulationMilitaryPercentage($this->dominion, 2), 1) . "%</td></tr>
        </table>
      </td>
      <td style='vertical-align: top; padding-right: 10px;'>
        <table>
          <tr><td>Homes</td><td>{$this->dominion->building_home}</td><td>" . round($this->dominion->building_home / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_home']}</td></tr>
          <tr><td>Alchs</td><td>{$this->dominion->building_alchemy}</td><td>" . round($this->dominion->building_alchemy / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_alchemy']}</td></tr>
          <tr><td>Farms</td><td>{$this->dominion->building_farm}</td><td>" . round($this->dominion->building_farm / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_farm']}</td></tr>
          <tr><td>Smithies</td><td>{$this->dominion->building_smithy}</td><td>" . round($this->dominion->building_smithy / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_smithy']}</td></tr>
          <tr><td>Masons</td><td>{$this->dominion->building_masonry}</td><td>" . round($this->dominion->building_masonry / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_masonry']}</td></tr>
          <tr><td>OM</td><td>{$this->dominion->building_ore_mine}</td><td>" . round($this->dominion->building_ore_mine / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_ore_mine']}</td></tr>
          <tr><td>GN</td><td>{$this->dominion->building_gryphon_nest}</td><td>" . round($this->dominion->building_gryphon_nest / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_gryphon_nest']}</td></tr>
          <tr><td>Towers</td><td>{$this->dominion->building_tower}</td><td>" . round($this->dominion->building_tower / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_tower']}</td></tr>
          <tr><td>WG</td><td>{$this->dominion->building_wizard_guild}</td><td>" . round($this->dominion->building_wizard_guild / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_wizard_guild']}</td></tr>
          <tr><td>Temples</td><td>{$this->dominion->building_temple}</td><td>" . round($this->dominion->building_temple / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_temple']}</td></tr>
          <tr><td>DM</td><td>{$this->dominion->building_diamond_mine}</td><td>" . round($this->dominion->building_diamond_mine / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_diamond_mine']}</td></tr>
          <tr><td>Schools</td><td>{$this->dominion->building_school}</td><td>" . round($this->dominion->building_school / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_school']}</td></tr>
          <tr><td>LY</td><td>{$this->dominion->building_lumberyard}</td><td>" . round($this->dominion->building_lumberyard / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_lumberyard']}</td></tr>
          <tr><td>FH</td><td>{$this->dominion->building_forest_haven}</td><td>" . round($this->dominion->building_forest_haven / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_forest_haven']}</td></tr>
          <tr><td>Facts</td><td>{$this->dominion->building_factory}</td><td>" . round($this->dominion->building_factory / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_factory']}</td></tr>
          <tr><td>GT</td><td>{$this->dominion->building_guard_tower}</td><td>" . round($this->dominion->building_guard_tower / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_guard_tower']}</td></tr>
          <tr><td>Shrines</td><td>{$this->dominion->building_shrine}</td><td>" . round($this->dominion->building_shrine / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_shrine']}</td></tr>
          <tr><td>Barracks</td><td>{$this->dominion->building_barracks}</td><td>" . round($this->dominion->building_barracks / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_barracks']}</td></tr>
          <tr><td>Docks</td><td>{$this->dominion->building_dock}</td><td>" . round($this->dominion->building_dock / $this->landCalculator->getTotalLand($this->dominion) * 100, 2) . "%</td><td> {$incoming_buildings['building_dock']}</td></tr>
        </table>
      </td>
      <td style='vertical-align: top;'>
        <table>
        <tr><td>Plain</td><td>{$incoming_land['land_plain']}</td></tr>
        <tr><td>Mountain</td><td>{$incoming_land['land_mountain']}</td></tr>
        <tr><td>Swamp</td><td>{$incoming_land['land_swamp']}</td></tr>
        <tr><td>Cavern</td><td>{$incoming_land['land_cavern']}</td></tr>
        <tr><td>Forest</td><td>{$incoming_land['land_forest']}</td></tr>
        <tr><td>Hill</td><td>{$incoming_land['land_hill']}</td></tr>
        <tr><td>Water</td><td>{$incoming_land['land_water']}</td></tr>
        </table>
      </td>
      <td style='vertical-align: top;'>
        <table>
          <tr><td>Science</td><td>{$science}%</td><td>({$this->dominion->improvement_science})</td></tr>
          <tr><td>Keep</td><td>{$keep}%</td><td>({$this->dominion->improvement_keep})</td></tr>
          <tr><td>Walls</td><td>{$walls}%</td><td>({$this->dominion->improvement_walls})</td></tr>
          <tr><td>Forges</td><td>{$forges}%</td><td>({$this->dominion->improvement_forges})</td></tr>
        </table>
      </td>
      <td style='vertical-align: top;'>";

      foreach($unlocked_techs as $tech) {
        print $tech . '<br />';
      }

    print "</td>
      </tr>
      </table>
    </div>
    ";

    $this->logState($tick, $mod_op, $mod_dp);
  }

  function logState($tick, $op, $dp) {
    $day = floor(($tick + 72) / 24) + 1;
    $hour = $tick % 24;
    $acres = $this->landCalculator->getTotalLand($this->dominion);
    $message = "Tick $tick (day $day hr $hour): $acres\t\t\t op: $op;\tdp: $dp;\tu1: {$this->dominion->military_unit1};\tu2: {$this->dominion->military_unit2} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit2")});\tu3: {$this->dominion->military_unit3} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit3")});\tu4: {$this->dominion->military_unit4} ({$this->queueService->getTrainingQueueTotalByResource($this->dominion, "military_unit4")});\tpeas: {$this->dominion->peasants}";
    fwrite($this->logfile,  $message . "\n");
  }
}
