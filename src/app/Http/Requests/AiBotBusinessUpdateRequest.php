<?php

namespace App\Http\Requests;

use App\Models\AiBot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiBotBusinessUpdateRequest extends FormRequest
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
            'is_enabled' => 'nullable',
            'is_palm_enabled' => 'nullable',
            'website' => ['nullable', 'active_url'],
            'business_text' => ['required' , 'string' , 'max:8000'] ,
        ];
    }
}
