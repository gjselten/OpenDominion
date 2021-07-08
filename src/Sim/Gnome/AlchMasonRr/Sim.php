<?php

namespace OpenDominion\Sim\Gnome\AlchMasonRr;

use OpenDominion\Models\User;
use OpenDominion\Models\Dominion;

use OpenDominion\Sim\Base;
use OpenDominion\Sim\Gnome\AlchMasonRr\BuildingStrategy;
use OpenDominion\Sim\Gnome\AlchMasonRr\TrainingStrategy;
use OpenDominion\Sim\Gnome\AlchMasonRr\ImprovementStrategy;
use OpenDominion\Sim\Gnome\AlchMasonRr\TechStrategy;
use OpenDominion\Sim\BaseTechStrategy;

class Sim extends Base
{
  function ticks_to_run() {
    return 24 * 25;
  }

  function setup($tick) {
    $this->buildingStrategy = new BuildingStrategy($this->dominion, $this->queueService);
    $this->trainingStrategy = new TrainingStrategy($this->dominion, 3, $this->eliteDp(), $this->queueService, $this->militaryCalculator, $this->trainingCalculator);
    $this->improvementStrategy =  new ImprovementStrategy();
    $this->tech_strategy = new BaseTechStrategy();
  }

  function get_buildings_to_construct($tick, $max_afford) {
      if($tick == 23 || $tick == 107) {
        // QUICKFIX HACK. GET SOME LUMBER GOING
        $result = $this->bankActionService->exchange(
            $this->dominion,
            'resource_ore',
            'resource_lumber',
            $this->dominion->resource_ore
        );
      }
    return $this->buildingStrategy->get_buildings_to_build($this->dominion, $tick, $max_afford, $this->queueService);
  }

  function get_units_to_train($tick) {
    return $this->trainingStrategy->get_units_to_train($this->dominion, $tick);
  }

  function get_acres_to_explore($tick, $max_afford) {
    return $this->buildingStrategy->get_land_types_to_explore($this->dominion, $tick, $max_afford);
  }

  function get_investment_into_caste($tick) {
    return $this->improvementStrategy->get_investment_to_do($this->dominion, $tick, $this->dominion->resource_ore, $this->improvementCalculator);
  }

  function pick_tech($tick) {
    return $this->tech_strategy->select_tech($this->dominion, $tick);
  }

  function get_self_spells_to_cast($tick) {
    return ['midas_touch', 'gaias_watch', 'ares_call', 'miners_sight'];
  }

  function get_incoming_acres_by_landtype() {
    return $this->buildingStrategy->get_incoming_acres();
  }

  function get_incoming_buildings() {
    return $this->buildingStrategy->get_incoming_buildings();
  }

  function takeLandBonus($tick) {
    if ($tick % 24 !== 0 || $tick == 0) {
      return;
    }

    try {
      // print "daily land bonus: taking daily land bonus\n";
      $result = $this->dailyBonusesActionService->claimLand($this->dominion);
    } catch (Exception $e) {
      print "ERROR: TAKING DAILY LAND BONUS FAILED: " . $e->getMessage();
      exit();
    }
  }

  function destroy($tick) {
    if($tick == 432) {
      $result = $this->destroyActionService->destroy($this->dominion, ['alchemy' => 67]);
      print "tick $tick: destroyed 150 alchs<br />";
    }
    if($tick == 442) {
      $result = $this->destroyActionService->destroy($this->dominion, ['alchemy' => 67]);
      print "tick $tick: destroyed 86 alchs<br />";
    }

    // if($tick == 440) {
    //   $result = $this->destroyActionService->destroy($this->dominion, ['ore_mine' => 100]);
    // }
  }

  function release($tick) {
    if($tick == 150) {
      try {
        $result = $this->releaseActionService->release($this->dominion, ['unit2' => 850]);
      } catch(Exception $e) {
        print "ERROR: RELEASING FAILED: {$e->getMessage()}\n";
        exit();
      }
    }

    $draftees_to_release = $this->dominion->military_draftees;
    if($draftees_to_release <= 0) {
      // print "release: no draftees to release.\n";
      return;
    }

    // print "release: release all ({$draftees_to_release}) draftees.\n";
    try {
      // print "tick $tick: release: $draftees_to_release draftees<br />";
      $result = $this->releaseActionService->release($this->dominion, ['draftees' => $draftees_to_release]);
    } catch(Exception $e) {
      print "ERROR: RELEASING FAILED: {$e->getMessage()}\n";
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
            'ore',
            $investment
        );
    } catch (Exception $e) {
      print "ERROR: INVESTING FAILED: {$e->getMessage()}." . print_r($investment, true) . "<br />";
      exit();
    }
  }

  function specDp() {
    return 3;
  }
  function eliteDp() {
    return $this->militaryCalculator->getUnitPowerWithPerks($this->dominion, null, null, $this->dominion->race->units[2], 'defense');
  }
  function specOp() {
    return 4;
  }
  function eliteOp() {
    return 6.5;
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
    $race_id = 4;

    return Dominion::create([
      'user_id' => $user_id,
      'round_id' => $round_id,
      'realm_id' => $realm_id,
      'race_id' => $race_id,
      'pack_id' => null,

      'ruler_name' => 'ruler' . rand(0,999999999),
      'name' => 'domname'  . rand(0,999999999),
      'prestige' => 250,

      'peasants' => 9181,
      'peasants_last_hour' => 0,

      'draft_rate' => 90,
      'morale' => 100,
      'spy_strength' => 100,
      'wizard_strength' => 100,

      'resource_platinum' => 205378,
      'resource_food' => 50000,
      'resource_lumber' => 2315,
      'resource_mana' => 10000,
      'resource_ore' => 185000,
      'resource_gems' => 0,
      'resource_tech' => 0,
      'resource_boats' => 0,

      'improvement_science' => 0,
      'improvement_keep' => 0,
      'improvement_towers' => 0,
      'improvement_forges' => 0,
      'improvement_walls' => 0,
      'improvement_harbor' => 0,

      'military_draftees' => 434,
      'military_unit1' => 0,
      'military_unit2' => 950,
      'military_unit3' => 580,
      'military_unit4' => 0,
      'military_spies' => 0,
      'military_wizards' => 0,
      'military_archmages' => 0,

      'land_plain' => 296,
      'land_mountain' => 415,
      'land_swamp' => 41,
      'land_cavern' => 0,
      'land_forest' => 20,
      'land_hill' => 0,
      'land_water' => 0,

      'building_home' => 31,
      'building_alchemy' => 134,
      'building_farm' => 27,
      'building_smithy' => 135,
      'building_masonry' => 0,
      'building_ore_mine' => 384,
      'building_gryphon_nest' => 0,
      'building_tower' => 41,
      'building_wizard_guild' => 0,
      'building_temple' => 0,
      'building_diamond_mine' => 0,
      'building_school' => 0,
      'building_lumberyard' => 20,
      'building_forest_haven' => 0,
      'building_factory' => 0,
      'building_guard_tower' => 0,
      'building_shrine' => 0,
      'building_barracks' => 0,
      'building_dock' => 0,

      'protection_ticks_remaining' => 0,
    ]);
  }
}
