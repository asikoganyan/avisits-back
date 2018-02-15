<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'father_name',
        'sex',
        'birthday',
        'email',
        'phone',
        'card_number',
        'card_number_optional',
        'comment',
        'deposit',
        'bonuses',
        'invoice_sum',
        'created_at',
        'updated_at',
    ];
    protected $hidden = [
    ];

    public static function getByEmailOrPhone($filter)
    {
        return self::select(['id','first_name','last_name','email','phone','comment'])
            ->where(["email"=>$filter])
            ->orWhere(["phone"=>$filter])
            ->first();
    }
}