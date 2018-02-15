<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\EmployeeTimesFilterRequest;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WidgetEmployeeController extends Controller
{
    private $chain;

    public function __construct(Request $request)
    {
        $this->chain = $request->route('chain');
    }

    public function employees(Request $request)
    {
        $filter = $request->post();
        $employees = Employee::employees($this->chain, $filter);
        return response()->json(['data' => ['employees' => $employees]], 200);
    }

    public function employee_times(Request $request)
    {
        $response = [];
        foreach ($request->post() as $filters){
            if (Carbon::today()->gt(Carbon::parse($filters['date']))) {
                return response()->json(["message" => "Invalid recording date", "status" => "ERROR"], 400);
            }
            $employees = Employee::employees($this->chain, $filters);
            $filterForTimes = [];
            $filterForTimes['salon_id'] = $filters['salon_id'];
            $filterForTimes["employees"] = [];
            $filterForTimes["date"] = $filters['date'];
            $employeesToArray = $employees->toArray();
            foreach ($employeesToArray as $employee) {
                array_push($filterForTimes["employees"], ["employee_id" => $employee["id"], "services" => $filters["services"]]);
            }
            $schedules = app('App\Http\Controllers\Widget\WidgetSchedulesController')->calculateAvailableTimes($filterForTimes);
            foreach ($employeesToArray as &$value) {
                foreach ($schedules["schedule"] as $schedule) {
                    if ($value['id'] == $schedule["employee_id"]) {
                        $value["periods"] = $schedule["periods"];
                        continue;
                    }
                }
            }
            array_push($response,['employees' => $employeesToArray, "date" => $filters['date'], "working_status" => 1, "services"=>$filters["services"]]);
        }
        return response()->json(['data' => $response], 200);
    }
}