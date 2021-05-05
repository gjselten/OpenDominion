<?php

namespace OpenDominion\Sim;

class BaseImprovementStrategy
{
  function investment_strategy() {
    throw new Exception("Child classes must implement investment_strategy()");
  }

  function get_investment_to_do($dominion, $tick, $gems, $improvementCalculator) {
    $invest = [
      'science' => 0,
      'keep' => 0,
      'walls' => 0
    ];

    if($gems === 0) {
      return $invest;
    }

    $strat = $this->investment_strategy();
    foreach($strat as $strat_row) {
      $type = $strat_row[0];
      $limit = $strat_row[1];
      $current = $improvementCalculator->getImprovementMultiplierBonus($dominion, $type) / $improvementCalculator->getImprovementMultiplier($dominion) * 100;
      if($current < $limit) {
        $invest[$type] = $gems;
        break;
      }
    }

    return $invest;
  }
}
