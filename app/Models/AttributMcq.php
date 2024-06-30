<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributMcq extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribut_mcq';

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
        'state',
        'id_mcq',
        'id_patient',
        'id_worker',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'state' => 'string',
    ];

    /**
     * Get the MCQ associated with the attribute.
     */
    public function mcq()
    {
        return $this->belongsTo(Mcq::class, 'id_mcq');
    }

    /**
     * Get the patient associated with the attribute.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_patient');
    }

    /**
     * Get the worker associated with the attribute.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }
}
