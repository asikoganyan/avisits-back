<?php
namespace App\Http\Services;


use App\Http\Services\Widget\WidgetEmployeeSchedule;

class AppointmentService
{
    public static function calculateEndOfAppointment($start,$duration){
        $startToInteger = WidgetEmployeeSchedule::getTimeToInteger($start);
        $endInteger =(int)($startToInteger + (int)$duration);
        return WidgetEmployeeSchedule::integerToTime($endInteger);
    }
}