<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Question;
use App\Repositories\Core\Repository;

class QuestionRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed|string
     */
    function model()
    {
        return Question::class;
    }

    /**
     * @return Collection
     */
    public function unansweredQuestions(): Collection
    {
        return $this->model->newQuery()
            ->where('status', Question::STATUS_UNANSWERED)
            ->get();
    }

    /**
     * @return Collection
     */
    public function trueQuestions(): Collection
    {
        return $this->model->newQuery()
            ->where('status', Question::STATUS_TRUE)
            ->get();
    }

    /**
     * @param $questionText
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getQuestionByText($questionText)
    {
        return $this->findBy('question',$questionText);
    }

    public function resetProgress()
    {
        $this->model->newQuery()
            ->where('status' ,'!=', Question::STATUS_UNANSWERED)
            ->update(['status' => Question::STATUS_UNANSWERED]);
    }


}