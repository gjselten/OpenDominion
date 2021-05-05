<?php

namespace OpenDominion\Sim\Firewalker\TechsRr;

use OpenDominion\Models\User;
use OpenDominion\Models\Dominion;

use OpenDominion\Sim\Base;
use OpenDominion\Sim\Firewalker\TechsRr\BuildingStrategy;
use OpenDominion\Sim\Firewalker\TechsRr\TrainingStrategy;
use OpenDominion\Sim\Firewalker\TechsRr\ImprovementStrategy;
use OpenDominion\Sim\Firewalker\TechsRr\TechStrategy;
use OpenDominion\Sim\BaseTechStrategy;

class Sim extends Base
{
  function ticks_to_run() {
    return 24 * 46;
  }

  function setup($tick) {
    $this->buildingStrategy = new BuildingStrategy($this->dominion, $this->queueService);
    $this->trainingStrategy = new TrainingStrategy($this->dominion, 3, 4.5, $this->queueService, $this->militaryCalculator, $this->trainingCalculator);
    $this->improvementStrategy =  new ImprovementStrategy();
    $this->tech_strategy = new BaseTechStrategy();
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
    return ['midas_touch', 'gaias_watch', 'ares_call', 'alchemist_flame'];
  }

  function get_incoming_acres_by_landtype() {
    return $this->buildingStrategy->get_incoming_acres();
  }

  function get_incoming_buildings() {
    return $this->buildingStrategy->get_incoming_buildings();
  }

  function release($tick) {
    if($tick <= 84) {
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
    $race_id = 3;

    return Dominion::create([
      'user_id' => $user_id,
      'round_id' => $round_id,
      'realm_id' => $realm_id,
      'race_id' => $race_id,
      'pack_id' => null,

      'ruler_name' => 'ruler' . rand(0,999999999),
      'name' => 'domname'  . rand(0,999999999),
      'prestige' => 250,

      'peasants' => 10310,
      'peasants_last_hour' => 0,

      'draft_rate' => 90,
      'morale' => 100,
      'spy_strength' => 100,
      'wizard_strength' => 100,

      'resource_platinum' => 519854,
      'resource_food' => 9975,
      'resource_lumber' => 8859,
      'resource_mana' => 10000,
      'resource_ore' => 0,
      'resource_gems' => 0,
      'resource_tech' => 0,
      'resource_boats' => 0,

      'improvement_science' => 0,
      'improvement_keep' => 0,
      'improvement_towers' => 0,
      'improvement_forges' => 0,
      'improvement_walls' => 0,
      'improvement_harbor' => 0,

      'military_draftees' => 975,
      'military_unit1' => 0,
      'military_unit2' => 745,
      'military_unit3' => 885,
      'military_unit4' => 0,
      'military_spies' => 0,
      'military_wizards' => 0,
      'military_archmages' => 0,

      'land_plain' => 424,
      'land_mountain' => 0,
      'land_swamp' => 59,
      'land_cavern' => 125,
      'land_forest' => 28,
      'land_hill' => 154,
      'land_water' => 0,

      'building_home' => 10,
      'building_alchemy' => 235,
      'building_farm' => 46,
      'building_smithy' => 143,
      'building_masonry' => 0,
      'building_ore_mine' => 0,
      'building_gryphon_nest' => 0,
      'building_tower' => 47,
      'building_wizard_guild' => 0,
      'building_temple' => 12,
      'building_diamond_mine' => 25,
      'building_school' => 90,
      'building_lumberyard' => 28,
      'building_forest_haven' => 0,
      'building_factory' => 95,
      'building_guard_tower' => 59,
      'building_shrine' => 0,
      'building_barracks' => 0,
      'building_dock' => 0,

      'protection_ticks_remaining' => 0,
    ]);
  }
}
