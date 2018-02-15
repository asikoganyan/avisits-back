<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\ScheduleStoreRequest;
use App\Http\Requests\Schedule\ScheduleUpdateRequest;
use App\Http\Services\EmployeeService;
use App\Http\Services\SalonService;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request) {
        $schedules = Schedule::join("salons","salons.id","=","schedules.salon_id")
            ->where(["salons.chain_id"=>$request->route('chain')])
            ->get();
        return response()->json(["data"=>$schedules],200);
    }

    public function create(){

    }

    public function store(ScheduleStoreRequest $request){
        $data = $request->all();
        if(!SalonService::ownSalon($request,$data['salon_id'])){
            return SalonService::ownErrorResponse();
        }
        if(!EmployeeService::ownEmployee($request,$data['employee_id'])){
            return EmployeeService::ownErrorResponse();
        }
        $schedule = new Schedule($data);
        if($schedule->save()){
            return response()->json(["data"=>["schedule"=>$schedule]],200);
        }
        return response()->json(["error"=>"The schedule save failed!"],400);
    }

    public function edit(){

    }

    public function update(ScheduleUpdateRequest $request){

    }

    public function destroy(){

    }
}
