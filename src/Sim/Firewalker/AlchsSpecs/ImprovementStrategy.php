<?php

namespace OpenDominion\Sim\Firewalker\AlchsSpecs;

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
