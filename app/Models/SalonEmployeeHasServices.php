<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SalonEmployeeHasServices extends Model
{
    protected $table = "salon_employee_services";

    protected $fillable = [
        'shm_id',
        'service_id',
        'price',
        'duration'
    ];
    protected $hidden = [

    ];

    public static function getSumOfDuration($salonId,$employeeId, array $services)
    {
        $query = self::query();
        $query->select(DB::raw("salon_has_employees.salon_id,salon_has_employees.employee_id, SUM(salon_employee_services.duration) as duration"));
        $query->join("salon_has_employees",function ($join) use($salonId,$employeeId,$services) {
                $join->on("shm_id","=","salon_has_employees.id")
                ->where(["salon_has_employees.salon_id"=>$salonId,"salon_has_employees.employee_id"=>$employeeId])
                ->whereIn("service_id",$services);
            });
        $query->groupBy(["salon_has_employees.salon_id","salon_has_employees.employee_id"]);
        $res = $query->first();
        if($res){
            return (integer)$res['duration'];
        }
        return 0;
    }
}