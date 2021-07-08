<?php

namespace OpenDominion\Sim\Merfolk\Converter\DmRr;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['walls', 15],
      ['science', 13],
      ['keep', 24],
      ['forges', 12],
      ['keep', 30],
    ];
  }
}
