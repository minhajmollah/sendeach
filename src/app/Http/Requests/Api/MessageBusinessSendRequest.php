<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\WhatsappMessageSendRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MessageBusinessSendRequest extends WhatsappMessageSendRequest
{
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => "failed",
        ], 422));
    }
}
