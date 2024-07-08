<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceTransfer extends Model

{protected $table = 'device_transfer';

    public $timestamps = true;

    protected $fillable = [
        'id_device','id_audio_center','id_worker'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device');
    }
    public function audioCenter()
    {
        return $this->belongsTo(AudioCenter::class, 'id_audio_center');
    }
    public function worker()
    {
        return $this->belongsTo(User::class, 'id_worker');
    }
}
