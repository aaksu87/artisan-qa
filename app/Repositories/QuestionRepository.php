<?php
namespace App\Repositories;

use App\Models\Progress;
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
     * @return \Illuminate\Database\Eloquent\Builder[]|Collection|\Illuminate\Support\Collection
     */
    public function allDataWithProgress(){
        return $this->model->newQuery()
            ->with('progress')
            ->get()->map(function($items){
                $data['id'] = $items->id;
                $data['question'] = $items->question;
                $data['status'] = $items->progress['status'];
           return $data;
        });
    }

    /**
     * @return Collection
     */
    public function unansweredQuestions(): Collection
    {
        return $this->model->newQuery()->whereHas('progress', function ($query) {
            return $query->where('status', '=', Progress::STATUS_UNANSWERED);
        })->get();
    }

    /**
     * @return Collection
     */
    public function trueQuestions(): Collection
    {
        return $this->model->newQuery()->whereHas('progress', function ($query) {
            return $query->where('status', '=', Progress::STATUS_TRUE);
        })->get();
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
            ->where('status' ,'!=', Progress::STATUS_UNANSWERED)
            ->update(['status' => Progress::STATUS_UNANSWERED]);
    }


}