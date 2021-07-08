<?php

namespace OpenDominion\Sim\Merfolk\Converter\DmRr;

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

    $new_acres = $this->paid_acres + $capacity;
    $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_farm', $new_acres, 0.07, 'plain', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    if($tick < 200) {
      $acres_to_explore['land_forest'] += $this->to_explore_by_percentage('building_lumberyard', $new_acres, 0.05, 'forest', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    } else {
      $acres_to_explore['land_forest'] += $this->to_explore_by_percentage('building_lumberyard', $new_acres, 0.07, 'forest', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

    $acres_to_explore['land_swamp'] += $this->to_explore_by_percentage('building_tower', $new_acres, 0.04, 'swamp', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    if($this->current_acres > 1500) {
      $wanted_homes = $this->homes_for_full_employment($tick);
      $acres_to_explore['land_water'] += min($wanted_homes, $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

    if($this->current_acres > 2400) {
      $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_smithy', $new_acres, 0.18, 'plain', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

    $wanted_dm = $this->build_to_max_nr('building_diamond_mine', 1100, 'cavern', $capacity);
    $acres_to_explore['land_cavern'] += $wanted_dm;
    $capacity = $max_afford - array_sum($acres_to_explore);

    $acres_to_explore['land_plain'] += $capacity;
    // print "explore: explore remaining capacity {$capacity} as plain";

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
    $farms_needed = round($this->current_acres * 0.07 - $this->dominion->building_farm - $this->incoming_buildings['building_farm']);
    if($farms_needed > 0) {
      $buildings_to_build['building_farm'] = min($farms_needed, $capacity, $barren);
      $capacity = $max_afford - array_sum($buildings_to_build);
    }

    if($tick >= 500) {
      // MASON
      $barren -= $buildings_to_build['building_farm'];
      $buildings_to_build['building_masonry'] = min($capacity, $barren);
      $capacity = $max_afford - array_sum($buildings_to_build);
    } else {
      // SMITHY
      $barren -= $buildings_to_build['building_farm'];
      $buildings_to_build['building_smithy'] = min($capacity, $barren);
      $capacity = $max_afford - array_sum($buildings_to_build);
    }

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'forest');
    $buildings_to_build['building_lumberyard'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'cavern');
    $buildings_to_build['building_diamond_mine'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'swamp');
    $buildings_to_build['building_tower'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'water');
    $buildings_to_build['building_home'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'hill');
    $buildings_to_build['building_guard_tower'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'plain');
    $buildings_to_build['building_masonry'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    return $buildings_to_build;
  }
}
