<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioCenter extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'audio_center';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_audio_center';

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
        'name',
        'city',
        'address',
        'postal_code',
        'id_master_audio',
    ];

    /**
     * Get the master audio associated with the audio center.
     */
    public function masterAudio()
    {
        return $this->belongsTo(MasterAudio::class, 'id_master_audio');
    }
    public function events()
    {
        return $this->hasMany(Event::class, 'id_audio_center');
    }
}
