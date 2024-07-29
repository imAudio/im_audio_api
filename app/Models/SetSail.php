<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetSail extends Model
{

    protected $table = 'set_sail';


    protected $fillable = [
        'size_earpiece','id_worker','id_device','id_device_dome','id_patient','side'
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'id_worker');
    }
    public function dome()
    {
        return $this->belongsTo(DeviceDome::class, 'id_device_dome');
    }
    public function patient()
    {
        return $this->belongsTo(User::class, 'id_patient');
    }
    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device');
    }
}
