<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientPhone extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patient_phone';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_patient_phone';

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
        'phone',
        'id_patient',
    ];



    /**
     * Get the patient that owns the note.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_patient');
    }
}
