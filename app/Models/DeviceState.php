<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceState extends Model
{
    protected $table = 'device_state';

    public $timestamps = true;

    protected $fillable = [
        'id_device','state','id_worker','information'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device');
    }
    public function worker()
    {
        return $this->belongsTo(User::class, 'id_worker');
    }
}
