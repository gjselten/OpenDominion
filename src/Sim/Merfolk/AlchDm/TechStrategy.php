<?php

namespace OpenDominion\Sim\Merfolk\AlchDm;

class TechStrategy
{

  function select_tech($dominion, $tick) {
    $rp = $dominion->resource_tech;
    $unlocked_techs = $dominion->techs->pluck('key')->all();
    // print "select tech $rp<br />";
    // print "UNLOCKED TECHS: " . print_r($unlocked_techs, true) . "<br />";
    // if(!empty($unlocked_techs)) {
    //   exit();
    // }
    if($rp >= 10000) {
      return $this->techs()[count($unlocked_techs)];
    }
    return;
  }

  function techs() {
    return [
      "tech_21_1",  // -10% lumber constructon cost
      "tech_20_3",  // -2.5% plat construction cost
      "tech_18_3",  // -2.5% plat explore cost
      "tech_17_5",  // -50% morale drop
      "tech_16_7",  // -1 blackop duration
      "tech_14_7",  // -7.5% construction cost
      "tech_15_5",  // -7.5% exploring cost
      "tech_16_3",  // -15% rot & drain
      "tech_14_3",  // +1.5% wiz refresh
      "tech_13_5",  // +1.5% pop
      "tech_15_1",  // +10% theft / -10% spy losses
      "tech_13_1",  // +10% wonder damage
      "tech_12_3",  // -2.5% explore & -2.5% construction
      "tech_11_5",  // -3% training cost
      "tech_10_3",  // +1% pop
      "tech_9_1",   // +2.5% plat prod
      "tech_7_1",   // + 7.5% food prod
      "tech_8_3",   // -20% lost to theft
      "tech_6_3",   // -5% construction plat cost
      "tech_4_3",   // 15% better exchange rates
      "tech_5_5",   // +2.5% gem production
      "tech_6_7",   // -20% FG cost
      "tech_8_7",   // +2.5% gem production
      "tech_10_7",  // +5% plat
      "tech_4_7",   // +10% boat production
      "tech_5_9",   // +2.5% food production
      "tech_6_11",  // +1.5% pop
      "tech_7_9",   // -20% lumber rot & -10% lumber construction cost
      "tech_9_9",   // -3% training cost
    ];
  }
}
