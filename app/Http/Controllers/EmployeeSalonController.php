<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalonHasEmployees;
use Illuminate\Http\Request;

class EmployeeSalonController extends Controller
{
    /**
     * Connect salon and employee each other
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        foreach ($request->input('salons') as $key => $value) {
            $employeeSalon = SalonHasEmployees::add($value['salon'], $request->input('employee_id'));
        }
        $data = [];
        $data['employee'] = Employee::getById($request->input('employee_id'));
        $data['status'] = 'OK';
        return response()->json($data);
    }

    /**
     * Edit salon employee
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $employeeSalonIds = [];
        foreach ($request->input('salons') as $key => $value) {
            if (isset($value['id']) && SalonHasEmployees::getById($value['id'])) {
                $employeeSalon = SalonHasEmployees::edit($value['id'], $value['salon'], $request->input('employee_id'));
            } else {
                $employeeSalon = SalonHasEmployees::add($value['salon'], $request->input('employee_id'));
            }
            $employeeSalonIds[$value['id']] = $value['id'];
        }
        $data = [];
        $data['employee'] = Employee::getById($request->input('employee_id'));
        $data['status'] = 'OK';
        return response()->json($data);
    }
}
