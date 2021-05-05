<?php

namespace OpenDominion\Sim\Merfolk\AlchDm;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\PopulationCalculator;

class BuildingStrategy
{

  public function __construct($dominion, $queueService) {
    $this->dominion = $dominion;
    $this->queueService = $queueService;

    $this->landCalculator = app(LandCalculator::class);
    $this->populationCalculator = app(PopulationCalculator::class);
    $this->current_acres = $this->landCalculator->getTotalLand($dominion);

    $this->incoming_acres = [
      'land_plain' => 0,
      'land_mountain' => 0,
      'land_swamp' => 0,
      'land_cavern' => 0,
      'land_forest' => 0,
      'land_hill' => 0,
      'land_water' => 0
    ];

    $this->incoming_buildings = [
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

    foreach($this->incoming_acres as $type => $_incoming) {
      $incoming = $this->queueService->getExplorationQueueTotalByResource($dominion, $type);
      $this->incoming_acres[$type] = $incoming;
    }

    foreach($this->incoming_buildings as $building => $_incoming) {
      $incoming = $this->queueService->getConstructionQueueTotalByResource($dominion, $building);
      $this->incoming_buildings[$building] = $incoming;
    }

    $this->paid_acres = $this->current_acres + array_sum($this->incoming_acres);
  }

  public function get_incoming_buildings() {
    return $this->incoming_buildings;
  }

  public function get_incoming_acres() {
    return $this->incoming_acres;
  }

  public function buildingStrategy() {
    # farms, smithies, alchs, masons, homes, diamond mines, schools,
    # lumber yards, towers, factories, guard towers, barracks.

    # simple strat 1:
    # 6% farms
    # 2.5% LY
    # 5% towers
    # full homes
    # 600 DM
    # GT
  }


  function to_explore_by_percentage($building, $acres, $percentage, $landtype, $capacity) {
    $needed = round($acres * $percentage - $this->dominion->$building - $this->incoming_buildings[$building]);

    if($needed <= 0) {
      return 0;
    }

    $barren = $this->landCalculator->getTotalBarrenLandByLandType($this->dominion, $landtype);
    $to_explore = $needed - $barren - $this->incoming_acres["land_$landtype"];
    $acres_to_explore = min($to_explore, $capacity);

    if($acres_to_explore < 0) {
      return 0;
    }

    // print "explore: acres paid: {$this->paid_acres}; to explore: {$capacity}; $building owned: {$this->dominion->$building}; $building incoming: {$this->incoming_buildings[$building]}; $building needed to build: {$needed}; barren: {$barren}; land incoming: {$this->incoming_acres["land_$landtype"]}; $building to explore: {$acres_to_explore}<br />";
    return $acres_to_explore;
  }

  function build_to_max_nr($building, $max, $landtype, $capacity) {
    $owned = $this->dominion->$building + $this->incoming_buildings[$building];
    $incoming_acres = $this->incoming_acres["land_$landtype"];
    $needed = $max - $owned - $incoming_acres;
    if($needed <= 0) {
      return 0;
    }
    // print "max: $max; owned: $owned; incoming: $incoming_acres; needed: $needed";
    return min($capacity, $needed);
  }

  function homes_for_full_employment($tick) {
    # find nr of jobs
    # find nr of peasants
    # find nr of 'incoming jobs' (incoming non_homes + incoming non_water)
    # find nr of incoming homes
    $jobs = $this->populationCalculator->getEmploymentJobs($this->dominion);
    $incoming_jobs = array_sum($this->incoming_buildings) - $this->incoming_buildings['building_home'] - $this->incoming_buildings['building_barracks'];
    $incoming_jobs += array_sum($this->incoming_acres) - $this->incoming_acres['land_water'];
    $total_jobs = $jobs + $incoming_jobs;

    $incoming_peasants = ($this->incoming_buildings['building_home'] * 15 + array_sum($this->incoming_acres) * 15 +  $this->incoming_acres['land_water'] * 15) * $this->populationCalculator->getMaxPopulationMultiplier($this->dominion);
    $total_peasants = $this->dominion->peasants + $incoming_peasants;

    // correct a bit. early we run into some unemployment.
    if($tick < 125) {
      $buffer_peasants = -1000;
    } else {
      $buffer_peasants = 0;
    }

    if(($total_peasants - $buffer_peasants) >= $total_jobs) {
      // print "Homes: enough homes for all jobs. Not building homes.";
      return 0;
    }


    $jobs_to_fill = ($total_jobs + $buffer_peasants) - $total_peasants;
    $pop_mods = $this->populationCalculator->getMaxPopulationMultiplier($this->dominion);
    $homes_required = round($jobs_to_fill / $pop_mods / 30);
    // print "Homes: pop mods: {$pop_mods}; jobs: $jobs; peasants: {$this->dominion->peasants}; total jobs with incoming: {$total_jobs}; total peasants with incoming: {$total_peasants}; jobs to fill: {$jobs_to_fill}; homes needed: {$homes_required}<br />";

    return $homes_required;
  }

  public function getLandTypesToExplore($dominion, $tick, $max_afford) {
    // first priority: farms
    // get current % of farms.
    // if percentage of farms is too low, explore plains.
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

    $this->landCalculator = app(LandCalculator::class);
    $acres = $this->landCalculator->getTotalLand($dominion);

    $new_acres = $this->paid_acres + $capacity;

    $acres_to_explore['land_plain'] += $this->to_explore_by_percentage('building_farm', $new_acres, 0.06, 'plain', $capacity);
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

    $wanted_homes = $this->homes_for_full_employment($tick);
    $acres_to_explore['land_water'] += min($wanted_homes, $capacity);
    $capacity = $max_afford - array_sum($acres_to_explore);

    $wanted_dm = $this->build_to_max_nr('building_diamond_mine', 750, 'cavern', $capacity);
    $acres_to_explore['land_cavern'] += $wanted_dm;
    $capacity = $max_afford - array_sum($acres_to_explore);

    if($tick > (24 * 33)) {
      $acres_to_explore['land_hill'] += $this->to_explore_by_percentage('building_guard_tower', $new_acres, 0.20, 'hill', $capacity);
      $capacity = $max_afford - array_sum($acres_to_explore);
    }

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
    $farms_needed = $this->farms_needed($this->dominion, $this->paid_acres);
    if($farms_needed > 0) {
      $barren = $this->landCalculator->getTotalBarrenLandByLandType($dominion, 'plain');
      $buildings_to_build['building_farm'] = min($farms_needed, $capacity, $barren);
      // print "acres paid: {$this->paid_acres}; farms owned: {$this->dominion->building_farm}; farms incoming: {$this->incoming_buildings['building_farm']}; farms needed: {$farms_needed}; barren plain: {$barren}; building farms: {$buildings_to_build['building_farm']}<br />";
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
    $buildings_to_build['building_alchemy'] = min($capacity, $barren);
    $capacity = $max_afford - array_sum($buildings_to_build);

    return $buildings_to_build;
  }

  function farms_needed($dominion, $acres) {
    return round($acres * 0.06 - $this->dominion->building_farm - $this->incoming_buildings['building_farm']);
  }

}
