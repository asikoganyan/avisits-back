<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Salon extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'img', 'country', 'city', 'address', 'street_number', 'latitude', 'longitude', 'current_time', 'user_id', 'chain_id', 'notify_about_appointments'
    ];

    public static function def_notifications_about_appointments($key = null)
    {
        $notifications = [
            "1h11" => "В день визита за 1 час, не позже 11",
            "2h11" => "В день визита за 2 час, не позже 11",
            "3h11" => "В день визита за 3 час, не позже 11",
            "1d19" => "За 1 день в 19 часов",
            "1d12" => "За 1 день в 12 часов",
            "2d12" => "За 2 дня в 12 часов",
            "3d12" => "За 3 дня в 12 часов",
            "7d12" => "За 7 дней в 12 часов"
        ];

        if ($key !== null && isset($notifications[$key])) {
            return $notifications[$key];
        }
        return $notifications;
    }

    /**
     * Get salon by id
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getById($id)
    {
        $salon = self::query()->with(['schedule'])->find($id);
        return $salon;
    }

    public static function getAll()
    {
        return self::orderBy('id', 'desc')->get();
    }

    /**
     * Get salons by chain id
     *
     * @param $chainId
     * @return mixed
     */
    public static function getByChainId($chainId)
    {
        $salons = self::where('chain_id', $chainId)->get();
        return $salons;
    }

    public function getImgAttribute($value)
    {
        $ds = DIRECTORY_SEPARATOR;
        return 'files' . $ds . 'salons' . $ds . 'images' . $ds . 'main' . $ds . $value;
    }

    public function getLatitudeAttribute($value)
    {
        return (float)$value;
    }

    public function getLongitudeAttribute($value)
    {
        return (float)$value;
    }

    public function getNotifyAboutAppointmentsAttribute($value)
    {
        return explode(',', $value);
    }

    /**
     * Relationship for salon schedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schedule()
    {
        return $this->hasMany('App\Models\SalonSchedule', 'salon_id', 'id');
    }

    public static function salonsCities($chain)
    {
        return self::select(['city'])
            ->distinct()
            ->where(['chain_id' => $chain])
            ->orderBy('city', 'asc')
            ->get();
    }

    public static function salons($chain, $filter = null)
    {
        $query = self::query();
        $query->select(['id', 'title', 'img', 'country', 'city', 'address', 'street_number', 'latitude', 'longitude']);
        $query->where('chain_id', '=', $chain);
        if ($filter !== null) {
            if (isset($filter['city']) && !empty($filter['city'])) {
                $query->where('city', '=', $filter['city']);
            }
            if ((isset($filter['services']) && count($filter['services']) > 0) && ( !isset($filter['employee_id']) || count($filter['employee_id']) <= 0)) {
                $services = $filter['services'];
                $query->whereIn("id", function ($salons) use($services) {
                    $salons->from("salon_has_services as shs")
                        ->select("shs.salon_id")
                        ->whereIn("shs.service_id",$services)
                        ->havingRaw("count(shs.service_id) = " . count($services))
                        ->groupBy("shs.salon_id");
                });
            }
            if((isset($filter['employee_id']) && count($filter['employee_id']) > 0)){
                $query->whereIn("id",function($subQuery) use($filter){
                    $subQuery->select("A.salon_id")
                        ->distinct()
                        ->from("salon_has_employees as A")
                        ->where("A.employee_id",$filter["employee_id"]);
                        if((isset($filter['services']) && count($filter['services']) > 0)){
                            $subQuery->join("salon_employee_services as B",function($join){
                                $join->on("A.id","=","B.shm_id");
                            })
                                ->whereIn("B.service_id",$filter["services"])
                                ->groupBy("B.shm_id")
                                ->havingRaw("count(B.service_id) = " . count($filter["services"]));
                        }
                });
                //dd($query->toSql());
            }
        }
        return $query->get();
    }
}
