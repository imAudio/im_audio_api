<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCharacteristic extends Model

{protected $table = 'device_characteristic';

    public $timestamps = true;

    protected $primaryKey = 'id_device_characteristic';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'information','created_at','update_at'
    ];

}
