<?php
namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\Http\Request;

class WidgetSalonController extends Controller
{
    private $chain;

    public function __construct(Request $request)
    {
        $this->chain = $request->route('chain');
    }

    public function salonsCities(Request $request) {
        $cities = Salon::salonsCities($this->chain);
        $data = collect($cities)->map(function($item){
            return $item->city;
        });
        return response()->json(['data' => ['cities'=>$data]], 200);
    }

    public function salons(Request $request) {
        $filters = $request->post();
        $response = [];
        $filter["city"] = $filters["city"];
        if(isset($filters["employees"]) && !empty($filters["employees"])){
            foreach ($filters["employees"] as $employee){
                $filter["employee_id"] = $employee["employee_id"];
                if(isset($employee["services"]) && !empty($employee["services"])){
                    $filter["services"] = $employee["services"];
                }
                $salons = Salon::salons($this->chain,$filter);
                $res = $filter;
                $res["salons"] = $salons;
                $response[] = $res;
            }
        }else{
            $salons = Salon::salons($this->chain,$filters);
            $response["salons"] = $salons;
        }
        return response()->json(['data' => $response], 200);
    }
}