<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $table = 'skill';

    protected $primaryKey = 'id_skill';

    public $incrementing = true;

    protected $keyType = 'int';


    protected $fillable = [
        'content','id_master_audio','color'
    ];

    public function masterAudio()
    {
        return $this->belongsTo(MasterAudio::class, 'id_master_audio');
    }

}
