<?php

namespace App\Http\Controllers;


use App\Http\Requests\ServicePrice\ServicePriceStoreRequest;
use App\Http\Requests\ServicePrice\ServicePriceUpdateRequest;
use App\Http\Services\CheckOwnService;
use App\Models\Service;
use App\Models\ServicePrice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServicePriceController extends Controller
{
    public function index()
    {
        $servicePrice = ServicePrice::getAll();
        return response()->json($servicePrice, 200);
    }

    public function show(Request $request)
    {
        $params = $request->route()->parameters();
        $servicePrice = ServicePrice::getOne($params);
        return response()->json(["data" => ["ServicePrice" => $servicePrice], "status" => "OK"], 200);
    }

    /**
     * Update service price
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $serviceId = $request->input('service_id');
        $date=Carbon::parse($request->input('date'))->format('Y-m-d');
        foreach ($request->input('prices') as $key => $value) {
            $servicePrice = ServicePrice::add($value['price_id'], $serviceId, $value['price_from'], $value['price_to'], $date);
        }
        $data = [];
        $data['data'] = Service::getById($serviceId);
        $data['status'] = 'OK';
        return response()->json($data, 200);
    }

    /**
     * Update service price
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $serviceId = $request->input('service_id');
        $date=Carbon::parse($request->input('date'))->format('Y-m-d');
        $servicePriceIds = [];
        foreach ($request->input('prices') as $key => $value) {
            if (isset($value['id']) && ServicePrice::getById($value['id'])) {
                $servicePrice = ServicePrice::edit($value['id'], $value['price_id'], $serviceId, $value['price_from'], $value['price_to'], $date);
            } else {
                $servicePrice = ServicePrice::add($value['price_id'], $serviceId, $value['price_from'], $value['price_to'], $value['date']);
            }
            $servicePriceIds[$servicePrice->id] = $servicePrice->id;
        }
        ServicePrice::deleteExceptIds($serviceId, $servicePriceIds);
        $data = [];
        $data['data'] = Service::getById($serviceId);
        $data['status'] = 'OK';
        return response()->json($data, 200);
    }

    public function destroy(Request $request)
    {
        $params = $request->route()->parameters();
        $servicePrice = ServicePrice::getOne($params);
        if ($servicePrice->delete()) {
            return response()->json(["success" => 1, "status" => "OK"], 200);
        }
    }
}