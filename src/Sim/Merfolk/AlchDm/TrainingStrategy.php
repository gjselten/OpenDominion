<?php

namespace OpenDominion\Sim\Merfolk\AlchDm;

use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\Actions\TrainingCalculator;

class TrainingStrategy
{

  public function __construct($dominion, $queueService, $militaryCalculator, $trainingCalculator) {
    $this->queueService = $queueService;
    $this->militaryCalculator = $militaryCalculator;
    $this->trainingCalculator = $trainingCalculator;

    $this->unit2_str = 3;
    $this->unit3_str = 7;

    $this->incoming_unit2 = $this->queueService->getTrainingQueueTotalByResource($dominion, "military_unit2");
    $this->incoming_unit3 = $this->queueService->getTrainingQueueTotalByResource($dominion, "military_unit3");
  }


  function get_units_to_train($dominion, $tick) {
    $wanted_dp = $this->wanted_dp($tick);

    $to_train = [
      'military_unit2' => 0,
      'military_unit3' => 0
    ];

    $dp = round($this->militaryCalculator->getDefensivePower($dominion), 0);
    $mods = ($this->militaryCalculator->getDefensivePowerMultiplier($dominion) - 1);
    $dp_without_draftees = $dp - ($dominion->military_draftees * (1 + $mods));
    $incoming_dp = ($this->incoming_unit2 * $this->unit2_str +
                    $this->incoming_unit3 * $this->unit3_str) *
                    (1 + $mods);
    $trained_dp = $dp_without_draftees + $incoming_dp;

    if($trained_dp >= ($wanted_dp - 100)) {
      // print "train: NOT TRAINING. wanted dp: {$wanted_dp}; current dp: $dp ({$trained_dp}); dp without drafts: $dp_without_draftees; mods: $mods<br />";
      return $to_train;
    }

    $dp_to_train = $wanted_dp - $trained_dp + ($dominion->military_draftees * (1 + $mods));
    $raw_dp_to_train = $dp_to_train / (1 + $mods);

    if($tick > 5 && $dominion->military_unit2 < 5000) {
      $unit2_needed = ceil($raw_dp_to_train / $this->unit2_str);
      $max_unit2_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit2'];
      $to_train['military_unit2'] = min($unit2_needed, $max_unit2_trainable);
    } else {
      $unit3_needed = ceil($raw_dp_to_train / $this->unit3_str);
      $max_unit3_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit3'];
      $to_train['military_unit3'] = min($unit3_needed, $max_unit3_trainable);
    }

    return $to_train;
  }

  function wanted_dp($tick) {
    $dp_per_day = [
      0 => 12000,
      1 => 18000,
      2 => 24000,
      3 => 30000,
      4 => 35000,
      5 => 43000,
      6 => 50000,
      7 => 60000,
      8 => 70000,
      9 => 82000,
      10 => 88000,
      11 => 95000,
      12 => 105000,
      13 => 115000,
      14 => 120000,
      15 => 125000,
      16 => 130000,
      17 => 135000,
      18 => 150000,
      19 => 163000,
      20 => 180000,
      21 => 195000,
      22 => 205000,
      23 => 215000,
      24 => 240000,
      25 => 250000,
      26 => 260000,
      27 => 280000,
      28 => 300000,
      29 => 320000,
      30 => 330000,
      31 => 340000,
      32 => 350000,
      33 => 360000,
      34 => 370000,
      35 => 380000,
      36 => 390000,
      37 => 400000,
      38 => 410000,
      39 => 420000,
      40 => 430000,
      41 => 440000,
      42 => 450000,
      43 => 460000,
      44 => 470000,
      45 => 480000,
      46 => 490000,
      47 => 500000
    ];
    $day = floor($tick / 24);
    return $dp_per_day[$day];
  }


}
