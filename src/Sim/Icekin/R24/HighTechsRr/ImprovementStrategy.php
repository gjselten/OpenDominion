<?php

namespace OpenDominion\Sim\Icekin\R24\HighTechsRr;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['science', 9],
      ['walls', 13],
      ['forges', 13],
      ['keep', 30],
    ];
  }
}
