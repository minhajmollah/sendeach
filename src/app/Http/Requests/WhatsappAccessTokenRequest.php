<?php

namespace App\Http\Requests;

use App\Models\WhatsappAccessToken;
use App\Services\WhatsappService\WhatsappBusinessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WhatsappAccessTokenRequest extends FormRequest
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
            'accessToken' => ['required' , 'string']
        ];
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function handle()
    {
        $response = (new WhatsappBusinessService())->debugToken($this->accessToken);

        if ($response->status() != 200) {
            throw ValidationException::withMessages(['accessToken' => 'Unable to verify token.']);
        }

        $response = $response->json();

        if (Arr::get($response , 'data.error')) {
            throw ValidationException::withMessages(['accessToken' => 'Invalid OAuth Access Token']);
        }

        $scopes = Arr::get($response , 'data.scopes');

        $permissionsMissing = array_diff(['whatsapp_business_management' , 'whatsapp_business_messaging'] , $scopes);

        if ($permissionsMissing) {
            throw ValidationException::withMessages(['accessToken' => 'Access token must have ' . join($permissionsMissing , ', ') . ' Permissions.']);
        }
    }
}
