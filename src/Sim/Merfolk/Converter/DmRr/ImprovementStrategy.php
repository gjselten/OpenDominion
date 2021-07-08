<?php

namespace OpenDominion\Sim\Merfolk\Converter\DmRr;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['science', 13],
      ['walls', 15],
      ['keep', 30],
    ];
  }
}
