<?php

namespace OpenDominion\Sim\Merfolk\LowSchools2;

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
