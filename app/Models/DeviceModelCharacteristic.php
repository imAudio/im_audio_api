<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceModelCharacteristic extends Model

{protected $table = 'device_model_characteristic';

    public $timestamps = true;

    protected $fillable = [
        'device_content','id_device_characteristic','created_at','update_at'
    ];
    public function deviceModel()
    {
        return $this->belongsTo(DeviceModel::class, 'id_device_model');
    }
    public function deviceCharacteristic()
    {
        return $this->belongsTo(DeviceCharacteristic::class, 'id_device_characteristic');
    }
}
