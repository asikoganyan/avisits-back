<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalonHasEmployees extends Model
{
    protected $table = "salon_has_employees";

    /**
     * Get employee salon by id
     *
     * @param $id
     * @return mixed
     */
    public static function getById($id)
    {
        $employeeSalon = self::query()->find($id);
        return $employeeSalon;
    }

    /**
     * Add salon to employee
     *
     * @param $salonId
     * @param $employeeId
     * @return SalonHasEmployees|array
     */
    public static function add($salonId, $employeeId)
    {
        $salonEmployee = new self();
        $salonEmployee->salon_id = $salonId;
        $salonEmployee->employee_id = $employeeId;
        if ($salonEmployee->save()) {
            return $salonEmployee;
        }
        return [];
    }

    /**
     * Edit salon employee
     *
     * @param $id
     * @param $salonId
     * @param $employeeId
     * @return array|mixed
     */
    public static function edit($id, $salonId, $employeeId)
    {
        $salonEmployee = self::getById($id);
        if ($salonEmployee) {
            $salonEmployee->salon_id = $salonId;
            $salonEmployee->employee_id = $employeeId;
            if ($salonEmployee->save()) {
                return $salonEmployee;
            }
        }
        return [];
    }

    /**
     * Delete except ids
     *
     * @param $employeeId
     * @param $ids
     */
    public static function deleteExceptIds($employeeId, $ids)
    {
        self::where('employee_id', $employeeId)->whereNotIn('id', $ids)->delete();
    }

    /**
     * Relationship for get salon
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salon()
    {
        return $this->hasMany('App\Models\Salon', 'id', 'salon_id');
    }
}
