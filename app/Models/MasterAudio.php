<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAudio extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_audio';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_worker';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_worker',
        'is_master_audio',
        'is_master',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_master_audio' => 'boolean',
        'is_master' => 'boolean',
    ];

    /**
     * Get the worker that owns the master audio.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }
}
