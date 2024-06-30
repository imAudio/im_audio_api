<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_device';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'serial_number',
        'state',
        'id_device_color',
        'id_device_model',
        'id_worker',
        'device_content',
        'id_audio_center',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'state' => 'string',
    ];

    /**
     * Get the color associated with the device.
     */
    public function deviceColor()
    {
        return $this->belongsTo(DeviceColor::class, 'id_device_color');
    }
    public function deviceModel()
    {
        return $this->belongsTo(DeviceModel::class, 'id_device_model');
    }

    /**
     * Get the worker associated with the device.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    /**
     * Get the audio center associated with the device.
     */
    public function audioCenter()
    {
        return $this->belongsTo(AudioCenter::class, 'id_audio_center');
    }

    public function setSail()
    {
        return $this->hasOne(SetSail::class, 'id_device');
    }
}
