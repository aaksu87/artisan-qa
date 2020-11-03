<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    public const STATUS_UNANSWERED = 0;
    public const STATUS_TRUE = 1;
    public const STATUS_FALSE = 2;

    public $timestamps = false;

    protected $fillable = [
        'question_id', 'status'
    ];

    public function getStatusAttribute($value)
    {
        return ['Unanswered','True','False'][$value];
    }



}
