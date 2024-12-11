<?php

namespace App\Http\Requests;

use App\Models\WhatsappDevice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WhatsappBotUpdateRequest extends FormRequest
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
            'whatsapp_gateway_id' => ['required' , Rule::exists('wa_device' , 'id')
                ->where('user_id' , auth()->id())->where('status' , WhatsappDevice::STATUS_CONNECTED)] ,
            'ai_bot_id' => ['nullable' , Rule::exists('ai_bots' , 'id')->where('user_id' , auth()->id())] ,
            'is_enabled' => ['nullable'] ,
            'ignored_numbers.*' => ['required' , 'numeric' , 'digits_between:11,15'] ,
            'allowed_numbers.*' => ['required' , 'numeric' , 'digits_between:11,15'] ,
            'greetings_text' => ['nullable'] ,
            'handle_only_unknown_user' => ['nullable'] ,
        ];
    }

    public function messages()
    {
        return array_merge(parent::messages() , [
            'ignored_numbers.*' => 'One of the Whatsapp Number is Invalid.' ,
            'allowed_numbers.*' => 'One of the Whatsapp Number is Invalid.' ,
        ]);
    }


}
