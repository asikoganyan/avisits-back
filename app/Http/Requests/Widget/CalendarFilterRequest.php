<?php

namespace App\Http\Requests\Widget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CalendarFilterRequest extends FormRequest
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
            "salon_id" => "required|exists:salons,id",
            "employees" => "array",
            "from" => "required|date_format:Y-m-d",
            "to" => "required|date_format:Y-m-d"
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return parent::messages(); // TODO: Change the autogenerated stub
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['ValidationError' => $validator->messages()], 422));
    }
}
