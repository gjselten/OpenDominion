<?php

namespace OpenDominion\Sim\Woodelf\LowSchools;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['science', 13],
      ['walls', 13],
      ['keep', 30],
    ];
  }
}
