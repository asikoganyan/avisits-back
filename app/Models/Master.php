<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'father_name',
        'photo',
        'birthday',
        'email',
        'phone',
        'viber',
        'whatsapp',
        'address',
        'comment',
        'user_id',
        'created_at',
        'updated_at'
    ];
}
