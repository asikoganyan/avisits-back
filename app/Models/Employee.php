<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Employee extends Model
{
    private $mainPhotoPath;

    public function __construct(array $attributes = [])
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->mainPhotoPath =  "files".$ds."employee".$ds."images".$ds."photo".$ds;
        parent::__construct($attributes);
    }

    /**
     * Get employee by id
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getById($id)
    {
        $employee = self::query()->with(['salons'])->find($id);
        return $employee;
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'father_name',
        'photo',
        'birthday',
        'email',
        'phone',
        'address',
//        'deposit',
//        'bonuses',
//        'invoice_sum',
        'position_id',
        'public_position',
        'comment'
    ];

    protected $hidden = [
        'chain_id'
    ];

    /**
     * @param $value
     * @return string
     */
    public function getPhotoAttribute($value) {
        if($value)
            return $this->mainPhotoPath.$value;
        return $value;
    }

    /**
     * Relationship for get salons
     *
     * @return $this
     */
    public function salons()
    {
        return $this->hasMany('App\Models\SalonHasEmployees', 'employee_id', 'id')->with('salon');
    }

    public function position() {
        return $this->hasOne('App\Models\Position', 'id', 'position_id')->select(['id','title','description']);
    }

    public static function employees($chain,$filter = null)
    {
        $query = self::query();
        $query->select(['employees.id','first_name','last_name','father_name','photo','sex','birthday','position_id','public_position'])
            ->distinct();
        $query->with('position');
        $query->where('employees.chain_id','=',$chain);

        if($filter !== null) {
            /*when need to filter by ID of Salon*/
            if(isset($filter['salon_id']) && !empty($filter['salon_id'])) {
                $salonId = $filter['salon_id'];
                $query->join('salon_has_employees', function ($join) use($salonId) {
                    $join->on('employees.id', '=', 'salon_has_employees.employee_id')
                        ->where('salon_has_employees.salon_id', '=', $salonId);
                });
                /*when need to filter by Latitude Longitude*/
            }elseIf(isset($filter['location']) && !empty($filter['location'])) {
                $location = $filter['location'];
                if((isset($location['latitude']) && isset($location['longitude'])) && (!empty($location['longitude']) && !empty($location['latitude']))) {
                    $query->join('salon_has_employees', function ($join) {
                        $join->on('employees.id', '=', 'salon_has_employees.employee_id');
                    });
                    $query->join('salons', function ($join) use( $location ) {
                        $join->on('salons.id', '=', 'salon_has_employees.salon_id');
                        $join->where(['salons.longitude'=>$location['longitude'],'salons.latitude'=>$location['latitude']]);
                    });
                }
            }
            /*when need to filter by Address*/
            elseIf(isset($filter['address']) && !empty($filter['address'])) {
                $address = $filter['address'];
                $query->join('salon_has_employees', function ($join) {
                    $join->on('employees.id', '=', 'salon_has_employees.employee_id');
                });
                $query->join('salons', function ($join) use( $address ) {
                    $join->on('salons.id', '=', 'salon_has_employees.salon_id');
                    if(isset($address['country']) && !empty($address['country'])) {
                        $join->where('salons.country','=',$address['country']);
                    }
                    if(isset($address['city']) && !empty($address['city'])) {
                        $join->where('salons.city','=',$address['city']);
                    }
                    if(isset($address['address']) && !empty($address['address'])) {
                        $join->where('salons.address','=',$address['address']);
                    }
                    if(isset($address['street_number']) && !empty($address['street_number'])) {
                        $join->where('salons.street_number','=',$address['street_number']);
                    }
                });
            }
            /*filter by services*/
            if(isset($filter['services']) && count($filter['services']) > 0) {
                $count = count($filter['services']);
                $fServices = collect($filter['services'])->map(function($item){
                    return (integer)$item;
                });
                $query->join('salon_employee_services',function($join) use ($fServices,$count) {
                    $join->on("salon_has_employees.id","=","salon_employee_services.shm_id")
                        ->whereIn("salon_employee_services.id",function($innerQuery) use($fServices,$count){
                            $innerQuery->select(DB::raw("DISTINCT(Q1.id)"))
                                ->from("salon_employee_services as Q1")
                                ->whereIn("Q1.service_id",$fServices)
                                ->havingRaw("count(Q1.shm_id) = ".$count)
                                ->groupBy("Q1.shm_id");
                        });
                });
            }
        }
        $query->where('dismissed','=',0);
        return $query->get();
    }
}