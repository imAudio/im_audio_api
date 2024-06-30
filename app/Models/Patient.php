<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patient';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'bigint';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'phone',
        'is_callback_request',
        'is_assured',
        'date_birth',
        'address',
        'postal_code',
        'city',
        'social_security_number',
        'sex',
        'id_worker',
        'id_audio_center',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function event()
    {
        return $this->hasMany(Event::class, 'id_user');
    }

    public function patientNote()
    {
        return $this->hasMany(PatientNote::class, 'id_patient');
    }
    public function setSail()
    {
        return $this->hasMany(SetSail::class, 'id_patient');
    }
    public function attributMcq()
    {
        return $this->hasMany(AttributMcq::class, 'id_patient');
    }
}
