<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'relate';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_mcq',
        'id_question',
        'id_worker',
    ];

    /**
     * Get the MCQ associated with the relation.
     */
    public function mcq()
    {
        return $this->belongsTo(Mcq::class, 'id_mcq');
    }

    /**
     * Get the question associated with the relation.
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'id_question');
    }

    /**
     * Get the worker associated with the relation.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }
}
