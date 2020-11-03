<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /**
     * @var string
     */
    protected $table = 'questions';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'question', 'answer'
    ];

    public function progress()
    {
        return $this->hasOne(Progress::class,'question_id');
    }

}
