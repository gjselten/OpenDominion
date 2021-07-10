<?php

namespace OpenDominion\Sim\Human\Converter\DmRr;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['science', 10],
      ['walls', 13],
      ['keep', 26],
      ['walls', 17],
      ['forges', 17],
      ['keep', 30],
    ];
  }
}
