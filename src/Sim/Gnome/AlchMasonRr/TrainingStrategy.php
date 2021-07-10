<?php

namespace OpenDominion\Sim\Gnome\AlchMasonRr;

use OpenDominion\Sim\BaseTrainingStrategy;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\Actions\TrainingCalculator;

use OpenDominion\Calculators\Dominion\LandCalculator;

class TrainingStrategy extends BaseTrainingStrategy
{
  function get_units_to_train($dominion, $tick) {
    $to_train = [
      'military_unit2' => 0,
      'military_unit3' => 0,
      'military_unit4' => 0,
      'military_wizards' => 0,
      'military_archmages' => 0
    ];

    $landCalculator = app(LandCalculator::class);
    $acres = $landCalculator->getTotalLand($dominion);

    $convert = false;
    if($acres >= 3000) {
      $convert = true;
    }

    $raw_dp_to_train = $this->get_raw_dp_needed($dominion, $tick);
    if($raw_dp_to_train <= 100 && !$convert) {
      return $to_train;
    }

    // if($tick > 5 && $dominion->military_unit2 < 5000) {
    //   $unit2_needed = ceil($raw_dp_to_train / $this->unit2_str);
    //   $max_unit2_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit2'];
    //   $unit2_to_train = min($unit2_needed, $max_unit2_trainable);
    //   $to_train['military_unit2'] = max($unit2_to_train, 0);
    // } else {
      $unit3_needed = ceil($raw_dp_to_train / $this->unit3_str);
      $max_unit3_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit3'];
      $unit3_to_train = min($unit3_needed, $max_unit3_trainable);
      $to_train['military_unit3'] = max($unit3_to_train, 0);
      if($to_train['military_unit3'] == '-0') {
        $to_train['military_unit3'] = 0;
      }
    // }

    if(array_sum($to_train) > 0) {
      return $to_train;
    }

    if($acres >= 3000) {
      $max_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit4'];
      $to_train['military_unit4'] = $max_trainable;
    }

    return $to_train;
  }
}