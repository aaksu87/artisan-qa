<?php

namespace App\Console\Commands;

use App\Services\QuestionService;
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
    protected $description = 'Truncate the question table for reset Q&A process';

    /**
     * @var QuestionService $questionService
     */
    protected $questionService;

    /**
     * QAndAReset constructor.
     * @param QuestionService $questionService
     */
    public function __construct(QuestionService $questionService)
    {
        parent::__construct();
        $this->questionService = $questionService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->questionService->resetProgress();
    }
}
