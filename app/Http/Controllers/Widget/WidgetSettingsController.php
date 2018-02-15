<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\WidgetSettings;
use Illuminate\Http\Request;

class WidgetSettingsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->chain = $request->route('chain');
    }

    public function index(Request $request)
    {
        $status = 200;
        $responseBody = ["data" => []];
        $params = $this->filterRequestData($request->all(), ["w_steps_service", "w_steps_employee", "w_color"]);
        $settings = WidgetSettings::find($this->chain);
        if ($settings->w_let_check_steps) {
            unset($settings->w_steps_g);
            if (isset($params["w_color"]) && !empty($params["w_color"])) {
                $settings->w_color = $params["w_color"];
            }
            if (isset($params['w_steps_service']) && !empty($params['w_steps_service'])) {
                if (key_exists($params['w_steps_service'], WidgetSettings::getStepsService())) {
                    $settings->w_steps_service = $params['w_steps_service'];
                } else {
                    $status = 400;
                    $responseBody["data"] = [];
                    $responseBody["status"] = "ERROR";
                    $responseBody["message"] = "checkSteps";
                    $responseBody["description"] = "Incorrect sequence of the Service steps!";
                    return response()->json($responseBody, $status);
                }
            }
            if (isset($params['w_steps_employee']) && !empty($params['w_steps_employee'])) {
                if (key_exists($params['w_steps_employee'], WidgetSettings::getStepsEmployee())) {
                    $settings->w_steps_employee = $params['w_steps_employee'];
                } else {
                    $status = 400;
                    $responseBody["data"] = [];
                    $responseBody["status"] = "ERROR";
                    $responseBody["message"] = "checkSteps";
                    $responseBody["description"] = "Incorrect sequence of the Employee steps!";
                    return response()->json($responseBody, $status);
                }
            }
            $status = 200;
            $responseBody["data"]["settings"] = $settings;
        } else {
            unset($settings->w_steps_service);
            unset($settings->w_steps_employee);
            /* Если не разрешено пользователю менять последовательность нутей, но производится попытка изменения последовательности */
            if ((isset($params["w_steps_service"]) && !empty($params["w_steps_service"])) || (isset($params["w_steps_employee"]) && !empty($params["w_steps_employee"]))) {
                $status = 400;
                $responseBody["data"] = [];
                $responseBody["status"] = "ERROR";
                $responseBody["message"] = "checkSteps";
                $responseBody["description"] = "You do not have the right to change the sequence of steps!";
            } else {
                if (isset($params["w_color"]) && !empty($params["w_color"])) {
                    $settings->w_color = $params["w_color"];
                }
                $status = 200;
                $responseBody["data"]["settings"] = $settings;
            }
        }
        return response()->json($responseBody, $status);
    }

    public function filterRequestData($params, $keys)
    {
        $params = collect($params)->filter(function ($item, $key) use ($keys) {
            return in_array($key, $keys);
        });
        return $params->all();
    }
}