<?php

namespace OpenDominion\Sim\Gnome\AlchMasonRr;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['science', 8],
      ['walls', 13],
      ['forges', 13],
      ['keep', 30],
    ];
  }
}
