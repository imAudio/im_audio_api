<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctor';


    protected $primaryKey = 'id_doctor';


    public $timestamps = true;


    protected $fillable = [
        'name',
        'finess',
        'rpps',
        'type',
        'created_at'
    ];
}
