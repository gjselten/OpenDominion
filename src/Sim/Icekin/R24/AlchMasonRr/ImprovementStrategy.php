<?php

namespace OpenDominion\Sim\Icekin\R24\AlchMasonRr;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['science', 8],
      ['walls', 13],
      ['keep', 24],
      ['science', 9],
      ['forges', 13],
      ['keep', 30],
    ];
  }
}