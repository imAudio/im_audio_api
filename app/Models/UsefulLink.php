<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsefulLink extends Model
{
    protected $table = 'useful_link';

    protected $primaryKey = 'id_useful_link';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_useful_link',
        'wording',
        'link',
        'id_worker',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }
}
