<?php

namespace OpenDominion\Sim\Merfolk\MasonsSchoolsRr;

use OpenDominion\Models\User;
use OpenDominion\Models\Dominion;

use OpenDominion\Sim\Base;
use OpenDominion\Sim\Merfolk\MasonsSchoolsRr\BuildingStrategy;
use OpenDominion\Sim\Merfolk\MasonsSchoolsRr\TrainingStrategy;
use OpenDominion\Sim\Merfolk\MasonsSchoolsRr\ImprovementStrategy;
use OpenDominion\Sim\BaseTechStrategy;

class Sim extends Base
{
  function ticks_to_run() {
    return 24 * 46;
  }

  function setup($tick) {
    $this->buildingStrategy = new BuildingStrategy($this->dominion, $this->queueService);
    $this->trainingStrategy = new TrainingStrategy($this->dominion, 3, 7, $this->queueService, $this->militaryCalculator, $this->trainingCalculator);
    $this->improvementStrategy =  new ImprovementStrategy();
    $this->tech_strategy = new BaseTechStrategy();
  }

  function get_buildings_to_construct($tick, $max_afford) {
    return $this->buildingStrategy->get_buildings_to_build($this->dominion, $tick, $max_afford, $this->queueService);
  }

  function get_units_to_train($tick) {
    return $this->trainingStrategy->get_units_to_train($this->dominion, $tick);
  }

  function get_acres_to_explore($tick, $max_afford) {
    return $this->buildingStrategy->get_land_types_to_explore($this->dominion, $tick, $max_afford);
  }

  function get_investment_into_caste($tick) {
    return $this->improvementStrategy->get_investment_to_do($this->dominion, $tick, $this->dominion->resource_gems, $this->improvementCalculator);
  }

  function pick_tech($tick) {
    return $this->tech_strategy->select_tech($this->dominion, $tick);
  }

  function get_self_spells_to_cast($tick) {
    return ['midas_touch', 'gaias_watch', 'ares_call'];
  }

  function get_incoming_acres_by_landtype() {
    return $this->buildingStrategy->get_incoming_acres();
  }

  function get_incoming_buildings() {
    return $this->buildingStrategy->get_incoming_buildings();
  }

  function takeLandBonus($tick) {
    if ($tick % 24 !== 0) {
      return;
    }

    try {
      // print "daily land bonus: taking daily land bonus\n";
      $result = $this->dailyBonusesActionService->claimLand($this->dominion);
    } catch (Exception $e) {
      print "ERROR: TAKING DAILY LAND BONUS FAILED: " . $e->getMessage();
      exit();
    }

    try {
      if(($this->dominion->building_home + $this->buildingStrategy->get_incoming_buildings()['building_home']) >= 250) {
        $result = $this->rezoneActionService->rezone(
            $this->dominion,
            ['water' => 20],
            ['plain' => 20]
        );
      }
    } catch(Exception $e) {
      print "ERROR: TAKING DAILY LAND BONUS REZONING FAILED: " . $e->getMessage();
      exit();
    }
  }


  function release($tick) {
    if($tick <= 72) {
      return;
    }

    $draftees_to_release = $this->dominion->military_draftees;
    if($draftees_to_release <= 0) {
      // print "release: no draftees to release.\n";
      return;
    }

    // print "release: release all ({$draftees_to_release}) draftees.\n";
    try {
      $result = $this->releaseActionService->release($this->dominion, ['draftees' => $draftees_to_release]);
    } catch(Exception $e) {
      print "ERROR: RELEASING FAILED: {$e->getMessage()}\n";
      exit();
    }
  }

  function destroy($tick) {
    if($this->dominion->building_school === 0) {
      return;
    }

    $unlocked_techs = $this->dominion->techs->pluck('key')->all();
    // print 'UNLOCKED TECHS: ' . print_r($unlocked_techs, true) . '<br />';
    if(in_array('tech_15_5', $unlocked_techs)) { // -7.5% explore cost
      try {
        $nr_schools = $this->dominion->building_school;
        $result = $this->destroyActionService->destroy($this->dominion, ['school' => $nr_schools]);
        // $result = $this->rezoneActionService->rezone(
        //     $this->dominion,
        //     ['cavern' => $nr_schools],
        //     ['plain' => $nr_schools]
        // );
      } catch (Exception $e) {
        print "DESTROYING SCHOOL ERROR: " . $e->getMessage();
        exit();
      }
    }
  }

  function specDp() {
    return 3;
  }
  function eliteDp() {
    return 7;
  }
  function specOp() {
    return 3;
  }
  function eliteOp() {
    return 4;
  }

  function createOopDom() {
    $user = User::create([
        'email' => "email" . rand(0,999999999) . "@example.com",
        'password' => 'abcdef',
        'display_name' => 'Dev User',
        'activated' => true,
        'activation_code' => str_random(),
    ]);

    $user_id = $user->id;
    $round_id = 1;
    $realm_id = 2;
    $race_id = 12;

    return Dominion::create([
      'user_id' => $user_id,
      'round_id' => $round_id,
      'realm_id' => $realm_id,
      'race_id' => $race_id,
      'pack_id' => null,

      'ruler_name' => 'ruler' . rand(0,999999999),
      'name' => 'domname'  . rand(0,999999999),
      'prestige' => 250,

      'peasants' => 9752,
      'peasants_last_hour' => 0,

      'draft_rate' => 90,
      'morale' => 100,
      'spy_strength' => 100,
      'wizard_strength' => 100,

      'resource_platinum' => 462073,
      'resource_food' => 8871,
      'resource_lumber' => 14888,
      'resource_mana' => 10000,
      'resource_ore' => 0,
      'resource_gems' => 22740,
      'resource_tech' => 0,
      'resource_boats' => 0,

      'improvement_science' => 0,
      'improvement_keep' => 0,
      'improvement_towers' => 0,
      'improvement_forges' => 0,
      'improvement_walls' => 0,
      'improvement_harbor' => 0,

      'military_draftees' => 946,
      'military_unit1' => 0,
      'military_unit2' => 1250,
      'military_unit3' => 275,
      'military_unit4' => 0,
      'military_spies' => 0,
      'military_wizards' => 0,
      'military_archmages' => 0,

      'land_plain' => 395,
      'land_mountain' => 0,
      'land_swamp' => 24,
      'land_cavern' => 167,
      'land_forest' => 32,
      'land_hill' => 157,
      'land_water' => 10,

      'building_home' => 10,
      'building_alchemy' => 239,
      'building_farm' => 38,
      'building_smithy' => 118,
      'building_masonry' => 0,
      'building_ore_mine' => 0,
      'building_gryphon_nest' => 0,
      'building_tower' => 24,
      'building_wizard_guild' => 0,
      'building_temple' => 0,
      'building_diamond_mine' => 67,
      'building_school' => 100,
      'building_lumberyard' => 32,
      'building_forest_haven' => 0,
      'building_factory' => 107,
      'building_guard_tower' => 50,
      'building_shrine' => 0,
      'building_barracks' => 0,
      'building_dock' => 0,

      'protection_ticks_remaining' => 0,
    ]);
  }
}