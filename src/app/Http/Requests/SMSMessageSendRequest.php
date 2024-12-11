<?php

namespace App\Http\Requests;

use App\Models\SmsGateway;
use App\Models\UserFcmToken;
use Illuminate\Validation\Rule;

class SMSMessageSendRequest extends MessageSendRequest
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
            'message' => 'required',
            'gateway_or_device_type' => ['required', 'string', Rule::in(['mobile', 'gateway'])],
            'mobile_device_id' => [
                'nullable',
                Rule::exists((new UserFcmToken())->getTable(), 'id')->where('user_id', auth()->id())
            ],
            'gateway_id' => [
                Rule::requiredIf($this->gateway_or_device_type == 'gateway'),
                'nullable',
                Rule::exists((new SmsGateway())->getTable(), 'id')
            ],
            'smsType' => 'nullable|required|in:plain,unicode',
            'schedule' => 'required|in:1,2',
            'shedule_date' => 'required_if:schedule,2',
            'group_id' => 'nullable|array|min:1',
            'group_id.*' => 'nullable|exists:groups,id,user_id,' . auth()->id(),
        ];
    }
}
