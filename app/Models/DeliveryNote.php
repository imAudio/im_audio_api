<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $table = 'delivery_note';

    protected $primaryKey = 'id_delivery_note';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'number_device',
        'id_audio_center',
        'id_worker',

    ];

    public function audioCenter()
    {
        return $this->belongsTo(AudioCenter::class, 'id_audio_center');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    public function deviceManufactured()
    {
        return $this->belongsTo(DeviceManufactured::class, 'id_device_manufactured');
    }
}
