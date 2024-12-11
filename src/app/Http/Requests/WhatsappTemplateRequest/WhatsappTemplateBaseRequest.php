<?php

namespace App\Http\Requests\WhatsappTemplateRequest;

use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WhatsappTemplateBaseRequest extends FormRequest
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
        return array_merge(WhatsappTemplate::rules());
    }

    /**
     * @throws ValidationException
     */
    public function handle()
    {

        $components = [];

        $bodyText = Arr::get($this->components , 'BODY.text');
        $bodyVars = $this->getVars($bodyText);
        $exampleVars = Arr::get($this->components , 'BODY.example.body_text') ?? [];

        if ($bodyVars && count($bodyVars) != count($exampleVars)) {
            throw ValidationException::withMessages(['components.BODY.text' => 'Please provide example BODY variable values.']);
        }

        $components[] = [
            'type' => 'BODY' ,
            'text' => $bodyText
        ];

        if ($exampleVars) {
            $components[0]['example']['body_text'][] = $exampleVars;
        }

        $headerText = Arr::get($this->components , 'HEADER.text');


        if ($headerText) {
            $headerVars = $this->getVars($headerText);
            $exampleVars = Arr::get($this->components , 'HEADER.example.header_text') ?? [];

            if ($headerVars && count($exampleVars) != count($headerVars)) {
                throw ValidationException::withMessages(['components.HEADER.text' => 'Please provide example HEADER variable values.']);
            }

            if($headerVars && count($headerVars) > 1)
            {
                throw ValidationException::withMessages(['components.HEADER.text' => 'Header cannot contain more than 1 variable.']);
            }

            $components[] = [
                'type' => 'HEADER' ,
                'format' => 'TEXT' ,
                'text' => $headerText
            ];

            if ($exampleVars) {
                $components[1]['example']['header_text'] = $exampleVars;
            }
        }

        $footerText = Arr::get($this->components , 'FOOTER.text');

        if ($footerText) {
            $components[] = [
                'type' => 'FOOTER' ,
                'text' => $footerText
            ];
        }

        return $components;
    }

    public function getVars($text)
    {
        $regex = '/\{\{([0-9])}\}/';
        $vars = Str::matchAll($regex , $text)->toArray();

        // Validate variable no
        for($i = 0; $i < count($vars); $i++)
        {
            if($vars[$i] != $i+1){
                throw ValidationException::withMessages(['components.*.text' => 'VARIABLE_NO should sequentially start with 1 and ends with 9']);
            }
        }

        return $vars;
    }
}
