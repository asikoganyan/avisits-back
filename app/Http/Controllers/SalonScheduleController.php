<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalonSchedule\SalonScheduleStoreRequest;
use App\Http\Requests\SalonSchedule\SalonScheduleUpdateRequest;
use App\Http\Services\SalonScheduleService;
use App\Http\Services\SalonService;
use App\Models\SalonSchedule;
use Illuminate\Http\Request;

class SalonScheduleController extends Controller
{
    public function index(Request $request)
    {
        $salonSchedules = SalonSchedule::getScheduleList($request);
        return response()->json(["data"=>$salonSchedules], 200);
    }

    public function create()
    {

    }

    public function store(Request $baseRequest, SalonScheduleStoreRequest $request)
    {
        $data = $request->all();
        if (!SalonService::ownSalon($baseRequest, $data['salon_id'])) {
            return SalonService::ownErrorResponse();
        }
        $salonSchedule = new SalonSchedule($data);
        $salonSchedule->salon_id = $data['salon_id'];
        $salonSchedule->save();
        return response()->json(["data"=>$salonSchedule], 200);
    }

    public function edit()
    {

    }

    public function update(Request $baseRequest,SalonScheduleUpdateRequest $request)
    {
        $params = $request->route()->parameters();
        $data = $request->all();
        if(!empty($params['salon_schedule'])){
            if(!SalonScheduleService::ownSalonSchedule($params['salon_schedule'])){
                return SalonScheduleService::ownErrorResponse();
            }
        }
        if(isset($data['salon_id']) && !empty($data['salon_id'])){
            if (!SalonService::ownSalon($baseRequest, $data['salon_id'])) {
                return SalonService::ownErrorResponse();
            }
        }
        $salon_schedule = SalonSchedule::find((integer)$params['salon_schedule']);
        $salon_schedule->fill($data);
        if($salon_schedule->save()){
            return response()->json(["data"=>$salon_schedule],200);
        }else{
            return response()->json(["error"=>"UPDATE Error"],400);
        }
    }

    public function destroy(Request $request)
    {
        $params = $request->route()->parameters();
        if(!empty($params['salon_schedule'])){
            if(!SalonScheduleService::ownSalonSchedule($params['salon_schedule'])){
                return SalonScheduleService::ownErrorResponse();
            }
        }
        $salon_schedule = SalonSchedule::find((integer)$params['salon_schedule']);
        $salon_schedule->delete();
        return response()->json(["success"=>"1"],200);
    }

    public function salon_schedule(Request $request,$salonId)
    {
        if (!SalonService::ownSalon($request, $salonId)) {
            return SalonService::ownErrorResponse();
        }
        $salonSchedule = SalonSchedule::where(['salon_id'=>$salonId])->get();
        return response()->json($salonSchedule,200);
    }
}
