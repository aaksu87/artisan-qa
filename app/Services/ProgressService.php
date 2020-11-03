<?php

namespace App\Services;

use App\Models\Progress;
use App\Repositories\ProgressRepository;

class ProgressService
{
    /**
     * @var ProgressRepository
     */
    protected $progressRepository;

    public function __construct(ProgressRepository $progressRepository)
    {
        $this->progressRepository = $progressRepository;
    }

    /**
     * @param $question
     * @param $userAnswer
     */
    public function setStatus($question, $userAnswer)
    {
        $status = ($question->answer == $userAnswer) ? Progress::STATUS_TRUE : Progress::STATUS_FALSE;
        $question->progress()->update(['status'=>$status]);
    }

    public function resetProgress()
    {
        $this->progressRepository->resetProgress();
    }

}