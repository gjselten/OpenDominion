<?php

namespace OpenDominion\Sim;

use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\Actions\TrainingCalculator;

class BaseTrainingStrategy
{
  public function __construct($dominion, $unit2_str, $unit3_str, $queueService, $militaryCalculator, $trainingCalculator) {
    $this->queueService = $queueService;
    $this->militaryCalculator = $militaryCalculator;
    $this->trainingCalculator = $trainingCalculator;

    $this->unit2_str = $unit2_str;
    $this->unit3_str = $unit3_str;

    $this->incoming_unit3 = $this->queueService->getTrainingQueueTotalByResource($dominion, "military_unit3");
    $this->incoming_spec = $this->queueService->getTrainingQueueTotalByResource($dominion, "military_unit2");
  }

  function get_units_to_train($dominion, $tick) {
    $to_train = ['military_unit2' => 0, 'military_unit3' => 0];

    $raw_dp_to_train = $this->get_raw_dp_needed($dominion, $tick);
    $unit3_needed = ceil($raw_dp_to_train / $this->unit3_str);
    $max_unit3_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit3'];
    $unit3_to_train = min($unit3_needed, $max_unit3_trainable);

    $to_train['military_unit3'] = $unit3_to_train;

    // print "TRAIN (tick $tick): Wanted dp: $wanted_dp; trained dp: $trained_dp; to train: " . print_r($to_train, true) . '<br />';
    return $to_train;
  }


  function get_raw_dp_needed($dominion, $tick) {
    $wanted_dp = $this->wanted_dp($tick);

    $raw_dp = $dominion->military_unit2 * $this->unit2_str + $dominion->military_unit3 * $this->unit3_str;
    $mods = $this->militaryCalculator->getDefensivePowerMultiplier($dominion) - $this->mod_reduction_to_defend($tick);
    $dp = $raw_dp * $mods;

    $incoming_unit2 = $this->queueService->getTrainingQueueTotalByResource($dominion, "military_unit2");
    $incoming_unit3 = $this->queueService->getTrainingQueueTotalByResource($dominion, "military_unit3");
    $incoming_dp = ($incoming_unit2 * $this->unit2_str + $incoming_unit3 * $this->unit3_str) * $mods;

    $trained_dp = $dp + $incoming_dp;

    $raw_dp_to_train = ($wanted_dp - $trained_dp) / $mods;
    return $raw_dp_to_train;
  }

  function mod_reduction_to_defend($tick) {
    return 0.1;
  }

  function wanted_dp($tick) {
    $dp_per_day = [
      0 => 12000,
      1 => 18000,
      2 => 25000,
      3 => 32000,
      4 => 38000,
      5 => 45000,
      6 => 55000,
      7 => 63000,
      8 => 72000,
      9 => 85000,
      10 => 90000,
      11 => 100000,
      12 => 110000,
      13 => 115000,
      14 => 120000,
      15 => 130000,
      16 => 140000,
      17 => 150000,
      18 => 170000,
      19 => 185000,
      20 => 190000,
      21 => 195000,
      22 => 200000,
      23 => 205000,
      24 => 220000,
      25 => 250000,
      26 => 255000,
      27 => 265000,
      28 => 275000,
      29 => 300000,
      30 => 330000,
      31 => 342000,
      32 => 354000,
      33 => 366000,
      34 => 377000,
      35 => 390000,
      36 => 402000,
      37 => 414000,
      38 => 426000,
      39 => 438000,
      40 => 430000,
      41 => 445000,
      42 => 460000,
      43 => 475000,
      44 => 490000,
      45 => 500000,
      46 => 515000,
      47 => 530000
    ];
    $day = floor($tick / 24);
    return $dp_per_day[$day];
  }


}
