<?php

namespace OpenDominion\Sim\Human\Converter\Base;

use OpenDominion\Models\User;
use OpenDominion\Models\Dominion;

use OpenDominion\Sim\Base;
use OpenDominion\Sim\Human\Converter\Base\BuildingStrategy;
use OpenDominion\Sim\Human\Converter\Base\TrainingStrategy;
use OpenDominion\Sim\Human\Converter\Base\ImprovementStrategy;
use OpenDominion\Sim\BaseTechStrategy;

class Sim extends Base
{
  function ticks_to_run() {
    return 24 * 25;
  }

  function setup($tick) {
    $this->buildingStrategy = new BuildingStrategy($this->dominion, $this->queueService);
    $this->trainingStrategy = new TrainingStrategy($this->dominion, 3, 6, $this->queueService, $this->militaryCalculator, $this->trainingCalculator);
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
    return ['midas_touch', 'gaias_watch', 'ares_call', 'mining_strength'];
  }

  function get_incoming_acres_by_landtype() {
    return $this->buildingStrategy->get_incoming_acres();
  }

  function get_incoming_buildings() {
    return $this->buildingStrategy->get_incoming_buildings();
  }
  //
  // function destroy($tick) {
  //   if($tick == 409 || $tick == 410) {
  //     // QUICKFIX HACK. GET SOME LUMBER GOING
  //     $result = $this->bankActionService->exchange(
  //         $this->dominion,
  //         'resource_ore',
  //         'resource_lumber',
  //         $this->dominion->resource_ore
  //     );
  //   }
  //   if($tick !== 408) {
  //     return;
  //   }
  //
  //   try {
  //     $result = $this->destroyActionService->destroy($this->dominion, ['factory' => 109]);
  //     $result = $this->rezoneActionService->rezone(
  //         $this->dominion,
  //         ['hill' => 109],
  //         ['mountain' => 109]
  //     );
  //
  //     $result = $this->bankActionService->exchange(
  //         $this->dominion,
  //         'resource_ore',
  //         'resource_lumber',
  //         $this->dominion->resource_ore
  //     );
  //   } catch (Exception $e) {
  //     print "DESTROYING ORE MINES ERROR: " . $e->getMessage();
  //     exit();
  //   }
  // }

  function release($tick) {
    // if($tick <= 72) {
    //   return;
    // }

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
    $race_id = 7;

    return Dominion::create([
      'user_id' => $user_id,
      'round_id' => $round_id,
      'realm_id' => $realm_id,
      'race_id' => $race_id,
      'pack_id' => null,

      'ruler_name' => 'ruler' . rand(0,999999999),
      'name' => 'domname'  . rand(0,999999999),
      'prestige' => 250,

      'peasants' => 11491,
      'peasants_last_hour' => 0,

      'draft_rate' => 90,
      'morale' => 100,
      'spy_strength' => 100,
      'wizard_strength' => 100,

      'resource_platinum' => 431296,
      'resource_food' => 2036,
      'resource_lumber' => 6215,
      'resource_mana' => 10000,
      'resource_ore' => 50000,
      'resource_gems' => 20910,
      'resource_tech' => 0,
      'resource_boats' => 0,

      'improvement_science' => 0,
      'improvement_keep' => 0,
      'improvement_towers' => 0,
      'improvement_forges' => 0,
      'improvement_walls' => 0,
      'improvement_harbor' => 0,

      'military_draftees' => 1040,
      'military_unit1' => 0,
      'military_unit2' => 1164,
      'military_unit3' => 425,
      'military_unit4' => 0,
      'military_spies' => 0,
      'military_wizards' => 0,
      'military_archmages' => 0,

      'land_plain' => 375,
      'land_mountain' => 37,
      'land_swamp' => 52,
      'land_cavern' => 145,
      'land_forest' => 23,
      'land_hill' => 150,
      'land_water' => 0,

      'building_home' => 14,
      'building_alchemy' => 182,
      'building_farm' => 38,
      'building_smithy' => 141,
      'building_masonry' => 0,
      'building_ore_mine' => 37,
      'building_gryphon_nest' => 0,
      'building_tower' => 30,
      'building_wizard_guild' => 0,
      'building_temple' => 22,
      'building_diamond_mine' => 145,
      'building_school' => 0,
      'building_lumberyard' => 23,
      'building_forest_haven' => 0,
      'building_factory' => 107,
      'building_guard_tower' => 43,
      'building_shrine' => 0,
      'building_barracks' => 0,
      'building_dock' => 0,

      'protection_ticks_remaining' => 0,
    ]);
  }
}
