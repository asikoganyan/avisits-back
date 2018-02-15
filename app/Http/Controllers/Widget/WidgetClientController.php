<?php
namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Requests\Widget\ClientStoreRequest;
use App\Http\Requests\Widget\ClientUpdateRequest;

class WidgetClientController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        return  response()->json(['data'=>['client'=>'In Process']],200);
    }

    public function show($chain,$client)
    {
        $result = Client::getByEmailOrPhone($client);
        return response()->json(["data"=>["client"=>$result]],200);
    }
    public function store(ClientStoreRequest $request)
    {
        $data = $request->all();
        $client = new Client();
        $client->fill($data);
        if($client->save()){
            return response()->json(["data"=>["client"=>$client]],200);
        }
    }

    public function update($chain,$client,ClientUpdateRequest $request)
    {
        $data = $request->all();
        $client = Client::find($client);
        $client->fill($data);
        if($client->save()){
            return response()->json(["data"=>["client"=>$client]],200);
        }
    }
}