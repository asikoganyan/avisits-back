<?php
namespace App\Http\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EmployeeService
{
    public static function ownEmployee(Request $request , $employee_id){
        $chainId = $request->route('chain') || null;
        $employee = Employee::where(['chain_id'=>$chainId,'id'=>$employee_id])->count();
        if($employee !== 0){
            return true;
        }
        return false;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ownErrorResponse(){
        return response()->json(["error" => "permission error", "message" => "incorrect employee_id"], 400);
    }
}