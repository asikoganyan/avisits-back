<?php

namespace App\Http\Requests\WidgetSettings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class WidgetSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "w_color"=>"string|max:255",
            "w_group_by_category"=>"integer|max:1",
            "w_show_any_employee"=>"integer|max:1",
            "w_step_display"=>"integer|max:15",
            "w_step_search"=>"integer|max:15",
            "w_let_check_steps"=>"integer|max:1",
            "w_steps_g"=>"string|max:255",
            "w_steps_service"=>"string|max:255",
            "w_steps_employee"=>"string|max:255",
            "w_contact_step"=>"string|max:255",
            "w_notification_text"=>"string|max:255"
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['ValidationError' => $validator->messages()],422));
    }
}
