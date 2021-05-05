<?php

namespace OpenDominion\Sim\Icekin\Convert\OmRrWizTech;

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
