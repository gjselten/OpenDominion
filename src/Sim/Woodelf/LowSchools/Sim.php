<?php

namespace OpenDominion\Sim\Woodelf\LowSchools;

use OpenDominion\Models\User;
use OpenDominion\Models\Dominion;

use OpenDominion\Sim\Base;
use OpenDominion\Sim\Woodelf\LowSchools\BuildingStrategy;
use OpenDominion\Sim\Woodelf\LowSchools\TrainingStrategy;
use OpenDominion\Sim\Woodelf\LowSchools\ImprovementStrategy;
use OpenDominion\Sim\BaseTechStrategy;

class Sim extends Base
{
  function ticks_to_run() {
    return 24 * 25;
  }

  function setup($tick) {
    $this->buildingStrategy = new BuildingStrategy($this->dominion, $this->queueService);
    $this->trainingStrategy = new TrainingStrategy($this->dominion, 4, 6, $this->queueService, $this->militaryCalculator, $this->trainingCalculator);
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

  function destroy($tick) {
    if($tick == 156) {
      $result = $this->destroyActionService->destroy($this->dominion, ['school' => 200]);
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
    $race_id = 20;

    return Dominion::create([
      'user_id' => $user_id,
      'round_id' => $round_id,
      'realm_id' => $realm_id,
      'race_id' => $race_id,
      'pack_id' => null,

      'ruler_name' => 'ruler' . rand(0,999999999),
      'name' => 'domname'  . rand(0,999999999),
      'prestige' => 250,

      'peasants' => 10736,
      'peasants_last_hour' => 0,

      'draft_rate' => 90,
      'morale' => 100,
      'spy_strength' => 100,
      'wizard_strength' => 100,

      'resource_platinum' => 433566,
      'resource_food' => 1704,
      'resource_lumber' => 7090,
      'resource_mana' => 10000,
      'resource_ore' => 7214,
      'resource_gems' => 22740,
      'resource_tech' => 0,
      'resource_boats' => 0,

      'improvement_science' => 0,
      'improvement_keep' => 0,
      'improvement_towers' => 0,
      'improvement_forges' => 0,
      'improvement_walls' => 0,
      'improvement_harbor' => 0,

      'military_draftees' => 969,
      'military_unit1' => 0,
      'military_unit2' => 1607,
      'military_unit3' => 0,
      'military_unit4' => 0,
      'military_spies' => 0,
      'military_wizards' => 0,
      'military_archmages' => 0,

      'land_plain' => 256,
      'land_mountain' => 15,
      'land_swamp' => 19,
      'land_cavern' => 246,
      'land_forest' => 115,
      'land_hill' => 109,
      'land_water' => 0,

      'building_home' => 101,
      'building_alchemy' => 132,
      'building_farm' => 42,
      'building_smithy' => 82,
      'building_masonry' => 0,
      'building_ore_mine' => 15,
      'building_gryphon_nest' => 0,
      'building_tower' => 19,
      'building_wizard_guild' => 0,
      'building_temple' => 0,
      'building_diamond_mine' => 46,
      'building_school' => 200,
      'building_lumberyard' => 14,
      'building_forest_haven' => 0,
      'building_factory' => 109,
      'building_guard_tower' => 0,
      'building_shrine' => 0,
      'building_barracks' => 0,
      'building_dock' => 0,

      'protection_ticks_remaining' => 0,
    ]);
  }
}
