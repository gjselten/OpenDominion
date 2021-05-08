<?php

namespace OpenDominion\Http\Controllers\Dominion;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\OpsCalculator;
use OpenDominion\Calculators\Dominion\PopulationCalculator;
use OpenDominion\Calculators\Dominion\RangeCalculator;
use OpenDominion\Calculators\NetworthCalculator;
use OpenDominion\Helpers\DiscordHelper;
use OpenDominion\Helpers\MiscHelper;
use OpenDominion\Helpers\NotificationHelper;
use OpenDominion\Helpers\RaceHelper;
use OpenDominion\Helpers\UnitHelper;
use OpenDominion\Mappers\Dominion\InfoMapper;
use OpenDominion\Models\Race;
use OpenDominion\Services\Dominion\ProtectionService;
use OpenDominion\Services\Dominion\QueueService;
// use OpenDominion\Sim\Merfolk\LowSchools\Sim;
// use OpenDominion\Sim\Woodelf\LowSchools\Sim;
// use OpenDominion\Sim\Firewalker\Alchs\Sim;
// use OpenDominion\Sim\Icekin\R24\Base\Sim;
// use OpenDominion\Sim\Icekin\R24\Techs\Sim;
// use OpenDominion\Sim\Icekin\R24\TechsRr\Sim;
use OpenDominion\Sim\Icekin\R24\AlchMasonRr\Sim;
// use OpenDominion\Sim\Icekin\R24\TechsRrAlchMasonRr\Sim;
// use OpenDominion\Sim\Human\Converter\Base\Sim;
// use OpenDominion\Sim\Human\R24\Base\Sim;
use DB;

// use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
// use Doctrine\ORM\EntityManagerInterface;


class SimController extends AbstractDominionController
{
    public function getSim() {

      try {
        // $merfolk = new Sim();
        // $merfolk->runSim();
        $sim = new Sim();
        print 'START: ' . get_class($sim) . " " . now() . "<br /><br />";
        $sim->runSim();
      } catch(Exception $e) {
        print 'error: ' . $e->getMessage();
        print "<pre>";
        print_r($e->getTrace());
        print '</pre>';
      }

      print 'DONE: ' . now();
      exit();
      return view('pages.dominion.sim', [
          // 'discordHelper' => app(DiscordHelper::class),
          // 'infoMapper' => app(InfoMapper::class),
          // 'landCalculator' => app(LandCalculator::class),
          // 'miscHelper' => app(MiscHelper::class),
          // 'militaryCalculator' => app(MilitaryCalculator::class),
          // 'networthCalculator' => app(NetworthCalculator::class),
          // 'notificationHelper' => app(NotificationHelper::class),
          // 'opsCalculator' => app(OpsCalculator::class),
          // 'populationCalculator' => app(PopulationCalculator::class),
          // 'protectionService' => app(ProtectionService::class),
          // 'queueService' => app(QueueService::class),
          // 'raceHelper' => app(RaceHelper::class),
          // 'rangeCalculator' => app(RangeCalculator::class),
          // 'unitHelper' => app(UnitHelper::class),
          // 'races' => $races,
          // 'notifications' => $notifications
      ]);
    }

}
