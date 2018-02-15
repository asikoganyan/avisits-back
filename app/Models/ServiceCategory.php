<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Service;

class ServiceCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'title',
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
     * Get service category by id
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getById($id)
    {
        $serviceCategory = self::query()
            ->orderBy('order','desc')
            ->with(['groups'=>function($query){
                $query->with('services');
            }])->find($id);
        return $serviceCategory;
    }

    /**
     * Get service categories by parent id
     *
     * @param $parent_id
     * @return $this
     */
    public static function getByParentId($parent_id) {
        $serviceCategories = self::query()
            ->where('parent_id',$parent_id)
            ->orderBy('order','desc')
            ->get();
        return $serviceCategories;
    }

    /**
     * Relationship for get groups
     *
     * @return $this
     */
    public function groups()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
//            ->with('services');
    }

    /**
     * Relationship for get services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'service_category_id', 'id');
    }

    public function category()
    {
        return $this->hasMany(self::class, 'id','parent_id');
    }

    public static function getCategoriesWithServices($chain,$filter){
        $query = self::query();
        $query->select(["service_categories.id","service_categories.title"])
            ->distinct()
            ->where(["service_categories.chain_id"=>$chain])
            ->join("service_categories as SG",function($join){
                $join->on("service_categories.id","=","SG.parent_id");
            })
            ->with(['groups'=>function($groups) use($filter){
                $groups->select(["id","parent_id","title"])
                    ->with(['services'=>function($service) use($filter){
                        $service->select(["id","service_category_id","title","description","duration as default_duration"]);
                        $service->with('minMaxPrices');
                        if(isset($filter["address"])) {
                            if(isset($filter["address"]["city"]) && !empty($filter["address"]["city"])){
                                $service->whereIn("id", function ($subQuery) use ($filter) {
                                    $subQuery->select("service_id")
                                        ->distinct()
                                        ->from("salon_has_services")
                                        ->join("salons", function ($join) use ($filter) {
                                            $join->on("salon_has_services.service_id", "=", "salons.id")
                                                ->where(["salons.city" => $filter["address"]['city']]);
                                        });
                                });
                            }
                            if(isset($filter["employees"]) && count($filter["employees"]) > 0) {
                                $service->whereIn("id", function ($subQuery) use ($filter) {
                                    $subQuery->select("salon_employee_services.service_id")
                                        ->distinct()
                                        ->from("salon_has_employees")
                                        ->join("salon_employee_services",function($join) use($filter) {
                                            $join->on("salon_has_employees.id","=","salon_employee_services.shm_id")
                                            ->whereIn("salon_has_employees.employee_id",$filter["employees"]);
                                        });
                                });
                            }
                        }
                    }]);
            }]);
        return $query->get();

    }
}
