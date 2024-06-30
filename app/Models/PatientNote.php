<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientNote extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patient_note';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_note_patient';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

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
        'content',
        'is_deleted',
        'id_worker',
        'id_patient',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the worker that wrote the note.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    /**
     * Get the patient that owns the note.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_patient');
    }
}
