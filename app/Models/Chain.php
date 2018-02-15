<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'img',
        'phone_number',
        'created_at',
        'updated_at'
    ];
    protected $hidden = [
        'w_color',
        'w_group_by_category',
        'w_show_any_employee',
        'w_step_display',
        'w_step_search',
        'w_let_check_steps',
        'w_steps_g',
        'w_steps_service',
        'w_steps_employee',
        'w_contact_step',
        'user_id'
    ];
    protected $appends = ['salonsCount'];

    public static function getContactSteps(){
        return [
            'at_first',
            'after_address',
            'at_the_end'
        ];
    }
    /**
     * Get chain by id
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getById($id)
    {
        $chain = self::query()->select([
            'id',
            'title',
            'img',
            'phone_number',
            'created_at',
            'updated_at'
        ])->with(['levels'])->find($id);
        return $chain;
    }

    /**
     * Get salons count attribute
     *
     * @return int
     */
    public function getSalonsCountAttribute()
    {
        return count($this->salons);
    }

    /**
     * Relationship for get salons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salons()
    {
        return $this->hasMany('App\Models\Salon', 'chain_id', 'id');
    }

    /**
     * Relationship for get levels
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function levels()
    {
        return $this->hasMany('App\Models\ChainPriceLevel', 'chain_id', 'id');
    }

    /**
     * @param $value
     * @return string
     *
     */
    public function getImgAttribute($value) {
        if(!$value){
            return null;
        }
        $ds = DIRECTORY_SEPARATOR;
        return 'files'.$ds.'chains'.$ds.'images'.$ds.'main'.$ds.$value;
    }
}
