<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tuto extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tuto';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_tuto';

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
        'link',
        'id_master_audio',
        'device_content',
    ];

    /**
     * Get the master audio associated with the tuto.
     */
    public function masterAudio()
    {
        return $this->belongsTo(MasterAudio::class, 'id_master_audio');
    }
}
