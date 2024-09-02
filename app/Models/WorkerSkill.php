<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerSkill extends Model
{
    protected $table = 'worker_skill';


    protected $fillable = [
        'id_worker','id_skill','id_master_audio'
    ];


    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }
    public function skill()
    {
        return $this->belongsTo(Skill::class, 'id_skill');
    }
    public function masterAudio()
    {
        return $this->belongsTo(MasterAudio::class, 'id_master_audio');
    }
}
