<?php

namespace App\Http\Requests;

use App\Models\EmailContact;
use App\Models\EmailGroup;
use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;

class MailSendRequest extends FormRequest
{
    public array $emailGroupName;
    public array|null $emailGroupID;
    public array $toEmails;

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
            'mail_gateway' => ['nullable' , 'exists:mails,id'] ,
            'subject' => 'required' ,
            'message' => 'required' ,
            'schedule' => 'required|in:1,2' ,
            'schedule_date' => 'required_if:schedule,2' ,
            'email_group_id' => 'nullable|array|min:1' ,
            'email_group_id.*' => ['nullable', Rule::exists('email_groups', 'id')
                ->when(auth()->id(), fn($q) => $q->where('user_id', auth()->id()))],
            'email.*' => 'nullable|email' ,
        ];
    }

    public function handle()
    {
        $this->emailGroupName = [];
        $this->toEmails = [];
        if (is_array($this->email) && count($this->email) > 0) {
            $this->toEmails[] = $this->email;

        }
        $this->emailGroupID = null;

        if ($this->email_group_id) {
            $group = EmailContact::active()->where('user_id', auth()->id())
                ->whereNotIn('email', EmailGroup::unsubscribedContacts(auth()->id())
                    ->pluck('email')->toArray())
                ->whereIn('email_group_id', $this->email_group_id)->get();
            $emailGroup = $group->pluck('email')->toArray();
            $this->emailGroupName = $group->pluck('name', 'email')->toArray();
            $this->emailGroupID = $group->pluck('id', 'email')->toArray();
            $this->toEmails[] = $emailGroup;
        }

        if ($this->file) {
            $extension = strtolower($this->file->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'xlsx'])) {
                $notify[] = ['error', 'Invalid file extension'];
                return back()->withNotify($notify);
            }
            if ($extension == "csv") {
                $contactNameCsv = array();
                $nameEmailArray[] = [];
                $csvArrayLength = 0;
                $contactEmailCsv = array();

                if (($handle = fopen($this->file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if ($csvArrayLength == 0) {
                            $csvArrayLength = count($data);
                        }
                        foreach ($data as $dataVal) {
                            if (filter_var($dataVal, FILTER_VALIDATE_EMAIL)) {
                                $contactEmailCsv[] = $dataVal;
                            } else {
                                $contactNameCsv[] = $dataVal;
                            }
                        }
                    }
                }
                for ($i = 0; $i < $csvArrayLength; $i++) {
                    unset($contactNameCsv[$i]);
                }
                if ((count($contactNameCsv)) == 0) {
                    $contactNameCsv = $contactEmailCsv;
                }
                $nameEmailArray = array_combine($contactEmailCsv, $contactNameCsv);
                $this->emailGroupName = array_merge($this->emailGroupName, $nameEmailArray);
                $csvEmail = array_values($contactEmailCsv);
                $this->toEmails[] = $csvEmail;
            }
            if ($extension == "xlsx") {
                $nameEmailArray[] = [];
                $contactEmailxlsx = array();
                $exelArrayLength = 0;
                $contactNameXlsx = array();
                $xlsx = SimpleXLSX::parse($this->file);
                $data = $xlsx->rows();
                foreach ($data as $key => $val) {
                    if ($exelArrayLength == 0) {
                        $exelArrayLength = count($val);
                    }
                    foreach ($val as $dataKey => $dataVal) {
                        if (filter_var($dataVal, FILTER_VALIDATE_EMAIL)) {
                            $contactEmailxlsx[] = $dataVal;
                        } else {
                            $contactNameXlsx[] = $dataVal;
                        }
                    }
                }
                for ($i = 0; $i < $exelArrayLength; $i++) {
                    unset($contactNameXlsx[$i]);
                }
                if ((count($contactNameXlsx)) == 0) {
                    $contactNameXlsx = $contactEmailxlsx;
                }
                $nameEmailArray = array_combine($contactEmailxlsx , $contactNameXlsx);
                $this->emailGroupName = array_merge($this->emailGroupName , $nameEmailArray);
                $excelEmail = array_values($contactEmailxlsx);
                $this->toEmails[] = $excelEmail;
            }
        }

        $contactNewArray = [];

        foreach ($this->toEmails as $childArray) {
            foreach ($childArray as $value) {
                $contactNewArray[] = $value;
            }
        }
        $this->toEmails = array_unique($contactNewArray);

        return $this->toEmails;
    }
}
