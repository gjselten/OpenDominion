<?php

namespace OpenDominion\Sim\Firewalker\Alchs;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 19],
      ['science', 13],
      ['walls', 10],
      ['keep', 30],
    ];
  }
}
