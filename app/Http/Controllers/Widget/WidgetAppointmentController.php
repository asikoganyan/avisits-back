<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\AppointmentStoreRequest;
use App\Models\Appointment;
use App\Models\AppointmentHasServices;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Requests\Widget\ClientStoreRequest;
use App\Http\Requests\Widget\ClientUpdateRequest;
use Illuminate\Validation\ValidationException;
use Validator;
use Illuminate\Support\Facades\DB;
use App;
use App\Http\Services\AppointmentService;
use App\Models\SalonEmployeeHasServices;

class WidgetAppointmentController extends Controller
{
    public function __construct()
    {
    }

    public function store(Request $request)
    {
        if(empty($request->all())){
            App::abort(400);
        }
        foreach ($request->all() as $data) {
            $this->validateStoreData($data);
        }
        $postData = $request->all();
        try {
            DB::transaction(function () use ($postData) {
                foreach ($postData as $item) {
                    $appointment = new Appointment();
                    $appointment->fill($item);
                    $durationsSum = SalonEmployeeHasServices::getSumOfDuration($item['salon_id'], $item["employee_id"], $item["services"]);
                    $end = AppointmentService::calculateEndOfAppointment($item["from_time"],$durationsSum);
                    $appointment->setAttribute("to_time",$end);
                    if ($appointment->save()) {
                        foreach ($item['services'] as $service) {
                            $appointmentHasService = new AppointmentHasServices();
                            $appointmentHasService->fill(["appointment_id" => $appointment->id, "service_id" => $service]);
                            $appointmentHasService->save();
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(["status" => "ERROR", "message" => $e->getMessage()], 400);
        }
        return response()->json(["status" => "OK", "message" => "The appointment have been successfully saved."], 200);
    }

    private function validateStoreData($data)
    {
        $appointmentRequest = new AppointmentStoreRequest();
        try {
            $validator = Validator::make($data, $appointmentRequest->rules());
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        } catch (ValidationException $exception) {
            $appointmentRequest->failedValidation($exception->validator);
        }
    }
}