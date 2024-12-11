<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomReplyUpdateRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'customs' => ['required', 'array'],
            'customs.*.reply' => 'required|string',
            'customs.*.keywords' => 'required|string',
            'customs.*.to_pause' => 'nullable',
            'customs.*.pause_duration' => 'nullable|numeric|min:0'
        ];
    }
}
