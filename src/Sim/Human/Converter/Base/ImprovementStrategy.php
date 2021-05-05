<?php

namespace OpenDominion\Sim\Human\Converter\Base;

use OpenDominion\Sim\BaseImprovementStrategy;

class ImprovementStrategy extends BaseImprovementStrategy
{
  function investment_strategy() {
    return [
      ['keep', 22],
      ['science', 10],
      ['walls', 13],
      ['forges', 13],
      ['keep', 30],
    ];
  }
}
