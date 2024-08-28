<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSocialSecurity extends Model
{

    protected $table = 'patient_social_security';

    protected $primaryKey = 'id_patient';

    public $incrementing = false;


    protected $fillable = [
        'social_security_number', 'id_patient', 'date_open', 'date_close', 'situation', 'special_situation', 'cash_register_code'
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'id_patient');
    }

}
