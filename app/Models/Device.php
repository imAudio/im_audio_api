<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'device';

    protected $primaryKey = 'id_device';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'serial_number',
        'id_device_color',
        'id_device_model',
        'id_worker',
        'device_content',
    ];

    public function deviceColor()
    {
        return $this->belongsTo(DeviceColor::class, 'id_device_color');
    }

    public function deviceModel()
    {
        return $this->belongsTo(DeviceModel::class, 'id_device_model');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    public function setSail()
    {
        return $this->hasOne(SetSail::class, 'id_device');
    }



    public function deviceState()
    {
        return $this->hasMany(DeviceState::class, 'id_device');
    }

    public function deviceTransfer()
    {
        return $this->hasMany(DeviceTransfer::class, 'id_device');
    }



    public function latestDeviceTransfer()
    {
        return $this->hasOne(DeviceTransfer::class, 'id_device')->latest('created_at');
    }
}
