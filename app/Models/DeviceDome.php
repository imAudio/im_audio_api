<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceDome extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device_dome';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_device_dome';

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
        'size',
        'state',
        'id_worker',
        'id_device_manufactured',
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
     * Get the worker associated with the device dome.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    /**
     * Get the manufactured device associated with the device dome.
     */
    public function deviceManufactured()
    {
        return $this->belongsTo(DeviceManufactured::class, 'id_device_manufactured');
    }
}
