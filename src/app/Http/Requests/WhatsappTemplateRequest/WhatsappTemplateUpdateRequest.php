<?php

namespace App\Http\Requests\WhatsappTemplateRequest;

use App\Models\WhatsappTemplate;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;

class WhatsappTemplateUpdateRequest extends WhatsappTemplateBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Arr::only(WhatsappTemplate::rules(), 'components');
    }

    public function handle()
    {
        if (!$this->whatsappTemplate->isEditable()) {
            throw ValidationException::withMessages(['error' => 'Cannot edit template.']);
        }

        return parent::handle();
    }
}
