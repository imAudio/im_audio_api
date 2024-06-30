<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_event';

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_event',
        'start',
        'end',
        'description',
        'state',
        'days',
        'weekly_start',
        'weekly_end',
        'id_worker',
        'id_type_event',
        'id_user',
        'id_audio_center',
        'created',
        'updated',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'weekly_start' => 'time',
        'weekly_end' => 'time',
    ];

    /**
     * Get the worker that owns the event.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    /**
     * Get the user that created the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the audio center associated with the event.
     */
    public function audioCenter()
    {
        return $this->belongsTo(AudioCenter::class, 'id_audio_center');
    }

    /**
     * Get the type of event.
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'id_type_event');
    }
}
