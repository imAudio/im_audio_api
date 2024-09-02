<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $table = 'worker';

    protected $primaryKey = 'id_user';

    public $incrementing = false;

    protected $keyType = 'bigint';

    public $timestamps = false;

    protected $fillable = [
        'id_user', 'day_off',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
