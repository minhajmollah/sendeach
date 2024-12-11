<?php

namespace App\Http\Requests;

use App\Models\Contact;
use App\Models\UserFcmToken;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;

class SMSSendRequest extends FormRequest
{
    public $numberGroupName;

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
            'message' => 'required' ,
            'recipients' => ['required', 'array'] ,
            'smsType' => 'nullable|in:plain,unicode' ,
            'schedule' => 'nullable|in:1,2' ,
            'shedule_date' => 'required_if:schedule,2' ,
            'group_id' => 'nullable|array|min:1' ,
            'group_id.*' => 'nullable|exists:groups,id,user_id,' . auth()->id() ,
        ];
    }

    public function getRecipientNumber(): array
    {
        $allContactNumber = [];

        if ($this->recipent_type === 'toNumberInput' && $this->recipients) {
            $contactNumber = preg_replace('/[ ,]+/' , ',' , trim($this->recipients));
            $recipientNumber = explode("," , $contactNumber);
            $allContactNumber[] = $recipientNumber;
        }
        if ($this->recipent_type === 'toNumbersFromFileInput' && $this->file) {
            $allContactNumber = $this->parseRecipientFile();
        }

        if ($this->group_id) {
            $groupNumber = Contact::whereNull('user_id')->whereIn('group_id' , $this->group_id)->pluck('contact_no')->toArray();
            $this->numberGroupName = Contact::where('user_id' , auth()->id())->whereIn('group_id' , $this->group_id)->pluck('name' , 'contact_no')->toArray();
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
