<?php

namespace App\Imports;

use App\Models\Contact;
use App\Models\GeneralSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactImport implements ToCollection , WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    protected $groupId;
    protected $status;
    public array $errors = [];

    public function __construct($groupId , $status, protected $phoneColumn = null)
    {
        $this->groupId = $groupId;
        $this->status = $status;
    }

    public function collection(Collection $rows)
    {

        $general = GeneralSetting::first();

        if ($rows->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['Excel File Should Contain at least 1 Record, Excluding Header Row.']);
        }

        $i = 0;

        foreach ($rows as $row) {
            $data = [];

            foreach ($row as $key => $dataVal) {
                if ($dataVal != '') {
                    if (filter_var($dataVal , FILTER_SANITIZE_NUMBER_INT) && strlen($dataVal) >= 10) {
                        $data['contact_no'] = $dataVal;
                    } else {
                        $data['name'] = $dataVal;
                    }
                }
            }
            $data['name'] = $row['name'] ?? $data['name'] ?? null;

            if($this->phoneColumn)
            {
                $data['contact_no'] = $row[Str::snake($this->phoneColumn)] ?? null;
            }

            $i++;
            if (!isset($data['contact_no']) || !$data['contact_no']) {
                $this->errors[] = 'Invalid Phone or No Phone is Found in Row '. $i;
                continue;
            }

            Contact::query()->updateOrCreate([
                'user_id' => $this->status ? null : auth()->id() ,
                'group_id' => $this->groupId ,
                'contact_no' => $data['contact_no'] ,
            ] , [
                'name' => $data['name'] ,
                'status' => 1 ,
            ]);
        }
    }
}
