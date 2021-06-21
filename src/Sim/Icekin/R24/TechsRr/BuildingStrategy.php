<?php

namespace OpenDominion\Sim\Icekin\R24\TechsRr;

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

    if($this->current_acres + array_sum($this->incoming_acres) > 3020) {
      return $acres_to_explore;
    }

    $to_go = 3020 - $this->current_acres - array_sum($this->incoming_acres);
    $max_afford = min($max_afford, $to_go);

    $new_acres = $this->paid_acres + $capacity;

    if(($dominion->building_farm + $this->incoming_buildings['building_farm']) < 65) {
      $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_farm', $new_acres, 0.04, 'plain', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

    $acres_to_explore['land_forest'] += $this->to_explore_by_percentage('building_lumberyard', $new_acres, 0.045, 'forest', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    $acres_to_explore['land_swamp'] += $this->to_explore_by_percentage('building_tower', $new_acres, 0.05, 'swamp', $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    if($this->current_acres > 2000) {
      $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_smithy', $new_acres, 0.18, 'plain', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);

      $acres_to_explore['land_water'] += $this->build_to_max_nr('building_dock', 100, 'water', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

    $acres_to_explore['land_mountain'] += $capacity;
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
    if($this->current_acres <= 1900) {
      $farms_needed = round($this->current_acres * 0.04 - $this->dominion->building_farm - $this->incoming_buildings['building_farm']);
      if($farms_needed > 0 && $barren > 0) {
        $buildings_to_build['building_farm'] = min($farms_needed, $capacity, $barren);
        // print "acres paid: {$this->paid_acres}; farms owned: {$this->dominion->building_farm}; farms incoming: {$this->incoming_buildings['building_farm']}; farms needed: {$farms_needed}; barren plain: {$barren}; building farms: {$buildings_to_build['building_farm']}<br />";
        $capacity = $max_afford - array_sum($buildings_to_build);
      }
    }

    // SMITHY
    $barren -= $buildings_to_build['building_farm'];
    $buildings_to_build['building_smithy'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'forest');
    $buildings_to_build['building_lumberyard'] = min($capacity, $barren);
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

    $homes_needed = $this->get_homes_to_build();
    // print "HOMES: ON {$this->current_acres}: $homes_needed<br />";
    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'mountain');
    $buildings_to_build['building_home'] = min($capacity, $barren, $homes_needed);
    $capacity = $max_afford - array_sum($buildings_to_build);

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'mountain');
    $barren -= $buildings_to_build['building_home'];
    if(($dominion->building_ore_mine + $this->incoming_buildings['building_ore_mine']) < 920 && $tick < 400) {
      $buildings_to_build['building_ore_mine'] = min($capacity, $barren);
      $capacity = $max_afford - array_sum($buildings_to_build);
    }

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'mountain');
    $barren -= $buildings_to_build['building_home'];
    $barren -= $buildings_to_build['building_ore_mine'];
    $buildings_to_build['building_home'] += min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    return $buildings_to_build;
  }

  function get_homes_to_build() {
    $jobs = $this->populationCalculator->getEmploymentJobs($this->dominion);
    $incoming_jobs = 20 * (array_sum($this->incoming_buildings) - $this->incoming_buildings['building_home'] - $this->incoming_buildings['building_barracks']);
    // $incoming_jobs += 20 * array_sum($this->incoming_acres) - $this->incoming_acres['land_cavern'];
    $total_jobs = $jobs + $incoming_jobs;

    $barren = $this->landCalculator->getTotalBarrenLand($this->dominion);
    $buildings = $this->buildingCalculator->getTotalBuildings($this->dominion);
    $incoming_buildings = array_sum($this->incoming_buildings);
    $homes = $this->dominion->building_home;
    $incoming_homes = $this->incoming_buildings['building_home'];
    $raw_pop_space = $barren * 5 + $buildings * 15 + $incoming_buildings * 15 + $homes * 15 + $incoming_homes * 15;
    $pop_mods = $this->populationCalculator->getMaxPopulationMultiplier($this->dominion);
    $pop_space = $raw_pop_space * $pop_mods;

    $military = $this->populationCalculator->getPopulationMilitary($this->dominion);

    $peasant_space = $pop_space - $military;

    if($total_jobs <= $peasant_space) {
      return 0;
    }

    $jobs_to_fill = $total_jobs - $peasant_space;
    $homes_needed = $jobs_to_fill / (30 * $pop_mods);
    $homes_to_build = round($homes_needed - $this->incoming_buildings['building_home']);
    if($homes_to_build <= 0) {
      return 0;
    }

    // print "JOBS: $total_jobs; PEASANT SPACE: $peasant_space; HOMES TO BUILD: $homes_to_build<br />";
    return $homes_to_build;
  }
}
