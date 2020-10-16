<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Questions;
use App\Repositories\Core\Repository;

class QuestionsRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed|string
     */
    function model()
    {
        return Questions::class;
    }


    public function unansweredQuestions(): Collection
    {
        return $this->model->newQuery()
            ->where('status', Questions::STATUS_UNANSWERED)
            ->get();
    }

    public function trueQuestions(): Collection
    {
        return $this->model->newQuery()
            ->where('status', Questions::STATUS_TRUE)
            ->get();
    }

    public function getQuestionByText($questionText)
    {
        return $this->findBy('question',$questionText);
    }

    public function truncateTable()
    {
        $this->model::truncate();
    }


}