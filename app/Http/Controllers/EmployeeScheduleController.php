<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SchedulePeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeScheduleController extends Controller
{
    /**
     * Create employee schedule
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = [];
        $rules = [];
        if ($request->input('type') == 1 || !$request->input('type')) {
            $rules = [
                "salon_id" => "required|integer|exists:salons,id",
                "employee_id" => "required|integer",
                "type" => "required|integer",
                "date" => "required|date_format:Y-m-d",
                "working_days" => "required|integer|max:7|min:0",
                "weekends" => "required",
                "periods" => "required"
            ];
        }
        if($request->input('type')==2) {
            $rules = [
                "salon_id" => "required|integer|exists:salons,id",
                "employee_id" => "required|integer",
                "type" => "required|integer",
                "date" => "required|date_format:Y-m-d",
                "days" => "required"
            ];
        }
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $data['ExceptionHandler'] = 'invalid_request';
        } else {
            if ($request->input('type') == 1) {
                $schedule = Schedule::create($request->input('salon_id'), $request->input('employee_id'), $request->input('type'), 1, $request->input('working_days'), $request->input('weekends'), 0, Carbon::parse($request->input('date'))->format('Y-m-d'));
                foreach ($request->input('periods') as $key => $value) {
                    $period = SchedulePeriod::add($schedule->id, Carbon::parse($value['start'])->format('H:i'), Carbon::parse($value['end'])->format('H:i'));
                }
            } else {
                foreach ($request->input('days') as $dayKey => $dayValue) {
                    $workingStatus = 0;
                    foreach ($dayValue as $i => $b) {
                        $numOfDay = $i;
                        $workingStatus = $b[0]['working_status'];
                        break;
                    }
                    $schedule = Schedule::create($request->input('salon_id'), $request->input('employee_id'), $request->input('type'), $workingStatus, 0, 0, $numOfDay, Carbon::parse($request->input('date'))->format('Y-m-d'));
                    foreach ($dayValue as $i => $b) {
                        foreach ($b as $r => $s) {
                            $period = SchedulePeriod::add($schedule->id, Carbon::parse($s['start'])->format('H:i'), Carbon::parse($s['end'])->format('H:i'));
                        }
                    }
                }
                if (isset($schedule)) {
                    $schedule = Schedule::getById($schedule->id);
                    $data['data'] = $schedule;
                    $data['status'] = 'OK';
                }
            }
            return response()->json($data, 200);
        }
    }

    /**
     * Edit employee schedule
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $data = [];
        $rules = [];
        if ($request->input('type') == 1 || !$request->input('type')) {
            $rules = [
                "salon_id" => "required|integer|exists:salons,id",
                "employee_id" => "required|integer",
                "type" => "required|integer",
                "date" => "required|date_format:Y-m-d",
                "working_days" => "required|integer|max:7|min:0",
                "weekends" => "required",
                "periods" => "required"
            ];
        }
        if($request->input('type')==2) {
            $rules = [
                "salon_id" => "required|integer|exists:salons,id",
                "employee_id" => "required|integer",
                "type" => "required|integer",
                "date" => "required|date_format:Y-m-d",
                "days" => "required"
            ];
        }
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $data['ExceptionHandler'] = 'invalid_request';
        } else {
            if ($request->input('type') == 1) {
                $schedule = Schedule::edit($request->input('id'), $request->input('salon_id'), $request->input('employee_id'), $request->input('type'), 1,$request->input('working_days'), $request->input('weekends'),0, Carbon::parse($request->input('date'))->format('Y-m-d'));
                $periodIds = [];
                foreach ($request->input('periods') as $key => $value) {
                    if (isset($value['id'])) {
                        $period = SchedulePeriod::edit($value['id'], $schedule->id, Carbon::parse($value['start'])->format('H:i'), Carbon::parse($value['end'])->format('H:i'));
                    } else {
                        $period = SchedulePeriod::add($schedule->id, Carbon::parse($value['start'])->format('H:i'), Carbon::parse($value['end'])->format('H:i'));
                    }
                    $periodIds[$period->id] = $period->id;
                }
                if (count($periodIds) > 0) {
                    SchedulePeriod::deleteExceptIds($schedule->id, $periodIds);
                }
            } else {
                foreach ($request->input('days') as $dayKey => $dayValue) {
                    $workingStatus = 0;
                    foreach ($dayValue as $i => $b) {
                        $numOfDay = $i;
                        $workingStatus = $b[0]['working_status'];
                        break;
                    }
                    $schedule = Schedule::edit($request->input('id'),$request->input('salon_id'), $request->input('employee_id'), $request->input('type'), $workingStatus, 0, 0, $numOfDay, Carbon::parse($request->input('date'))->format('Y-m-d'));
                    $periodIds = [];
                    foreach ($dayValue as $i => $b) {
                        foreach ($b as $r => $s) {
                            if (isset($s['id'])) {
                                $period = SchedulePeriod::edit($s['id'], $schedule->id, Carbon::parse($s['start'])->format('H:i'), Carbon::parse($s['end'])->format('H:i'));
                            } else {
                                $period = SchedulePeriod::add($schedule->id, Carbon::parse($s['start'])->format('H:i'), Carbon::parse($s['end'])->format('H:i'));
                            }
                            $periodIds[$period->id] = $period->id;
                        }
                    }
                    if (count($periodIds) > 0) {
                        SchedulePeriod::deleteExceptIds($schedule->id, $periodIds);
                    }
                }
                if (isset($schedule)) {
                    $schedule = Schedule::getById($schedule->id);
                    $data['data'] = $schedule;
                }
            }
            if (isset($schedule)) {
                $schedule = Schedule::getById($schedule->id);
                $data['data'] = $schedule;
                $data['status'] = 'OK';
            }
        }
        return response()->json($data, 200);
    }
}