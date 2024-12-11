<?php

namespace App\Http\Requests\Api;

use App\Models\WhatsappLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageUpdateRequest extends MessageRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'log_id' => ['nullable', Rule::exists('whatsapp_logs', 'id')->where('user_id', auth()->id())],
            'log_id.*' => ['nullable', Rule::exists('whatsapp_logs', 'id')->where('user_id', auth()->id())],
            'status' => ['required', Rule::in([WhatsappLog::PENDING, WhatsappLog::FAILED, WhatsappLog::SUCCESS, WhatsappLog::PROCESSING, WhatsappLog::SCHEDULE])]
        ]);
    }
}
