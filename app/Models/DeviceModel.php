<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device_model';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_device_model';

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
        'id_device_type',
        'content',
        'state',
    ];



    /**
     * Get the device type associated with the manufactured device.
     */
    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class, 'id_device_type');
    }
    public function deviceManufactured()
    {
        return $this->belongsTo(DeviceManufactured::class, 'id_device_manufactured');
    }
}
