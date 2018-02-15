<?php

namespace App\Http\Services\Widget;

use Illuminate\Support\Debug\Dumper;

class WidgetEmployeeSchedule
{
    public static function getTimeToInteger($value)
    {
        $times = explode(':', $value);
        $times = collect($times)->map(function ($item) {
            return (integer)$item;
        });
        return ($times[0] * 60) + $times[1];
    }

    public static function integerToTime($value)
    {
        $HH = (int)$value / 60;
        $mm = $value % 60;
        $HH = (int)$HH < 10 ? "0".$HH : (int)$HH;
        $mm = (int)$mm < 10 ? "0".$mm : (int)$mm;
        return $HH . ":" . $mm;
    }

    public static function newAvailableTime($time, $search, $duration = null)
    {
        $temp = self::getTimeToInteger($time);
        if ($duration !== null) {
            $temp = $temp - $duration;
        }
        if ($temp > 0) {
            if($search > 0){
                $temp = intval($temp / $search);
                if ($temp > 0) {
                    $temp = $temp * $search;
                }
            }
        }
        else{
            return false;
        }
        return $temp;
    }
    public static function removeOffHours($salonScheduleSeq, $employeeSchedulePeriods, $duration)
    {
        $result = [];
        foreach ($employeeSchedulePeriods as $employeePeriod) {
            $start = self::getTimeToInteger($employeePeriod['start']);
            $end = self::getTimeToInteger($employeePeriod['end']);
            foreach ($salonScheduleSeq as $salonSchedule) {
                if($salonSchedule >= $start && ($salonSchedule + $duration) <=  $end) {
                    array_push($result,$salonSchedule);
                }
            }
        }
        return $result;
    }

    public static function removeBusyTime($salonScheduleSeq, $appointmentPeriods, $duration)
    {
        foreach ($appointmentPeriods as $appointmentPeriod) {
            $start = self::getTimeToInteger($appointmentPeriod['from_time']);
            $end = self::getTimeToInteger($appointmentPeriod['to_time']);
            foreach ($salonScheduleSeq as $key=>$salonSchedule) {
                if($salonSchedule < $end && ($salonSchedule + $duration) > $start) {
                    unset($salonScheduleSeq[$key]);
                }
            }
        }
        return $salonScheduleSeq;
    }

    public static function parseSequenceOfIntToTime($sequence)
    {
        $sequenceOfTimes = [];
        foreach ($sequence as $item) {
            $sequenceOfTimes[] = self::integerToTime($item);
        }
        return $sequenceOfTimes;
    }
}