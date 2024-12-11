<?php

namespace App\Imports;

use App\Models\EmailContact;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmailContactImport implements ToCollection , WithHeadingRow
{
    protected $groupId;
    protected $status;
    public array $errors = [];

    public function __construct($groupId , $status, protected $emailColumn = null)
    {
        $this->groupId = $groupId;
        $this->status = $status;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['Excel File Should Contain at least 1 Record, Excluding Header Row.']);
        }

        $i = 0;
        foreach ($rows as $val) {
            $data = [];

            foreach ($val as $key => $dataVal) {
                if ($dataVal != '') {
                    if (filter_var($dataVal , FILTER_VALIDATE_EMAIL)) {
                        $data['email'] = $dataVal;
                    } else {
                        $data['name'] = $dataVal;
                    }
                }
            }

            $data['name'] = $row['name'] ?? $data['name'] ?? null;

            if($this->emailColumn)
            {
                $data['email'] = $val[Str::snake($this->emailColumn)] ?? null;
            }
            $i++;
            if (!isset($data['email']) || !$data['email']) {
                $this->errors[] = 'Invalid Email or No Email is Found in Row '. $i;
                continue;
            }

            EmailContact::query()->updateOrCreate([
                'email' => $data['email'],
                'email_group_id' => $this->groupId ,
                'user_id' => $this->status ? null : auth()->id() ,
            ] , [
                'name' => $data['name'] ,
                'status' => 1 ,
            ]);
        }
    }
}
