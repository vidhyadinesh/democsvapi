<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatecsvRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [            
            'module_code' => 'required|regex:/(^[A-Za-z0-9]*$)/u',
            'module_name' => 'required|regex:/(^[A-Za-z0-9]*$)/u',
            'module_term' => 'required|regex:/(^[A-Za-z0-9]*$)/u',
        ];
    }
}
