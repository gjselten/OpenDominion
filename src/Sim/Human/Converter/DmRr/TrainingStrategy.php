<?php

namespace OpenDominion\Sim\Human\Converter\DmRr;

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

    $ticks_saving_up_plat = [
      476,477,478,479,480,    # r/r alchs to masons
      487,488,489,490,491,    # r/r dm to masons
      498,499,500,501,502,    # r/r dm to masons
      // 509,510,511,512,513,    # r/r dm to masons
    ];

    if(in_array($tick, $ticks_saving_up_plat)) {
      return $to_train;
    }

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

    $unit3_needed = ceil($raw_dp_to_train / $this->unit3_str);
    $max_unit3_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit3'];
    $unit3_to_train = min($unit3_needed, $max_unit3_trainable);
    $to_train['military_unit3'] = max($unit3_to_train, 0);

    if(array_sum($to_train) > 0) {
      return $to_train;
    }

    if($convert) {
      $max_trainable = $this->trainingCalculator->getMaxTrainable($dominion)['unit4'];
      $to_train['military_unit4'] = $max_trainable;
    }

    return $to_train;
  }
}
