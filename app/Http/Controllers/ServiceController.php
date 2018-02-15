<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceStoreRequest;
use App\Http\Requests\ServiceUpdateRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->route()->parameters();
        $services = Service::where(["chain_id" => $params['chain']])
            ->with(['servicePrice'=>function($with){
                $with->with('level');
            }])
            ->orderBy('order', 'desc')
            ->get();
        return response()->json(["data" =>["services"=>$services] ], 200);
    }

    public function create()
    {
        return response()->json(["success" => "coming soon"], 200);
    }

    public function store(Request $baseRequest, ServiceStoreRequest $request)
    {
        $data = $request->all();
        if (!$this->ownServiceCategory($baseRequest, $data['service_category_id'])) {
            return response()->json(["error" => "permission error", "message" => "incorrect service_category_id"], 400);
        }
        $chain_id = (integer)$request->route('chain');
        $service = new Service();
        $service->fill($data);
        $service->service_category_id = $data['service_category_id'];
        $service->chain_id = $chain_id;
        if ($service->save()) {
            return response()->json(["data" =>["service"=>$service] ], 200);
        }
        return response()->json(["error" => "save error"], 400);
    }

    public function edit()
    {
        return response()->json(["success" => "coming soon"], 200);
    }

    public function update(Request $baseRequest, ServiceUpdateRequest $request)
    {
        $params = $request->route()->parameters();
        $model = Service::where(["id" => $params['service'], 'chain_id' => $params['chain']])->first();
        if (!$model) {
            return response()->json(["error" => "incorrect service"], 400);
        }
        if ($request->input('service_category_id')) {
            if (!$this->ownServiceCategory($baseRequest, $request->input('service_category_id'))) {
                return response()->json(["error" => "permission error", "message" => "incorrect service_category_id"], 400);
            }
        }
        $model->fill($request->all());
        $model->chain_id = $params['chain'];
        if ($model->save()) {
            return response()->json(["data" =>["service"=>$model] ], 200);
        }
        return response()->json(["error" => "update error"], 400);
    }

    public function destroy(Request $request)
    {
        $params = $request->route()->parameters();
        $model = Service::where(["id" => $params['service'], 'chain_id' => $params['chain']])->first();
        if (!$model) {
            return response()->json(["error" => "incorrect service"], 400);
        }
        $model->delete();
        return response()->json(["success" => "1"], 200);
    }

    /**
     *
     * @param Request $request
     * @param $service_category_id
     * @return bool
     */
    public function ownServiceCategory(Request $request, $service_category_id)
    {
        $service_category = ServiceCategory::where([
            "chain_id" => (integer)$request->route('chain'),
            "id" => (integer)$service_category_id
        ])->count();
        if ($service_category === 0) {
            return false;
        }
        return true;
    }
}
