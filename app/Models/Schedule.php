<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Schedule extends Model
{
    protected $table = "schedules";

    protected $fillable = [
        'salon_id',
        'employee_id',
        'start_time',
        'end_time',
        'day',
        'working_status',
        'created_at',
        'updated_at'
    ];
    protected $hidden = [

    ];

    /**
     * Get employee by id
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getById($id)
    {
        $schedule = self::query()->with(['periods'])->find($id);
        return $schedule;
    }

    /**
     * Get employee by employee id and num of day
     *
     * @param $salonId
     * @param $employeeId
     * @param $numOfDay
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getByEmployeeIdAndNumOfDay($salonId, $employeeId, $numOfDay)
    {
        $schedule = self::query()->where('salon_id', $salonId)->where('employee_id', $employeeId)->where('num_of_day', $numOfDay)->get();
        return $schedule;
    }

    /**
     * Create employee schedule
     *
     * @param $salonId
     * @param $employeeId
     * @param $type
     * @param $workingDays
     * @param $weekend
     * @param $date
     * @return Schedule|array
     */
    public static function create($salonId, $employeeId, $type, $workingStatus, $workingDays = 0, $weekend = 0, $numOfDay = 0, $date)
    {
        $schedule = new self();
        $schedule->salon_id = $salonId;
        $schedule->employee_id = $employeeId;
        $schedule->type = $type;
        if ($type == 2) {
            $schedule->working_status = $workingStatus;
        }
        if ($workingDays) {
            $schedule->working_days = $workingDays;
        }
        if ($weekend) {
            $schedule->weekend = $weekend;
        }
        if ($numOfDay) {
            $schedule->num_of_day = $numOfDay;
        }
        $schedule->date = $date;
        if ($schedule->save()) {
            return $schedule;
        }
        return [];
    }

    /**
     * Edit employee schedule
     *
     * @param $scheduleId
     * @param $salonId
     * @param $employeeId
     * @param $type
     * @param $workingDays
     * @param $weekend
     * @param $date
     * @return Schedule|array|\Illuminate\Database\Eloquent\Collection|Model|null|static[]
     */
    public static function edit($scheduleId, $salonId, $employeeId, $type, $workingStatus, $workingDays = 0, $weekend = 0, $numOfDay = 0, $date)
    {
        $schedule = self::getById($scheduleId);
        $schedule->salon_id = $salonId;
        $schedule->employee_id = $employeeId;
        $schedule->type = $type;
        if ($type == 2) {
            $schedule->working_status = $workingStatus;
        }
        if ($workingDays) {
            $schedule->working_days = $workingDays;
        }
        if ($weekend) {
            $schedule->weekend = $weekend;
        }
        if ($numOfDay) {
            $schedule->num_of_day = $numOfDay;
        }
        $schedule->date = $date;
        if ($schedule->save()) {
            return $schedule;
        }
        return [];
    }

    /**
     * Relationship for schedule periods
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function periods()
    {
        return $this->hasMany('App\Models\SchedulePeriod', 'schedule_id', 'id');
    }

    public static function getWorkingHours($filter = null)
    {
        $params = [];
        $t = 0;
        $dayOfWeek = 0;
        if($filter !== null) {
            if(isset($filter['date']) && !empty($filter['date'])) {
                $params[] = $filter['date'];
                $dayOfWeek = Carbon::parse($filter['date'])->dayOfWeek;
                if($dayOfWeek === 0){
                    $dayOfWeek = 7;
                }
                $t++;
            }
            if(isset($filter['salon_id']) && !empty($filter['salon_id'])) {
                $params[] = $filter['salon_id'];
                $t++;
            }
            if(isset($filter['employee_id']) && !empty($filter['employee_id'])) {
                $params[] = $filter['employee_id'];
                $t++;
            }

            if(count($params) !== $t ){
                return [];
            }
        }
        $query = self::query();
        $query->from('schedules AS t1');
        $query->select(["t1.id","t1.salon_id","t1.employee_id","t1.working_status","t1.type","t1.working_days","t1.weekend","t1.num_of_day","t1.date"]);
        $query->join(DB::raw("(SELECT t2.salon_id,"
            ."t2.employee_id, "
            ."t2.type, "
            ."t2.date AS maxdate "
            ."FROM "
            ."`schedules` t2 "
            ."WHERE t2.date<=? "
            ."AND t2.salon_id=? "
            ."AND t2.employee_id=? "
            ."ORDER BY t2.date DESC "
            ."LIMIT 1) t3"),
            function ($join) use($dayOfWeek){
                $join->on("t1.salon_id","=","t3.salon_id")
                    ->where("t1.employee_id","=",DB::raw("t3.employee_id"))
                    ->where("t1.date","=",DB::raw("t3.maxdate"))
                    ->whereRaw("CASE WHEN `t3`.`type`= 1 THEN  t1.num_of_day = ".(integer)($dayOfWeek)." ELSE `t1`.`type` =`t3`.`type` END");
            })
            ->addBinding($params);
        $query->with(['periods'=>function($q){
            $q->select(['id','schedule_id','start','end'])->orderBy('start','asc');
        }]);
        return $query->first();
    }
}
