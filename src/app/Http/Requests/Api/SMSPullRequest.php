<?php

namespace App\Http\Requests\Api;

use App\Models\UserFcmToken;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SMSPullRequest extends FormRequest
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
            'device_token' => ['nullable', Rule::exists('user_fcm_tokens', 'token')->where('user_id', auth()->id())],
            'device_id' => ['required_if:device_token,null', Rule::exists('user_fcm_tokens', 'device_id')->where('user_id', auth()->id())],
            'limit' => ['nullable', 'numeric', 'max:1000', 'min:0']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'success' => false,
        ], 422));
    }
}
