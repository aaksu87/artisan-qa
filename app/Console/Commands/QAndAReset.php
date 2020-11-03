<?php

namespace App\Console\Commands;

use App\Services\ProgressService;
use Illuminate\Console\Command;

class QAndAReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the progress table to unanswered';

    /**
     * @var ProgressService $progressService
     */
    protected $progressService;

    /**
     * QAndAReset constructor.
     * @param ProgressService $progressService
     */
    public function __construct(ProgressService $progressService)
    {
        parent::__construct();
        $this->progressService = $progressService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->progressService->resetProgress();
    }
}
