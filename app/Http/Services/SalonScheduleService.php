<?php
namespace App\Http\Services;

use App\Models\Salon;
use App\Models\SalonSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SalonScheduleService
{
    public static function ownSalonSchedule($salon_schedule){
        $salon = SalonSchedule::join("salons","salons.id","=",'salon_schedules.salon_id')
            ->where(['salons.user_id'=>Auth::id(),'salon_schedules.id'=>$salon_schedule])
            ->count();
        if($salon !== 0){
            return true;
        }
        return false;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ownErrorResponse(){
        return response()->json(["error" => "permission error", "message" => "incorrect id of salon_schedule"], 400);
    }
}