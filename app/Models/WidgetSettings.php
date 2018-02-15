<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetSettings extends Model
{
    protected $table = "chains";

    protected $fillable = [
        'id',
        'img',
        'phone_number',
        'w_color',
        'w_group_by_category',
        'w_show_any_employee',
        'w_step_display',
        'w_step_search',
        'w_let_check_steps',
        'w_steps_g',
        'w_steps_service',
        'w_steps_employee',
        'w_contact_step',
        'w_contact_step',
        'w_notification_text'
    ];

    protected $hidden = [
        "title",
        "user_id",
        "created_at",
        "updated_at",
    ];

    public function allSteps()
    {
        return [
            "address"=>"Адрес",
            "service"=>"Услуги",
            "employee"=>"Сотрудники",
            "employee_time"=>"Сотрудники, Время",
            "time"=>"Время",
        ];
    }
    public static function getStepsGeneral()
    {
        $general = [
            "address,service,time" => "Адрес -> Услуги -> Время",
            "service,address,time" => "Услуги -> Адрес -> Время",
        ];
        return array_merge(self::getStepsService(), self::getStepsEmployee(),$general);
    }

    public static function getStepsService()
    {
        return [
            "address,service,employee_time" => "Адрес -> Услуги -> Сотрудники, Время",
            "service,address,employee_time" => "Услуги -> Адрес -> Сотрудники, Время",
        ];
    }

    public static function getStepsEmployee()
    {
        return [
            "address,employee,service,time" => "Адрес -> Сотрудники -> Услуги -> Время",
            "employee,service,address,time" => "Сотрудники -> Услуги -> Адрес -> Время",
            "employee,address,service,time" => "Сотрудники -> Адрес -> Услуги -> Время",
        ];
    }

    public static function getContactSteps() {
        return [
            "at_first"=>"В начале",
            "after_address"=>"После выбора адреса",
            "at_the_end"=>"В конце"
        ];

    }
    public static function getDefaultSettings() {
        return [
            "w_color"=>"#ff7f00",
            "w_group_by_category"=>1,
            "w_show_any_employee"=>0,
            "w_step_display"=>15,
            "w_step_search"=>0,
            "w_let_check_steps"=>1,
            "w_steps_g"=>"address,employee,service,time",
            "w_steps_service"=>"address,service,employee_time",
            "w_steps_employee"=>"address,employee,service,time",
            "w_contact_step"=>"at_the_end",
            "w_notification_text"=>""
        ];
    }

    /**
     * @param $value
     * @return array
     */
    public function getWStepsGAttribute($value)
    {
        return explode(',',$value);
    }

    /**
     * @param $value
     * @return array
     */
    public function getWStepsServiceAttribute($value)
    {
        return explode(',',$value);
    }

    /**
     * @param $value
     * @return array
     */
    public function getWStepsEmployeeAttribute($value)
    {
        return explode(',',$value);
    }

    /**
     * @param $value
     * @return string
     *
     */
    public function getImgAttribute($value) {
        if(!$value){
            return null;
        }
        $ds = DIRECTORY_SEPARATOR;
        return 'files'.$ds.'chains'.$ds.'images'.$ds.'main'.$ds.$value;
    }
}