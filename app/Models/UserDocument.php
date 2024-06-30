<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    protected $table = 'user_document';
    protected $primaryKey = 'id_document';

    protected $fillable = [
        'file_name', 'file_path', 'file_type', 'id_user','id_woker'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'id_worker');
    }

}
