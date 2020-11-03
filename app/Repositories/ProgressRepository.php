<?php
namespace App\Repositories;

use App\Models\Progress;
use App\Repositories\Core\Repository;

class ProgressRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed|string
     */
    function model()
    {
        return Progress::class;
    }

    public function resetProgress()
    {
        $this->model->newQuery()
            ->where('status' ,'!=', Progress::STATUS_UNANSWERED)
            ->update(['status' => Progress::STATUS_UNANSWERED]);
    }


}