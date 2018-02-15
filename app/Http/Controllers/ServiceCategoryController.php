<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $chain = $request->route('chain');
        $serviceCategory = ServiceCategory::where(["chain_id" => $chain])->orderBy('id', 'desc')->get();
        return response()->json(["data" => $serviceCategory], 200);
    }

    public function categoryGroups(Request $request)
    {
        $chain = $request->route('chain');
        $categories = ServiceCategory::where(["chain_id" => $chain])
            ->whereNull('parent_id')
            ->with(['groups'=>function($query){
                $query->with('services');
            }])
            ->get();
        return response()->json(["data" => ["categories" => $categories]], 200);
    }

    public function categories(Request $request)
    {
        $chain = $request->route('chain');
        $categories = ServiceCategory::where(["chain_id" => $chain])
            ->with(['groups'=>function($query){
                $query->with('services');
            }])
            ->whereNull('parent_id')
            ->get();
        return response()->json(["data" => ["categories" => $categories]], 200);
    }

    /**
     * Get groups by category id
     *
     * @param Request $request
     * @param $chainId
     * @param $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupsByCategory(Request $request, $chainId, $categoryId)
    {
        $groups = ServiceCategory::getByParentId($categoryId);
        return response()->json(["data" => ["groups" => $groups]], 200);
    }

    public function groups(Request $request)
    {
        $chain = $request->route('chain');
        $groups = ServiceCategory::where(["chain_id" => $chain])->whereNotNull('parent_id')->get();
        return response()->json(["data" => ["groups" => $groups]], 200);
    }

    public function create()
    {
        return response()->json(["success" => "coming soon"], 200);
    }

    public function store(ServiceCategoryRequest $request)
    {
        $data = $request->only('title');
        $chain_id = (integer)$request->route('chain');
        $serviceCategory = new ServiceCategory($data);
        if ($request->input('parent_id')) {
            $serviceCategory->parent_id = $request->input('parent_id');
        }
        $serviceCategory->chain_id = $chain_id;
        if ($serviceCategory->save()) {
            $serviceCategory = ServiceCategory::getById($serviceCategory->id);
            return response()->json(["data" => $serviceCategory], 200);
        }
        return response()->json(["error" => "save error"], 400);
    }

    public function edit()
    {
        return response()->json(["success" => "coming soon"], 200);
    }

    public function update(ServiceCategoryRequest $request)
    {
        $params = $request->route()->parameters();
        $model = ServiceCategory::where(["id" => $params['service_category'], 'chain_id' => $params['chain']])->first();
        if (!$model) {
            return response()->json(["error" => "incorrect service category"], 400);
        }
        $model->fill($request->all());
        if ($request->input('parent_id')) {
            $model->parent_id = $request->input('parent_id');
        }
        $model->chain_id = $params['chain'];
        if ($model->save()) {
            $model = ServiceCategory::getById($model->id);
            return response()->json(["data" => $model], 200);
        }
        return response()->json(["error" => "update error"], 200);
    }

    public function destroy(Request $request)
    {
        $params = $request->route()->parameters();
        $serviceCategory = ServiceCategory::getById($params['service_category']);
        if ($serviceCategory) {
            $serviceCategory->delete();
        }
        return response()->json(["success" => "Successfully deleted"], 200);
    }
}
