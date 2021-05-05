<?php

namespace OpenDominion\Sim\Merfolk\AlchDm;

class ImprovementStrategy
{

  function get_investment_to_do($dominion, $tick, $gems, $improvementCalculator) {
    $invest = [
      'science' => 0,
      'keep' => 0,
      'walls' => 0
    ];

    if(($improvementCalculator->getImprovementMultiplierBonus($dominion, 'keep') / $improvementCalculator->getImprovementMultiplier($dominion)) < 0.22) {
      $invest['keep'] = $gems;
    }
    elseif(($improvementCalculator->getImprovementMultiplierBonus($dominion, 'science') / $improvementCalculator->getImprovementMultiplier($dominion)) < 0.13) {
      $invest['science'] = $gems;
    }
    elseif(($improvementCalculator->getImprovementMultiplierBonus($dominion, 'walls') / $improvementCalculator->getImprovementMultiplier($dominion)) < 0.15) {
      $invest['walls'] = $gems;
    }
    else {
      $invest['keep'] = $gems;
    }

    return $invest;
  }
}
