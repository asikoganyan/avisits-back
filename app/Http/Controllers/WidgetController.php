<?php

namespace App\Http\Controllers;

use App\Http\Requests\WidgetSettings\WidgetSettingsRequest;
use App\Models\WidgetSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Chain;

Class WidgetController extends Controller
{
    public function update(WidgetSettingsRequest $request) {
        $chain = $request->route("chain");
        $data = $request->post();
        $model = WidgetSettings::find($chain);
        $model->fill($data);
        if($model->save()){
            return response(["data"=>["settings"=>$model],"status"=>"OK"],200);
        }
        return response(["data"=>[], "status"=>"ERROR"],400);
    }

    public function show(){
    }
}