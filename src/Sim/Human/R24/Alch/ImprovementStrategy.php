<?php

namespace OpenDominion\Sim\Human\R24\Alch;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 18],
      ['science', 7],
      ['walls', 9],
      ['forges', 9],
      ['keep', 30],
    ];
  }
}
