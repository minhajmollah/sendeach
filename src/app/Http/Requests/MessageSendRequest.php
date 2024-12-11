<?php

namespace App\Http\Requests;

use App\Models\Contact;
use App\Models\Group;
use App\Rules\MessageFileValidationRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Shuchkin\SimpleXLSX;

/**
 * @property string $whatsapp_phone_number_id
 */
class MessageSendRequest extends FormRequest
{
    /**
     * @var array Recipient Phone Number
     */
    public array $to = [];

    public array $numberGroupName = [];
    public array $groupIDContact = [];

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
        $rule = ['required'];
        $message = 'message';

        if ($this->hasFile('document')) {
            $message = 'document';
            $rule = ['required', 'file'];
        } else if ($this->hasFile('audio')) {
            $message = 'audio';
            $rule = ['required', new MessageFileValidationRule('audio')];
        } else if ($this->hasFile('image')) {
            $message = 'image';
            $rule = ['required', new MessageFileValidationRule('image')];
        } else if ($this->hasFile('video')) {
            $message = 'video';
            $rule = ['required', new MessageFileValidationRule('video')];
        }

        return [
            'recipent_type' => 'required|in:array,toNumberInput,toNumbersFromFileInput,toNumberGroupInput',
            'file' => 'file|nullable',
            'schedule' => 'required|in:1,2',
            $message => $rule,
            'spinMessage' => ['nullable', 'array'],
            'schedule_date' => ['required_if:schedule,2', 'after:' . now()],
            'group_id' => 'nullable|array|min:1',
            'group_id.*' => ['nullable', Rule::exists('groups', 'id')->where('user_id', auth()->id())],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function handle()
    {
        $this->to = $this->getRecipientNumber();

        $this->schedule_date = Carbon::parse($this->schedule_date);

        if (!$this->to) throw ValidationException::withMessages(['Please provide recipient to send. Or Invalid.']);
    }

    public function getRecipientNumber(): array
    {
        $allContactNumber = [];

        if ($this->recipent_type === 'toNumberInput' && $this->number) {
            $contactNumber = preg_replace('/[ ,]+/', ',', trim($this->number));
            $contactNumber = str_replace('+', '', $contactNumber);
            $recipientNumber = explode(",", $contactNumber);
            $allContactNumber[] = $recipientNumber;
        }
        if ($this->recipent_type === 'toNumbersFromFileInput' && $this->file) {
            $allContactNumber = $this->parseRecipientFile();
        }

        if ($this->group_id) {
            $group = Contact::active()->where('user_id', auth()->id())
                ->whereNotIn('contact_no',
                    Group::unsubscribedContacts(auth()->id())->pluck('contact_no')->toArray()
                )
                ->whereIn('group_id', $this->group_id)->get();

            $groupNumber = $group->pluck('contact_no')->toArray();
            $this->numberGroupName += $group->pluck('name', 'contact_no')->toArray();
            $this->groupIDContact = $group->pluck('id', 'contact_no')->toArray();
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

    public function parseRecipientFile(): array
    {
        if (!$this->file) return [];

        $allContactNumber = [];
        $nameNumberArray = [];

        $extension = strtolower($this->file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xlsx'])) {
            $notify[] = ['error', 'Invalid file extension'];
            return back()->withNotify($notify);
        }
        if ($extension == "txt") {
            $contactNumberTxt = file($this->file);
            unset($contactNumberTxt[0]);
            $txtNumber = array_values($contactNumberTxt);
            $txtNumber = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $txtNumber);
            $allContactNumber[] = $txtNumber;
        }
        if ($extension == "csv") {
            $contactNumberCsv = array();
            $contactNameCsv = array();
            $nameNumberArray[] = [];
            $csvArrayLength = 0;
            if (($handle = fopen($this->file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($csvArrayLength == 0) {
                        $csvArrayLength = count($data);
                    }
                    foreach ($data as $dataVal) {
                        if (filter_var($dataVal, FILTER_SANITIZE_NUMBER_INT)) {
                            $contactNumberCsv[] = $dataVal;
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
                $contactNameCsv = $contactNumberCsv;
            }
            $nameNumberArray = array_combine($contactNumberCsv, $contactNameCsv);
            $csvNumber = array_values($contactNumberCsv);
            $allContactNumber[] = $csvNumber;
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
                    if (filter_var($dataVal, FILTER_SANITIZE_NUMBER_INT)) {
                        $contactNumberxlsx[] = $dataVal;
                    } else {
                        $contactNameXlsx[] = (string)$dataVal;
                    }
                }
            }
            for ($i = 0; $i < $exelArrayLength; $i++) {
                unset($contactNameXlsx[$i]);
            }
            if ((count($contactNameXlsx)) == 0) {
                $contactNameXlsx = $contactNumberxlsx;
            }

            $nameNumberArray = array_combine($contactNumberxlsx, $contactNameXlsx);
            $excelNumber = array_values($contactNumberxlsx);
            $allContactNumber[] = $excelNumber;
        }

        $this->numberGroupName += $nameNumberArray;

        return $allContactNumber;
    }


    public function getAttachment()
    {
        $postData = [];
        try {

            if ($this->hasFile('document')) {
                $file = $this->file('document');
                $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
                $path = filePath()['whatsapp']['path_document'];
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                move_uploaded_file($file->getRealPath(), $path . '/' . $fileName);

                $postData['type'] = 'Document';
                $postData['url_file'] = $path . '/' . $fileName;
                $postData['name'] = $fileName;

                $this->document = url($postData['url_file']);
                $this->file_caption = $file->getClientOriginalName();
            }

            if ($this->hasFile('audio')) {
                $file = $this->file('audio');
                $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
                $path = filePath()['whatsapp']['path_audio'];
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                move_uploaded_file($file->getRealPath(), $path . '/' . $fileName);

                $postData['type'] = 'Audio';
                $postData['url_file'] = $path . '/' . $fileName;
                $postData['name'] = $fileName;
                $this->audio = url($postData['url_file']);
                $this->file_caption = $file->getClientOriginalName();
            }
            if ($this->hasFile('image')) {
                $file = $this->file('image');
                $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
                $path = filePath()['whatsapp']['path_image'];
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                move_uploaded_file($file->getRealPath(), $path . '/' . $fileName);

                $postData['type'] = 'Image';
                $postData['url_file'] = $path . '/' . $fileName;
                $postData['name'] = $fileName;
                $this->image = url($postData['url_file']);
                $this->file_caption = $file->getClientOriginalName();

            }
            if ($this->hasFile('video')) {
                $file = $this->file('video');
                $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
                $path = filePath()['whatsapp']['path_video'];
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                move_uploaded_file($file->getRealPath(), $path . '/' . $fileName);
                $postData['type'] = 'Video';
                $postData['url_file'] = $path . '/' . $fileName;
                $postData['name'] = $fileName;
                $this->video = url($postData['url_file']);
                $this->file_caption = $file->getClientOriginalName();
            }
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
        }

        return $postData;
    }
}
