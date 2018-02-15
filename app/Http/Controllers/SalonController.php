<?php

namespace App\Http\Controllers;

use \App\Http\Requests\StoreSalonRequest;
use \App\Http\Requests\UpdateSalonRequest;
use App\Models\Salon;
use App\Models\SalonSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use JWTAuth;
use File;
use Exception;

class SalonController extends Controller
{

    public function index(Request $request, $chainId)
    {
        if ($chainId) {
            $salons = Salon::getByChainId($chainId);
        } else {
            $salons = Salon::getAll();
        }
        dd($salons);
        return response()->json(["data" => $salons], 200);
    }

    public function create(Request $request)
    {
        return "create";
    }

    /**
     * Create salon
     *
     * @param StoreSalonRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreSalonRequest $request)
    {
        $ds = DIRECTORY_SEPARATOR;
        $salon = new Salon();
        $salon->fill($request->all());
        $salon->user_id = Auth::id();
        $salon->chain_id = $request->route('chain');
        $salon->current_time = Carbon::parse($request->input('current_time'))->format('Y-m-d H:i:s');
        $salon->notify_about_appointments = implode(',',$request->input('notify_about_appointments'));
        if ($salon->save()) {
            if($request->input('schedule')) {
                foreach ($request->input('schedule') as $key => $value) {
                    SalonSchedule::add($salon->id, $value['num_of_day'], $value['working_status'], $value['start'], $value['end']);
                }
            }else {
                $default_schedules = SalonSchedule::default_schedules($salon->id);
                SalonSchedule::insert($default_schedules);
            }
            $salon->refresh();
            return response()->json(['success' => 'Created successfully', 'data' => Salon::find($salon->id), 'salon_schedule' => $salon->schedule], 200);
        }
        return response()->json(["error" => "any problem with storing data"], 400);
    }

    /**
     * Get salon
     *
     * @param $chainId
     * @param $salonId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($chainId, $salonId)
    {
        $salon = Salon::getById($salonId);
        return response()->json(["data" => $salon], 200);
    }

    public function edit(StoreSalonRequest $request, Salon $salon)
    {
        return "edit";
    }

    /**
     * Update salon
     *
     * @param UpdateSalonRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSalonRequest $request)
    {
        $ds = DIRECTORY_SEPARATOR;
        $salon = (integer)$request->route('salon');
        $model = Salon::find($salon);
        $model->fill($request->all());
        $model->user_id = Auth::id();
        $salon->notify_about_appointments = serialize($request->input('notify_about_appointments'));
        $model->current_time = Carbon::parse($request->input('current_time'))->format('Y-m-d H:i:s');
        if ($model->save()) {
            foreach ($request->input('schedule') as $key => $value) {
                if (isset($value['id'])) {
                    SalonSchedule::edit($value['id'], $model->id, $value['num_of_day'], $value['working_status'], $value['start'], $value['end']);
                }
            }
            $model->refresh();
            $salon = Salon::getById($model->id);
            return response()->json(["data" => $salon], 200);
        }
        return response()->json(["error" => "any problem with storing data!"], 400);
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('img')) {
            return response()->json(["data"=>[
                "img" => null
            ],"status"=>"OK"],200);
        }
        $ds = DIRECTORY_SEPARATOR;
        $file = $request->file('img');
        $path = public_path("files" . $ds . "salons" . $ds . "images" . $ds . "main");
        $fileName = time() . "_" . md5($file->getClientOriginalName()) . "." . $file->getClientOriginalExtension();
        if (!File::exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        if ($file->move($path, $fileName)) {
            return response()->json(["data"=>[
                "img" => "files" . $ds . "salons" . $ds . "images" . $ds . "main". $ds . $fileName
            ],"status"=>"OK"],200);
        } else {
            return response()->json(["data"=>"","status"=>"ERROR","message"=>"File upload failed!"],400);
        }

    }

    public function destroy($chain, $salon)
    {
        $model = Salon::find($salon);
        $model->delete();
        return response()->json(["success" => "1"], 200);
    }

    public function haveAnySalon($chainId = 0)
    {
        $salons = Salon::join('chains', 'salons.chain_id', '=', 'chains.id')
            ->where(['chains.user_id' => Auth::id(), 'salons.user_id' => Auth::id()]);
        if ($chainId) {
            $salons = $salons->where('salons.chain_id', $chainId);
        }
        return $salons->count();
    }
}
