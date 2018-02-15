<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chain\ChainStoreRequest;
use App\Http\Requests\Chain\ChainUpdateRequest;
use App\Models\ChainPriceLevel;
use App\Models\WidgetSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Chain;
use File;

Class ChainController extends Controller
{
    public function index()
    {
        $chains = Chain::where(["user_id" => Auth::id()])->with(['levels'])->orderBy('id', 'desc')->get();
        return response()->json(['data' => $chains], 200);
    }

    public function store(ChainStoreRequest $request)
    {
        $chain = new Chain($request->all());
        $chain->user_id = Auth::id();
        /*if ($request->hasFile('img')) {
            $file = $this->upload($request);
            if ($file) {
                $chain->img = $file['fileName'];
            }
        }*/
        $this->setDefaultSettingsForWidget($chain);
        if ($chain->save()) {
            if($request->input('levels')){
                foreach ($request->input('levels') as $key => $value) {
                    $level = ChainPriceLevel::add($value['level'], $chain->id);
                }
            }
            else{
                ChainPriceLevel::add("Уровен 1", $chain->id);
            }
            $chain = Chain::getById($chain->id);
            $data = [];
            $data['chain'] = $chain;
            $data['status'] = 'OK';
            return response()->json(["data" => $data], 200);
        }
        return response()->json(['error' => 'The chain saving failed!'], 400);
    }

    public function update(ChainUpdateRequest $request)
    {
        $params = $request->route()->parameters();
        $chain = Chain::where(["id" => $params['chain'], "user_id" => Auth::id()])->first();
        if ($chain) {
            $chain->fill($request->all());
            if ($chain->save()) {
                $levelIds = [];
                foreach ($request->input('levels') as $key => $value) {
                    if (isset($value['id']) && ChainPriceLevel::getById($value['id'])) {
                        $level = ChainPriceLevel::edit($value['id'], $value['level'], $chain->id);
                    } else {
                        $level = ChainPriceLevel::add($value['level'], $chain->id);
                    }
                    $levelIds[$level->id] = $level->id;
                }
                ChainPriceLevel::deleteExceptIds($levelIds, $chain->id);
                $chain=Chain::getById($chain->id);
                $data = [];
                $data['chain'] = $chain;
                $data['status'] = 'OK';
                return response()->json(["data" => $data], 200);
            } else {
                return response()->json(['error' => 'The process of saving data failed!'], 400);
            }
        } else {
            return response()->json(['error' => 'The chain not found or permission failed!'], 400);
        }

    }

    public function show(Request $request)
    {
        $params = $request->route()->parameters();
        $chain = Chain::where(["id" => $params['chain'], "user_id" => Auth::id()])->with(['levels'])->first();
        if ($chain) {
            return response()->json(['data' => ["chain" => $chain]], 200);
        } else {
            return response()->json(['error' => "The chain not found or permission failed!"], 400);
        }

    }

    public function destroy(Request $request)
    {
        $params = $request->route()->parameters();
        $chain = Chain::where(["id" => $params['chain'], "user_id" => Auth::id()])->first();
        if ($chain) {
            if ($chain->delete()) {
                return response()->json(['success' => 1], 200);
            } else {
                return response()->json(['error' => "Failed to delete!"], 400);
            }
        } else {
            return response()->json(['error' => "The chain not found or permission failed!"], 400);
        }
    }

    public function firstChain()
    {
        $chain = new Chain();
        $chain->fill(["title" => "Сеть 1", "user_id" => Auth::id()]);
        $chain->user_id = Auth::id();
        $this->setDefaultSettingsForWidget($chain);
        if ($chain->save()) {
            return $chain;
        }
        return [];
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('img')) {
            return response()->json(["data"=>[
                "img" => null
            ]],200);
        }
        $ds = DIRECTORY_SEPARATOR;
        $file = $request->file('img');
        $path = public_path("files" . $ds . "chains" . $ds . "images" . $ds . "main");
        $fileName = time() . "_" . md5($file->getClientOriginalName()) . "." . $file->getClientOriginalExtension();
        if (!File::exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        if ($file->move($path, $fileName)) {
            return response()->json(["data"=>[
                "img" => 'files'.$ds.'chains'.$ds.'images'.$ds.'main'.$ds.$fileName
            ],
                "status"=>"OK"],200);
        } else {
            return response()->json(["data"=>"","status"=>"ERROR","message"=>"File upload failed!"],400);
        }

    }

    private function setDefaultSettingsForWidget($model){
        $defSettings = WidgetSettings::getDefaultSettings();
        foreach ($defSettings as $key=>$item) {
            $model->setAttribute($key,$item);
        }
    }
}