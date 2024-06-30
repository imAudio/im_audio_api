<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceColor extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device_color';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_device_color';

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
    protected $keyType = 'bigint';

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
        'content',
        'id_device_manufactured',
        'id_worker',
    ];

    /**
     * Get the manufactured device associated with the device color.
     */
    public function deviceManufactured()
    {
        return $this->belongsTo(DeviceManufactured::class, 'id_device_manufactured');
    }

    /**
     * Get the worker associated with the device color.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }
}
