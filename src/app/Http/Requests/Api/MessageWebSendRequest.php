<?php

namespace App\Http\Requests\Api;

use App\Rules\MessageFileValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MessageWebSendRequest extends FormRequest
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
        $message = 'message';
        $rules = 'required';
        if($this->hasFile('document')){
            $message = 'document';
            $rules = ['required', 'file'];
        } else if($this->hasFile('audio')){
            $message = 'audio';
            $rules = ['required', new MessageFileValidationRule('audio')];
        } else if($this->hasFile('image')){
            $message = 'image';
            $rules = ['required', new MessageFileValidationRule('image')];
        } else if($this->hasFile('video')){
            $message = 'video';
            $rules = ['required', new MessageFileValidationRule('video')];
        }

        return [
            $message => $rules,
            'whatsapp_device' => ['nullable'],
            "recipients" => 'required|array'
        ];
    }
}
