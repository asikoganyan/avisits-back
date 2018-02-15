<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SchedulePeriod extends Model
{
    protected $table = "schedule_periods";

    /**
     * Get period by id
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getById($id)
    {
        $period = self::query()->find($id);
        return $period;
    }

    /**
     * Add new schedule period
     *
     * @param $scheduleId
     * @param $start
     * @param $end
     * @return SchedulePeriod|array
     */
    public static function add($scheduleId, $start, $end)
    {
        $period = new self();
        $period->schedule_id = $scheduleId;
        $period->start = $start;
        $period->end = $end;
        if ($period->save()) {
            return $period;
        }
        return [];
    }

    /**
     * Edit schedule period
     *
     * @param $id
     * @param $scheduleId
     * @param $start
     * @param $end
     * @return SchedulePeriod|array|\Illuminate\Database\Eloquent\Collection|Model|null|static[]
     */
    public static function edit($id, $scheduleId, $start, $end)
    {
        $period = self::getById($id);
        $period->schedule_id = $scheduleId;
        $period->start = $start;
        $period->end = $end;
        if ($period->save()) {
            return $period;
        }
        return [];
    }

    /**
     * Delete except ids
     *
     * @param $ids
     */
    public static function deleteExceptIds($scheduleId,$ids)
    {
        self::query()->where('schedule_id',$scheduleId)->whereNotIn('id', $ids)->delete();
    }

    /**
     * Format start attribute
     *
     * @param $value
     * @return string
     */
    public function getStartAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    /**
     * Format end attribute
     *
     * @param $value
     * @return string
     */
    public function getEndAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }
}
