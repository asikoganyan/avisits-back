<?php
namespace App\Http\Services;

use App\Models\PriceLevel;
use App\Models\Service as ServiceModel;
use Illuminate\Http\Request;

class CheckOwnService
{
    public static function ownService(Request $request , $service_id){
        $chainId = $request->route('chain') || null;
        $service = ServiceModel::where(['chain_id'=>$chainId,'id'=>$service_id])->count();
        if($service !== 0){
            return true;
        }
        return false;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public static function serviceErrorResponse(){
        return response()->json(["error" => "permission error", "message" => "incorrect service_id"], 400);
    }

    public static function ownPriceLevel(Request $request , $price_level_id){
        $chainId = $request->route('chain') || null;
        $price_level = PriceLevel::where(['chain_id'=>$chainId,'id'=>$price_level_id])->count();
        if($price_level !== 0){
            return true;
        }
        return false;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public static function priceLevelErrorResponse(){
        return response()->json(["error" => "permission error", "message" => "incorrect price_level_id"], 400);
    }
}