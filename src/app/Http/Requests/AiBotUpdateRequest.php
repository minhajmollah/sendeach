<?php

namespace App\Http\Requests;

use App\Models\AiBot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiBotUpdateRequest extends FormRequest
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
            'user_text' => ['nullable' , 'string' , 'max:1000'] ,
            'system_text' => [Rule::requiredIf(fn() => request('name') == AiBot::CHAT) , 'string' , 'max:1000'] ,
            'assistant_text' => 'nullable|string|max:256' ,
            'temperature' => 'required|decimal:0,2|min:0|max:2' ,
            'max_tokens' => 'required|numeric|min:1|max:100' ,
            'n' => 'nullable|numeric|min:1|max:2' ,
            'stop' => 'nullable|string|max:10' ,
            'messages_per_minute' => 'required|numeric|min:0|max:60' ,
            'enable_memory' => 'nullable',
        ];
    }
}
