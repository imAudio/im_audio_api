<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';


    protected $primaryKey = 'id_company';


    public $timestamps = true;


    protected $fillable = [
        'name',
        'postal_code',
        'city',
        'rpps',
        'phone',
        'email',
        'manager',
        'created_at',
        'updated_at'
    ];
}
