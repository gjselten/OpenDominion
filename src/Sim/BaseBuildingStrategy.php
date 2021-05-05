<?php

namespace OpenDominion\Sim;
use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\PopulationCalculator;
use OpenDominion\Calculators\Dominion\BuildingCalculator;

class BaseBuildingStrategy
{

  public function __construct($dominion, $queueService) {
    $this->dominion = $dominion;
    $this->queueService = $queueService;

    $this->landCalculator = app(LandCalculator::class);
    $this->populationCalculator = app(PopulationCalculator::class);
    $this->buildingCalculator = app(BuildingCalculator::class);

    $this->current_acres = $this->landCalculator->getTotalLand($dominion);

    $this->set_incoming_acres();
    $this->set_incoming_buildings();
    $this->paid_acres = $this->current_acres + array_sum($this->incoming_acres);
  }

  public function get_incoming_buildings() {
    return $this->incoming_buildings;
  }

  public function get_incoming_acres() {
    return $this->incoming_acres;
  }

  public function get_land_types_to_explore($dominion, $tick, $max_afford) {
    throw new Exception("Child classes must implement get_land_types_to_explore()");
  }

  public function get_buildings_to_build($dominion, $tick, $max_afford) {
    throw new Exception("Child classes must implement get_buildings_to_build()");
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

  function homes_for_full_employment() {
    $jobs = $this->populationCalculator->getEmploymentJobs($this->dominion);
    $incoming_jobs = 20 * (array_sum($this->incoming_buildings) - $this->incoming_buildings['building_home'] - $this->incoming_buildings['building_barracks']);
    // $incoming_jobs += 20 * (array_sum($this->incoming_acres) - $this->incoming_acres['land_cavern']);
    $total_jobs = $jobs + $incoming_jobs;

    $incoming_peasants = $this->incoming_buildings['building_home'] * $this->populationCalculator->getMaxPopulationMultiplier($this->dominion);
    $total_peasants = $this->dominion->peasants + $incoming_peasants;


    if($total_peasants >= $total_jobs) {
      // print "Homes: enough homes for all jobs. Not building homes.";
      return 0;
    }

    $jobs_to_fill = $total_jobs - $total_peasants;
    $pop_mods = $this->populationCalculator->getMaxPopulationMultiplier($this->dominion);
    $homes_required = round($jobs_to_fill / $pop_mods / 30);
    // print "Homes: jobs: $jobs; total jobs with incoming: {$total_jobs}; pop mods: {$pop_mods}; peasants: {$this->dominion->peasants}; ; total peasants with incoming: {$total_peasants}; jobs to fill: {$jobs_to_fill}; homes needed: {$homes_required}<br />";

    return $homes_required;
  }


  private function set_incoming_acres() {
    $this->incoming_acres = [
      'land_plain' => 0,
      'land_mountain' => 0,
      'land_swamp' => 0,
      'land_cavern' => 0,
      'land_forest' => 0,
      'land_hill' => 0,
      'land_water' => 0
    ];

    foreach($this->incoming_acres as $type => $_incoming) {
      $incoming = $this->queueService->getExplorationQueueTotalByResource($this->dominion, $type);
      $this->incoming_acres[$type] = $incoming;
    }
  }

  private function set_incoming_buildings() {
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

    foreach($this->incoming_buildings as $building => $_incoming) {
      $incoming = $this->queueService->getConstructionQueueTotalByResource($this->dominion, $building);
      $this->incoming_buildings[$building] = $incoming;
    }
  }
}
