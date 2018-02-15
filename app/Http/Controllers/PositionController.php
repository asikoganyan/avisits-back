<?php

namespace App\Http\Controllers;

use App\Http\Requests\Position\PositionStoreRequest;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request) {
        $chainId = $request->route('chain');
        $positions = Position::where(['chain_id'=>$chainId])->get();
        return response()->json(["data"=>["positions"=>$positions]],200);
    }
    public function index_grid(Request $request) {
        $data = $request->all();
        $datatable = isset($data['datatable']) ? $data['datatable'] : null;
        $positions = Position::getAll($request->route('chain') , $datatable);
        $meta = [];
        if($datatable){
            $datatable['pagination']['total'] = $positions['total'];
            $meta = $datatable['pagination'];
        }
        return response()->json(["data"=>$positions['position'],
            "meta"=>$meta],200);
    }
    public function store(Request $baseRequest,PositionStoreRequest $request){
        $params  = $baseRequest->route()->parameters();
        $data = $request->all();
        $position = new Position($data);
        $position->chain_id = $params['chain'];
        if($position->save()){
            return response()->json(["data"=>["position"=>$position],"status"=>"OK"],200);
        }
        return response()->json(["error"=>"saving error"],400);

    }
    public function show(Request $request){
        $params  = $request->route()->parameters();
        $position = Position::where(["chain_id"=>$params['chain'],"id"=>$params["position"]])->first();
        return response()->json(["data"=>["position"=>$position],"status"=>"OK"],200);
    }
    public function update(Request $baseRequest, PositionStoreRequest $request){
        $params  = $request->route()->parameters();
        $position = Position::where(["chain_id"=>$params['chain'],"id"=>$params["position"]])->first();
        $position->fill($request->all());
        if($position->save()){
            return response()->json(["data"=>["position"=>$position],"status"=>"OK"],200);
        }
        return response()->json(["error"=>"saving error"],400);
    }
    public function destroy(Request $request){
        $params  = $request->route()->parameters();
        $position = Position::where(["chain_id"=>$params['chain'],"id"=>$params["position"]])->first();
        if($position->delete()){
            return response()->json(["success"=>1],200);
        }
    }
}
