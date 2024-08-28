<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDoctor extends Model
{
    protected $table = 'patient_doctor';


    protected $primaryKey = 'id_patient_doctor';


    public $timestamps = true;


    protected $fillable = [
        'id_worker',
        'id_user',
        'id_doctor',
        'date_prescription'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    public function worker()
    {
        return $this->belongsTo(User::class, 'id_worker');
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'id_doctor');
    }
}
