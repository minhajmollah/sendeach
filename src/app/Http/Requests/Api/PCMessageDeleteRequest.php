<?php

namespace App\Http\Requests\Api;

use App\Models\WhatsappLog;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PCMessageDeleteRequest extends FormRequest
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
            'id' => ['required', Rule::exists('whatsapp_pc_message_deletes', 'id')->where('user_id', auth()->id())],
            'status' => ['required', Rule::in(WhatsappLog::STATUSES)],
            'message' => ['nullable', 'string']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => "failed",
        ], 422));
    }
}
