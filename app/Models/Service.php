<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'service_category_id',
        'title',
        'description',
        'duration',
        'available_for_online_recording',
        'only_for_online_recording',
        'order',
        'created_at',
        'updated_at'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'chain_id',
    ];

    /**
     * Get service by id
     *
     * @param $id
     * @return mixed
     */
    public static function getById($id)
    {
        $service = self::query()
            ->orderBy('order','desc')
            ->with(['servicePrice'])
            ->find($id);
        return $service;
    }

    /**
     * Relationship for get service prices
     *
     * @return $this
     */
    public function servicePrice()
    {
        return $this->hasMany('App\Models\ServicePrice', 'service_id', 'id');
    }

    public function minMaxPrices () {
        return $this->hasOne('App\Models\ServicePrice', 'service_id', 'id')
            ->select(DB::raw("MIN(price) as min_price,MAX(max_price) as max_price , service_id"))->groupBy('service_id');
    }

    public function service_category(){
        return $this->hasOne('App\Models\ServiceCategory','id','service_category_id')
            ->orderBy('order','desc')
            ->select(['id','parent_id','title']);
    }
    public static function getServices($chain_id,$filter = null) {
        $data = [];
        $response = [];
        if($filter !== null) {
            $salonId = isset($filter['salon_id']) ? $filter['salon_id']: null;
            if(isset($filter['employees']) && count($filter['employees'])) {
                $query = Employee::query();
                $select = [
                    'salon_has_employees.employee_id',
                    'salon_employee_services.price',
                    'salon_employee_services.duration',
                    'services.id',
                    'services.service_category_id',
                    'services.title',
                    'services.duration as default_duration',
                    'services.description',
                    'services.available_for_online_recording',
                    'services.only_for_online_recording'
                ];
                $query->select($select);
                $query->selectRaw("GROUP_CONCAT(DISTINCT CONCAT('{\"id\": ',service_categories.id,',','\"parent_id\": ',COALESCE(service_categories.parent_id,'null'),',','\"title\": ','\"',service_categories.title,'\"','}')) as service_category");
                $query->distinct();
                $query->groupBy([
                    'salon_has_employees.employee_id',
                    'salon_employee_services.price',
                    'salon_employee_services.duration',
                    'services.id',
                    'services.service_category_id',
                    'services.title',
                    'default_duration',
                    'services.description',
                    'services.available_for_online_recording',
                    'services.only_for_online_recording'
                ]);
                $employees = $filter['employees'];
                $query->join('salon_has_employees', function ($join) use($salonId,$employees) {
                    $join->on('employees.id', '=', 'salon_has_employees.employee_id');
                        if($salonId){
                            $join->where('salon_has_employees.salon_id', '=', $salonId);
                        }
                    $join->whereIn('employee_id',$employees);
                });
                $query->join('salon_employee_services',function($join) {
                    $join->on('salon_has_employees.id','=','salon_employee_services.shm_id');
                });
                $query->join('services',function($join) {
                    $join->on('services.id','=','salon_employee_services.service_id');
                });
                $query->join('service_categories',function($join) {
                    $join->on('services.service_category_id','=','service_categories.id');
                });
                $result = $temp = $query->get();
                $data = collect($result)->map(function($item){
                    $item->service_category = \GuzzleHttp\json_decode($item->service_category);
                    return $item;
                });
                $employees = [];
                foreach ($data as $item) {
                    if(!array_key_exists($item->employee_id,$employees)) {
                        $employees[$item->employee_id] = [
                            "employee_id" => $item->employee_id,
                            "service_groups" => []
                        ];
                    }
                    if(!array_key_exists($item->service_category->id,$employees[$item->employee_id]['service_groups'])){
                        $employees[$item->employee_id]['service_groups'][$item->service_category->id] = $item->service_category;
                        $employees[$item->employee_id]['service_groups'][$item->service_category->id]->services = [];
                    }
                    $temp = clone $item;
                    unset($temp->service_category);
                    unset($temp->employee_id);
                    $tempServiceModel = new Service($temp->toArray());
                    $temp['min_max_prices'] = $tempServiceModel->minMaxPrices()
                        ->first();
                    array_push($employees[$item->employee_id]['service_groups'][$item->service_category->id]->services,$temp);
                };
                $employees = array_values($employees);
                foreach ($employees as &$e) {
                    $e['service_groups'] = array_values($e['service_groups']);
                }
                $response['employees'] = $employees;
            }
            else {
                $query = ServiceCategory::query()->from("service_categories as Q1")
                    ->select([
                        'Q1.id',
                        'Q1.parent_id',
                        'Q1.title'
                    ]);
                $query->distinct();
                $query->whereNull('Q1.parent_id');
                $query->where(['Q1.chain_id'=>$chain_id]);
                $query->join('service_categories as Q2', function ($join) use($chain_id) {
                    $join->on('Q2.parent_id','=','Q1.id');
                });
                $query->join('services', function ($join) use($chain_id) {
                    $join->on('Q2.id','=','services.service_category_id')->whereNotNull("Q2.parent_id");
                });
                $query->join('salon_has_services as SHS', function ($join) use($salonId) {
                    $join->on('SHS.service_id','=','services.id');
                        if($salonId){
                           $join->where(["salon_id"=>$salonId]);
                        }
                });
                $query->with(['groups'=>function($g) use($salonId) {
                    $g->select(["id","parent_id","title"])
                        ->with(['services'=>function($s) use($salonId) {
                            $s->select(['services.id',
                                'services.service_category_id',
                                'services.title',
                                'services.duration as default_duration',
                                'services.description',
                                'services.available_for_online_recording',
                                'services.only_for_online_recording'])
                                ->with("minMaxPrices")
                                ->join('salon_has_services as SHS2', function ($join) use($salonId) {
                                    $join->on('SHS2.service_id','=','services.id');
                                        if($salonId){
                                            $join->where(["salon_id"=>$salonId]);
                                        }
                                });
                        }])->whereIn("service_categories.id",function($subQ) use($salonId){
                            $subQ->select("service_category_id")
                                ->from("services as S1")
                                ->join('salon_has_services as SHS2', function ($join) use($salonId) {
                                    $join->on('S1.id','=','SHS2.service_id');
                                    if($salonId){
                                        $join->where(['salon_id'=>$salonId]);
                                    }
                                });
                        });
                }]);
                $response = ["categories"=>$query->get()];
            }
            if(isset($filter['salon_id'])) {

            }
        }
        return $response;
    }
}
