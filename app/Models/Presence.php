<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
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
    protected $primaryKey = 'id_presence';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    protected $table = 'presence';


    protected $fillable = [
        'id_worker', 'id_audio_center','days','hourly'
    ];


    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

    public function audioCenter()
    {
        return $this->belongsTo( AudioCenter::class, 'id_audio_center');
    }

}

