<?php
namespace App\Http\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Salon;

class SalonService
{
    public static function ownSalon(Request $request , $salon_id) {
        $chainId = $request->route('chain') || null;
        $salon = Salon::where(['user_id'=>Auth::id(),'id'=>$salon_id])->count();
        if($salon !== 0){
            return true;
        }
        return false;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ownErrorResponse(){
        return response()->json(["error" => "permission error", "message" => "incorrect salon_id"], 400);
    }
}