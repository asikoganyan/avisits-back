<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\CalendarFilterRequest;
use App\Http\Services\SalonScheduleService;
use App\Http\Services\Widget\WidgetEmployeeSchedule as EmployeeScheduleService;
use App\Models\Appointment;
use App\Models\SalonEmployeeHasServices;
use App\Models\SalonSchedule;
use App\Models\Schedule;
use App\Models\WidgetSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use function PHPSTORM_META\type;

class WidgetSchedulesController extends Controller
{
    public function __construct(Request $request)
    {
        $this->chain = $request->route('chain');
    }

    public function employeeCalendar(CalendarFilterRequest $request)
    {
        $filters = $request->all();
        $filter['salon_id'] = $filters['salon_id'];
        $from = Carbon::parse($filters['from']);
        $to = Carbon::parse($filters['to']);
        $dates = [];
        while ($from->lessThanOrEqualTo($to)) {
            array_push($dates, $from->format("Y-m-d"));
            $from = $from->addDay(1);
        }
        $response = [];
        foreach ($dates as $date) {
            $item = ["date" => $date, "working_status" => 0];
            $filter['date'] = $date;
            $salon_schedule_status = SalonSchedule::getStatusByDate($filter['salon_id'], $date);
            if ($salon_schedule_status === 1 && (isset($filters['employees']) && count($filters['employees']) >= 1)) {
                foreach ($filters['employees'] as $employee) {
                    $filter['employee_id'] = $employee;
                    $employee_schedule = $this->status($filter);
                    if (count($employee_schedule) <= 0) {
                        continue;
                    }
                    $employee_schedule['working_status'] = $this->getWorkingStatus($employee_schedule, $date);
                    if ($employee_schedule['working_status'] === 1) {
                        $item['working_status'] = 1;
                        break;
                    }
                }
            }else{
                $item['working_status'] = $salon_schedule_status;
            }
            array_push($response, $item);
        }
        return response()->json(["data" => ["calendar" => $response]], 200);
    }

    public function freeTimes(Request $request)
    {
        $filters = $request->post();
        if (Carbon::today()->gt(Carbon::parse($filters['date']))) {
            return response()->json(["message" => "Invalid recording date", "status" => "ERROR"], 400);
        }
        $data = $this->calculateAvailableTimes($filters);
        return response()->json(["data" => $data], 200);
    }

    public function calculateAvailableTimes($filters)
    {
        $filter = [];
        $filter['salon_id'] = $filters['salon_id'];
        $filter['date'] = $filters['date'];
        $response = [];
        $salonSchedule = SalonSchedule::getScheduleByDate($filter['salon_id'], $filter['date']);
        if (!$salonSchedule || $salonSchedule->working_status != 1) {
            return ["schedule" => [], "salon_id" => $filter['salon_id'], "date" => $filter['date'], "working_status" => 0];
        }
        $settings = WidgetSettings::select(["w_step_display", "w_step_search"])->find($this->chain);
        $salonScheduleSequenceDef = $this->dropPeriod($salonSchedule->start, $salonSchedule->end, $settings->w_step_display);
        foreach ($filters['employees'] as $employee) {
            $salonScheduleSequence = $salonScheduleSequenceDef;
            $durationsSum = SalonEmployeeHasServices::getSumOfDuration($filter['salon_id'], $employee["employee_id"], $employee["services"]);
            $newTime = EmployeeScheduleService::newAvailableTime($salonSchedule->start, $settings->w_step_search);
            if ($newTime) {
                $salonScheduleSequence[0] = $newTime;
                $newTime = null;
            }
            $newTime = EmployeeScheduleService::newAvailableTime($salonSchedule->end, $settings->w_step_search, $durationsSum);
            if ($newTime) {
                $salonScheduleSequence[count($salonScheduleSequence) - 1] = $newTime;
                $newTime = null;
            }
            $filter["employee_id"] = $employee["employee_id"];
            $appointments = Appointment::getAppointments($filter);
            if ($appointments) {
                foreach ($appointments->toArray() as $appointment) {
                    $newTime = EmployeeScheduleService::newAvailableTime($appointment['from_time'], $settings->w_step_search, $durationsSum);
                    if ($newTime) {
                        array_push($salonScheduleSequence, $newTime);
                        $newTime = null;
                    }
                    $newTime = EmployeeScheduleService::newAvailableTime($appointment['to_time'], $settings->w_step_search);
                    if ($newTime) {
                        array_push($salonScheduleSequence, $newTime);
                        $newTime = null;
                    }
                }
            }
            $employeeSchedules = Schedule::getWorkingHours($filter);
            if (!$employeeSchedules) {
                $data = [];
                $data["employee_id"] = $filter["employee_id"];
                $data["periods"] = [];
                $response["schedule"][] = $data;
                continue;
            }
            $employeeSchedulesArray = $employeeSchedules->toArray();
            $employeeSchedulesArray['working_status'] = $this->getWorkingStatus($employeeSchedulesArray, $filter['date']);
            if ($employeeSchedulesArray['working_status'] !== 1 || count($employeeSchedulesArray['periods']) < 1) {
                $data = [];
                $data["employee_id"] = $filter["employee_id"];
                $data["periods"] = [];
                $response["schedule"][] = $data;
                continue;
            }
            foreach ($employeeSchedulesArray['periods'] as $period) {
                $newTime = EmployeeScheduleService::newAvailableTime($period['start'], $settings->w_step_search);
                if ($newTime) {
                    array_push($salonScheduleSequence, $newTime);
                    $newTime = null;
                }
                $newTime = EmployeeScheduleService::newAvailableTime($period['end'], $settings->w_step_search, $settings->w_step_search);
                if ($newTime) {
                    array_push($salonScheduleSequence, $newTime);
                    $newTime = null;
                }
            }
            $salonScheduleSequence = array_unique($salonScheduleSequence);
            $salonScheduleSequence = EmployeeScheduleService::removeOffHours($salonScheduleSequence, $employeeSchedulesArray['periods'], $durationsSum);
            $salonScheduleSequence = EmployeeScheduleService::removeBusyTime($salonScheduleSequence, $appointments->toArray(), $durationsSum);
            sort($salonScheduleSequence);
            $salonScheduleSequence = EmployeeScheduleService::parseSequenceOfIntToTime($salonScheduleSequence);
            $data = [];
            $data["employee_id"] = $filter["employee_id"];
            $data["periods"] = $salonScheduleSequence;
            $response["working_status"] = 1;
            $response["date"] = $filters['date'];
            $response["schedule"][] = $data;
        }
        return $response;
    }

    public function freeTimesOld(Request $request)
    {
        $filters = $request->post();
        if (Carbon::today()->gt(Carbon::parse($filters['date']))) {
            return response()->json(["message" => "Invalid recording date", "status" => "ERROR"], 400);
        }
        $filter = [];
        $filter['salon_id'] = $filters['salon_id'];
        $filter['date'] = $filters['date'];
        $response = [];
        foreach ($filters['employees'] as $employee) {
            $filter['employee_id'] = $employee;
            array_push($response, [
                "employee_id" => $employee,
                "schedule" => $this->freeTimeOfEmployee($filter)
            ]);
        }
        return response()->json(["data" => ["employees" => $response]], 200);
    }

    private function dropPeriod($start, $end, $divider = 15)
    {
        $startInteger = $this->getTimeToInteger($start);
        $end = $this->getTimeToInteger($end);
        $sequence = [];
        while ($startInteger <= $end) {
            array_push($sequence, $startInteger);
            $startInteger += $divider;
        }
        return $sequence;
    }

    private function status($filter)
    {
        $dayOfWeek = null;
        $dayOfWeek = Carbon::parse($filter['date'])->dayOfWeek;
        if ($dayOfWeek === 0) {
            $dayOfWeek = 7;
        }
        $query = Schedule::query();
        $query->select(["working_status", "date", "type", "working_days", "weekend"])
            ->where(["salon_id" => $filter['salon_id'], "employee_id" => $filter['employee_id']])
            ->where("date", "<=", $filter["date"])
            ->where(function ($where) use ($dayOfWeek) {
                $where->where("num_of_day", "=", $dayOfWeek)->orWhereNull("num_of_day")->orWhere("num_of_day", "=", 0);
            })->orderBy('date', 'desc');
        $schedule = $query->first();
        if ($schedule)
            return $schedule->toArray();
        return [];
    }

    private function getTimeToInteger($value)
    {
        $times = explode(':', $value);
        $times = collect($times)->map(function ($item) {
            return (integer)$item;
        });
        return ($times[0] * 60) + $times[1];
    }

    private function getWorkingStatus($employeeSchedule, $date)
    {
        if ($employeeSchedule['type'] == 1) {
            return $employeeSchedule['working_status'];
        }
        if ($employeeSchedule['type'] == 2) {
            $days = Carbon::parse($date)->diffInDays(Carbon::parse($employeeSchedule['date'])) + 1;
            $sumOfDays = (integer)$employeeSchedule['working_days'] + (integer)$employeeSchedule['weekend'];
            $nowIs = $days % $sumOfDays;
            if ($nowIs > $employeeSchedule['working_days'] || $nowIs == 0) {
                return 0;
            } else {
                return 1;
            }
        }
    }

    private function freeTimeOfEmployee($filter)
    {
        $appointments = Appointment::getAppointments($filter);
        $schedules = Schedule::getWorkingHours($filter);
        if (!$schedules) {
            return null;
        }
        $schedulesArray = $schedules->toArray();
        $schedulesArray['free_periods'] = $schedulesArray['periods'];
        $workingStatus = $this->getWorkingStatus($schedulesArray, $filter['date']);
        $schedulesArray['working_status'] = $workingStatus;
        if (count($appointments) > 0 && $workingStatus == 1) {
            foreach ($appointments as $appointment) {
                $from_time = $this->getTimeToInteger($appointment['from_time']);
                $to_time = $this->getTimeToInteger($appointment['to_time']);
                foreach ($schedulesArray['free_periods'] as &$sh) {
                    $start = $this->getTimeToInteger($sh['start']);
                    $end = $this->getTimeToInteger($sh['end']);
                    /*если начало записи внутри периода*/
                    if ($start <= $from_time && $from_time <= $end) {
                        /*если конец записи тоже внутри периода*/
                        if ($start <= $to_time && $to_time <= $end) {
                            /*если начало совпадает с началом периода*/
                            if ($from_time == $start) {
                                /*Если конец тоже совпадает с концом периода*/
                                if ($to_time == $end) {
                                    $sh['removed'] = 1;
                                    continue;
                                } else {
                                    $sh['start'] = $appointment['to_time'];
                                }
                            } else {
                                /*Если начало записи не совпадает с началом периода, но конец совпадает с концом периода */
                                if ($to_time == $end) {
                                    $sh['end'] = $appointment['from_time'];
                                } /*from_time i end time  внутри периода и не совпадают с началом и концом периода*/
                                else {
                                    $tEnd = $sh['end'];
                                    $sh['end'] = $appointment['from_time'];

                                    $schedulesArray['free_periods'][] = [
                                        "schedule_id" => $sh['schedule_id'],
                                        "start" => $appointment['to_time'],
                                        "end" => $tEnd];
                                }
                            }
                        }
                    }
                }
            }
            foreach ($schedulesArray['free_periods'] as $key => $period) {
                if (isset($period['removed']) && $period['removed'] == 1) {
                    array_splice($schedulesArray['free_periods'], $key, 1);
                }
            }
        }
        return $schedulesArray;
    }

}