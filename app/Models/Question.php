<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public const STATUS_UNANSWERED = 0;
    public const STATUS_TRUE = 1;
    public const STATUS_FALSE = 2;

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
        'question', 'answer', 'status'
    ];

    /**
     * @param $value
     * @return string
     */
    public function getStatusAttribute($value)
    {
        return ['Unanswered','True','False'][$value];
    }

}
