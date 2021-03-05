<?php

namespace OpenDominion\Console\Commands\Game;

use Illuminate\Console\Command;
use OpenDominion\Console\Commands\CommandInterface;
use OpenDominion\Services\Dominion\AIService;
use OpenDominion\Services\Dominion\TickService;

class TickCommand extends Command implements CommandInterface
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'game:tick';

    /** @var string The console command description. */
    protected $description = 'Ticks the game';

    /** @var AIService */
    protected $aiService;

    /** @var TickService */
    protected $tickService;

    /**
     * GameTickCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->aiService = app(AIService::class);
        $this->tickService = app(TickService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        $this->tickService->tickDaily();
        $this->tickService->tickHourly();
        $this->tickService->updateDailyRankings();
        $this->aiService->executeAI();
    }
}
