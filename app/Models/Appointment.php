<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'salon_id',
        'employee_id',
        'price',
        'from_time',
        'to_time',
        'day',
        'email',
        'phone',
        'client_id',
        'created_at',
        'updated_at'
    ];
    protected $hidden = [
        'user_id'
    ];

    public static function getAppointments($filter = null){
        $query = self::query();
        $query->select([
            'from_time',
            'to_time',
            'day'
        ]);
        if($filter !== null){
            $where = [];
            if(isset($filter['date']) && !empty($filter['date'])){
                $where['day'] = Carbon::parse($filter['date']);
            }
            if(isset($filter['salon_id']) && !empty($filter['salon_id'])){
                $where['salon_id'] = $filter['salon_id'];
            }
            if(isset($filter['employee_id']) && !empty($filter['employee_id'])) {
                $where['employee_id'] = $filter['employee_id'];
            }
            if(!empty($where)){
                $query->where($where);
            }
        }
        $query->orderBy('from_time','asc');
        return $query->get();
    }
}