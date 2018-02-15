<?php

namespace App\Http\Requests\SalonSchedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SalonScheduleUpdateRequest extends FormRequest
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
            "salon_id"=>"exists:salons,id",
            "start"=>"date_format:H:i",
            "end"=>"date_format:H:i",
            "num_of_day"=>"in:1,2,3,4,5,7",
            "working_status"=>"integer|max:1"
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['ValidationError' => $validator->messages()],422));
    }
}
