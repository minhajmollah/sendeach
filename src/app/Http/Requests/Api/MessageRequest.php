<?php

namespace App\Http\Requests\Api;

use App\Http\Controllers\Api\ManageWhatsappController;
use App\Models\WhatsappLog;
use Doctrine\Inflector\Rules\French\Rules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MessageRequest extends FormRequest
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
        $this->fields = $this->fields ? array_map(fn($val) => trim($val), explode(',', $this->fields)) : null;
        $this->log_ids = $this->log_ids ? array_map(fn($val) => trim($val), explode(',', $this->log_ids)) : null;

        return [
            'fields' => ['nullable', ],
            'status' => ['nullable', Rule::in([WhatsappLog::PENDING, WhatsappLog::FAILED, WhatsappLog::SUCCESS, WhatsappLog::PROCESSING, WhatsappLog::SCHEDULE])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'windows_token' => ['nullable']
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
