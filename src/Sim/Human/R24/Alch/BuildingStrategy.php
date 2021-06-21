<?php

namespace OpenDominion\Sim\Human\R24\Alch;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\PopulationCalculator;
use OpenDominion\Sim\BaseBuildingStrategy;

class BuildingStrategy extends BaseBuildingStrategy
{
  public function get_land_types_to_explore($dominion, $tick, $max_afford) {
    $capacity = $max_afford;

    $acres_to_explore = [
      'land_plain' => 0,
      'land_mountain' => 0,
      'land_swamp' => 0,
      'land_cavern' => 0,
      'land_forest' => 0,
      'land_hill' => 0,
      'land_water' => 0
    ];

    if($this->current_acres + array_sum($this->incoming_acres) > 2900) {
      return $acres_to_explore;
    }

    $new_acres = $this->paid_acres + $capacity;

    $home_percentage = 0.15;
    if($tick > 160) { // day 11+
      $home_percentage = 0.18;
    }
    $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_home', $new_acres, $home_percentage, 'plain', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    $percentage = 0.06 + $tick / 40000; # slowly scale up farms
    $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_farm', $new_acres, $percentage, 'plain', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    if($this->current_acres < 2100) {
      $acres_to_explore['land_forest'] += $this->to_explore_by_percentage('building_lumberyard', $new_acres, 0.06, 'forest', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    } else {
      $acres_to_explore['land_forest'] += $this->to_explore_by_percentage('building_lumberyard', $new_acres, 0.04, 'forest', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

    $acres_to_explore['land_mountain'] += $this->to_explore_by_percentage('building_ore_mine', $new_acres, 0.05, 'mountain', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    $acres_to_explore['land_swamp'] += $this->to_explore_by_percentage('building_tower', $new_acres, 0.055, 'swamp', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    $acres_to_explore['land_plain'] += $this->build_to_max_nr('building_alchemy', 800, 'plain', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);


    if($this->current_acres > 2000) {
      $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_smithy', $new_acres, 0.18, 'plain', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);

      $acres_to_explore['land_water'] += $this->build_to_max_nr('building_dock', 100, 'water', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

    $acres_to_explore['land_cavern'] += $this->build_to_max_nr('building_diamond_mine', 125, 'cavern', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    $acres_to_explore['land_plain'] += $capacity;
    $capacity = $max_afford - array_sum($acres_to_explore);

    return $acres_to_explore;
  }

  public function get_buildings_to_build($dominion, $tick, $max_afford) {
    $capacity = $max_afford;

    $buildings_to_build = [
      'building_home' => 0,
      'building_alchemy' => 0,
      'building_farm' => 0,
      'building_smithy' => 0,
      'building_masonry' => 0,
      'building_ore_mine' => 0,
      'building_gryphon_nest' => 0,
      'building_tower' => 0,
      'building_wizard_guild' => 0,
      'building_temple' => 0,
      'building_diamond_mine' => 0,
      'building_school' => 0,
      'building_lumberyard' => 0,
      'building_forest_haven' => 0,
      'building_factory' => 0,
      'building_guard_tower' => 0,
      'building_shrine' => 0,
      'building_barracks' => 0,
      'building_dock' => 0,
    ];

    // FARMS
    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'plain');
    $percentage = 0.06; // + $tick / 40000;
    $farms_needed = round($this->current_acres * $percentage - $this->dominion->building_farm - $this->incoming_buildings['building_farm']);
    if($farms_needed > 0 && $barren > 0) {
      $buildings_to_build['building_farm'] = min($farms_needed, $capacity, $barren);
      // print "acres paid: {$this->paid_acres}; farms owned: {$this->dominion->building_farm}; farms incoming: {$this->incoming_buildings['building_farm']}; farms needed: {$farms_needed}; barren plain: {$barren}; building farms: {$buildings_to_build['building_farm']}<br />";
      $capacity = $max_afford - array_sum($buildings_to_build);
    }

    // SMITHY
    $barren -= $buildings_to_build['building_farm'];
    $owned_smithy = $this->dominion->building_smithy + $this->incoming_buildings['building_smithy'];
    $max_smithy = round((0.18 * $this->current_acres) - $owned_smithy);
    $max_smithy = max(0, $max_smithy);
    $buildings_to_build['building_smithy'] = min($capacity, $barren, $max_smithy);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'plain');
    $barren -= $buildings_to_build['building_farm'];
    $barren -= $buildings_to_build['building_smithy'];
    if(($dominion->building_alchemy + $this->incoming_buildings['building_alchemy']) < 800) {
      $buildings_to_build['building_alchemy'] = min($capacity, $barren);
      $capacity = $max_afford - array_sum($buildings_to_build);
    }

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'forest');
    $buildings_to_build['building_lumberyard'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'mountain');
    $buildings_to_build['building_ore_mine'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'swamp');
    $buildings_to_build['building_tower'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'water');
    $buildings_to_build['building_dock'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'cavern');
    $buildings_to_build['building_diamond_mine'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'plain');
    $barren -= $buildings_to_build['building_farm'];
    $barren -= $buildings_to_build['building_smithy'];
    $barren -= $buildings_to_build['building_alchemy'];
    $owned_homes = $this->dominion->building_home + $this->incoming_buildings['building_home'];
    $max_homes = round((0.18 * $this->current_acres) - $owned_homes);
    $buildings_to_build['building_home'] = min($capacity, $barren, $max_homes);
    if($buildings_to_build['building_home'] < 0) {
      $buildings_to_build['building_home'] = 0;
    }
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'plain');
    $barren -= $buildings_to_build['building_farm'];
    $barren -= $buildings_to_build['building_smithy'];
    $barren -= $buildings_to_build['building_home'];
    $barren -= $buildings_to_build['building_alchemy'];
    // if($this->dominion->building_masonry < 100 ) { // get extra homes for conversion
    //   $buildings_to_build['building_masonry'] = min($capacity, $barren);
    //   $capacity = $max_afford - array_sum($buildings_to_build);
    // } else {
      $buildings_to_build['building_home'] += min($capacity, $barren);
      $capacity = $max_afford - array_sum($buildings_to_build);
    // }

    return $buildings_to_build;
  }
}
