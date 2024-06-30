<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToDoList extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'to_do_list';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_to_do_list';

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
        'content',
        'category',
        'date',
        'is_deleted',
        'id_worker',
        'id_audio_center',
        'id_user',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the worker that owns the to-do item.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    /**
     * Get the user that created the to-do item.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the audio center associated with the to-do item.
     */
    public function audioCenter()
    {
        return $this->belongsTo(AudioCenter::class, 'id_audio_center');
    }
}
