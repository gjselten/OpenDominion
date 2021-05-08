<?php

namespace OpenDominion\Sim\Icekin\R24\AlchMasonRr;

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
    if($rp >= 10000 && isset($this->techs()[count($unlocked_techs)])) {
      return $this->techs()[count($unlocked_techs)];
    }
    return;
  }

  // get 7.5% explore reduction first, then work towards 5% plat tech.
  function techs() {
    return [
      "tech_21_1",  // -10% lumber constructon cost
      "tech_19_1",  // -2.5% food consumption
      "tech_17_1",  // -15% wiz cost & -5% spy cost
      "tech_18_3",  // -2.5% plat explore cost
      "tech_20_3",  // -2.5% plat construction cost
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
      "tech_18_7",  // -25% draftees/spies lost
      "tech_17_9",  // -1% RG tax
      "tech_15_9",  // +1.5% spy refresh rate
      "tech_13_9",  // -3% training cost
      "tech_14_11", // 1% pop bonus
      "tech_8_11"   // +2.5% gem production; -5% self spell cost
    ];
  }

    // get 5% plat tech first, then other production techs, then explore techs.
    // function techs() {
    //    return [
    //      "tech_1_1",   // +2.5% food
    //      "tech_3_1",   // +7.5% mana
    //      "tech_4_3",   // 15% better exchange rates
    //      "tech_5_5",   // +2.5% gem production
    //      "tech_6_7",   // -20% FG cost
    //      "tech_8_7",   // +2.5% gem production
    //      "tech_10_7",  // +5% plat
    //      "tech_6_3",   // -5% construction plat cost
    //      "tech_5_1",   // +5% lumber
    //      "tech_7_1",   // + 7.5% food prod
    //      "tech_9_1",   // +2.5% plat prod
    //      "tech_10_3",  // +1% pop
    //      "tech_11_5",  // -3% training cost
    //      "tech_12_3",  // -2.5% explore & -2.5% construction
    //      "tech_21_1",  // -10% lumber constructon cost
    //      "tech_20_3",  // -2.5% plat construction cost
    //      "tech_18_3",  // -2.5% plat explore cost
    //      "tech_17_5",  // -50% morale drop
    //      "tech_16_7",  // -1 blackop duration
    //      "tech_14_7",  // -7.5% construction cost
    //      "tech_15_5",  // -7.5% exploring cost
    //      "tech_13_1",  // +10% wonder damage
    //      "tech_15_1",  // +10% theft / -10% spy losses
    //      "tech_14_3",  // +1.5% wiz refresh
    //      "tech_13_5",  // +1.5% pop
    //      "tech_4_7",   // +10% boat production
    //      "tech_5_9",   // +2.5% food production
    //      "tech_6_11",  // +1.5% pop
    //      "tech_7_9",   // -20% lumber rot & -10% lumber construction cost
    //      "tech_9_9",   // -3% training cost
    //    ];
    //  }
}
