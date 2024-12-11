<?php

namespace App\Http\Requests;

use App\Models\Contact;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Shuchkin\SimpleXLSX;

/**
 * @property string $whatsapp_phone_number_id
 */
class WhatsappMessageSendRequest extends FormRequest
{
    /**
     * @var array|string Recipient Phone Number
     */
    public $to;


    /**
     * @var bool
     */
    public $isMultipleRecipient;
    public $whatsappBusiness;
    public $template;

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
        $this->template = WhatsappTemplate::where('whatsapp_template_id' , $this->template_id)->first();
        $this->whatsappBusiness = $this->template?->whatsappBusinessAccount;

        return [
            'recipent_type' => 'required|in:array,toNumberInput,toNumbersFromFileInput,toNumberGroupInput' ,
            'whatsapp_phone_number_id' => ['required' , Rule::exists('whatsapp_phone_numbers' , 'whatsapp_phone_number_id')
                ->where(function ($query){
                    return $query->where('is_public', true)->orWhere('user_id' , auth()->id());
                })] ,
            'file' => 'file|nullable' ,
            'schedule' => 'required|in:1,2' ,
            'schedule_date' => ['required_if:schedule,2' , 'after:' . now()] ,
            'template_var_BODY[]' => 'array|nullable' ,
            'template_var_HEADER[]' => 'array|nullable' ,
            'group_id' => 'nullable|array|min:1' ,
            'group_id.*' => ['nullable' , Rule::exists('groups' , 'id')->where('user_id' , auth()->id())] ,
            'template_id' => ['required' , 'string' ,
                Rule::exists('whatsapp_templates' , 'whatsapp_template_id')
                    ->where('status' , 'APPROVED')
                    ->where('whatsapp_business_id' , optional($this->whatsappBusiness)->whatsapp_business_id)
                    ->where(function ($query){
                        return $query->where('is_public', true)
                            ->orWhere('user_id' , auth()->id());
                    })
            ]
        ];
    }

    public function messages()
    {
        return array_merge(
            parent::messages() ,
            [
                'template_id.exists' => 'Please choose template ID registered to phone number or invalid template ID.' ,
                'whatsapp_phone_number_id.exists' => 'Please choose your valid phone number ID.'
            ]
        );
    }

    /**
     * @throws ValidationException
     */
    public function handle()
    {

        if($this->template->is_public && auth()->user()?->credit < 1)
        {
            throw ValidationException::withMessages(['You don\'t have enough credits to use sendEach Whatsapp Business Gateway. Please Buy Some Credits.']);
        }

        $this->to = $this->getRecipientNumber();

        if (!$this->to) throw ValidationException::withMessages(['Please provide recipient to send. Or Invalid.']);
    }

    public function getRecipientNumber(): array
    {
        $allContactNumber = [];

        if ($this->recipent_type === 'toNumberInput' && $this->number) {
            $contactNumber = preg_replace('/[ ,]+/' , ',' , trim($this->number));
            $recipientNumber = explode("," , $contactNumber);
            $allContactNumber[] = $recipientNumber;
        }
        if ($this->recipent_type === 'toNumbersFromFileInput' && $this->file) {
            $allContactNumber = $this->parseRecipientFile();
        }

        if ($this->group_id) {
            $groupNumber = Contact::active()->whereNull('user_id')->whereIn('group_id' , $this->group_id)->pluck('contact_no')->toArray();
            $allContactNumber[] = $groupNumber;
        }

        $contactNewArray = [];
        foreach ($allContactNumber as $childArray) {
            foreach ($childArray as $value) {
                $contactNewArray[] = $value;
            }
        }

        return array_unique($contactNewArray);
    }

    protected function parseRecipientFile(): array
    {
        if (!$this->file) return [];

        $allContactNumber = [];

        $extension = strtolower($this->file->getClientOriginalExtension());
        if (!in_array($extension , ['csv' , 'txt' , 'xlsx'])) {
            $notify[] = ['error' , 'Invalid file extension'];
            return back()->withNotify($notify);
        }
        if ($extension == "txt") {
            $contactNumberTxt = file($this->file);
            unset($contactNumberTxt[0]);
            $txtNumber = array_values($contactNumberTxt);
            $txtNumber = preg_replace('/[^a-zA-Z0-9_ -]/s' , '' , $txtNumber);
            array_push($allContactNumber , $txtNumber);
        }
        if ($extension == "csv") {
            $contactNumberCsv = array();
            $contactNameCsv = array();
            $nameNumberArray[] = [];
            $csvArrayLength = 0;
            if (($handle = fopen($this->file , "r")) !== FALSE) {
                while (($data = fgetcsv($handle , 1000 , ",")) !== FALSE) {
                    if ($csvArrayLength == 0) {
                        $csvArrayLength = count($data);
                    }
                    foreach ($data as $dataVal) {
                        if (filter_var($dataVal , FILTER_SANITIZE_NUMBER_INT)) {
                            array_push($contactNumberCsv , $dataVal);
                        } else {
                            array_push($contactNameCsv , $dataVal);
                        }
                    }
                }
            }
            for ($i = 0; $i < $csvArrayLength; $i++) {
                unset($contactNameCsv[$i]);
            }
            if ((count($contactNameCsv)) == 0) {
                $contactNameCsv = $contactNumberCsv;
            }
            $nameNumberArray = array_combine($contactNumberCsv , $contactNameCsv);
            $csvNumber = array_values($contactNumberCsv);
            array_push($allContactNumber , $csvNumber);
        }
        if ($extension == "xlsx") {
            $nameNumberArray[] = [];
            $contactNameXlsx = array();
            $exelArrayLength = 0;
            $contactNumberxlsx = array();
            $xlsx = SimpleXLSX::parse($this->file);
            $data = $xlsx->rows();
            foreach ($data as $key => $val) {
                if ($exelArrayLength == 0) {
                    $exelArrayLength = count($val);
                }
                foreach ($val as $dataKey => $dataVal) {
                    if (filter_var($dataVal , FILTER_SANITIZE_NUMBER_INT)) {
                        array_push($contactNumberxlsx , $dataVal);
                    } else {
                        array_push($contactNameXlsx , (string)$dataVal);
                    }
                }
            }
            for ($i = 0; $i < $exelArrayLength; $i++) {
                unset($contactNameXlsx[$i]);
            }
            if ((count($contactNameXlsx)) == 0) {
                $contactNameXlsx = $contactNumberxlsx;
            }
            $nameNumberArray = array_combine($contactNumberxlsx , $contactNameXlsx);
            $excelNumber = array_values($contactNumberxlsx);
            array_push($allContactNumber , $excelNumber);
        }

        return $allContactNumber;
    }
}
