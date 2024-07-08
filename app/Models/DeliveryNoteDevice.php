<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteDevice extends Model
{
    protected $table = 'delivery_note_device';


    public $timestamps = true;

    protected $fillable = [
        'id_delivery_note',
        'id_device',
        'id_worker',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class, 'id_delivery_note');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device');
    }
}
