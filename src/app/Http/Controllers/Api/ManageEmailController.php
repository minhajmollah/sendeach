<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmail;
use App\Models\EmailContact;
use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shuchkin\SimpleXLSX;

class ManageEmailController extends Controller
{
    public function sendEmail(Request $request)
    {

        $user = Auth::user();
        $request->validate([
            'subject' => 'required' ,
            'message' => 'required' ,
            'schedule' => 'nullable|in:1,2' ,
            'shedule_date' => 'required_if:schedule,2' ,
            'email_group_id' => 'nullable|array|min:1' ,
            'email_group_id.*' => 'nullable|exists:email_groups,id,user_id,' . $user->id ,
            'email' => 'nullable|array' ,
            'email.*' => 'nullable|email' ,
        ]);

        $emailMethod = MailConfiguration::where(['user_id' => auth()->user()->id , 'user_type' => 'user' , 'status' => 1 , 'default_use' => 1])->first();

        if (!$emailMethod) {
            return response()->json(['status' => 'error' , 'message' => "Invalid Default gateway!. Please configure from web interface."] , 400);
        }

        if (!$request->email && !$request->email_group_id && !$request->file) {
            return response()->json(['status' => 'error' , 'message' => "Invalid Emails."] , 400);
        }
        $emailGroupName = [];
        $allEmail = [];
        if (is_array($request->email) && count($request->email) > 0) {
            array_push($allEmail , $request->email);
        }
        if ($request->email_group_id) {
            $emailGroup = EmailContact::where('user_id' , $user->id)->whereIn('email_group_id' , $request->email_group_id)->pluck('email')->toArray();
            $emailGroupName = EmailContact::where('user_id' , $user->id)->whereIn('email_group_id' , $request->email_group_id)->pluck('name' , 'email')->toArray();
            array_push($allEmail , $emailGroup);
        }
        if ($request->file) {
            $extension = strtolower($request->file->getClientOriginalExtension());
            if (!in_array($extension , ['csv' , 'xlsx'])) {
                $notify[] = ['error' , 'Invalid file extension'];
                return back()->withNotify($notify);
            }
            if ($extension == "csv") {
                $contactNameCsv = array();
                $nameEmailArray[] = [];
                $csvArrayLength = 0;
                $contactEmailCsv = array();

                if (($handle = fopen($request->file , "r")) !== FALSE) {
                    while (($data = fgetcsv($handle , 1000 , ",")) !== FALSE) {
                        if ($csvArrayLength == 0) {
                            $csvArrayLength = count($data);
                        }
                        foreach ($data as $dataVal) {
                            if (filter_var($dataVal , FILTER_VALIDATE_EMAIL)) {
                                array_push($contactEmailCsv , $dataVal);
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
                    $contactNameCsv = $contactEmailCsv;
                }
                $nameEmailArray = array_combine($contactEmailCsv , $contactNameCsv);
                $emailGroupName = array_merge($emailGroupName , $nameEmailArray);
                $csvEmail = array_values($contactEmailCsv);
                array_push($allEmail , $csvEmail);
            }
            if ($extension == "xlsx") {
                $nameEmailArray[] = [];
                $contactEmailxlsx = array();
                $exelArrayLength = 0;
                $contactNameXlsx = array();
                $xlsx = SimpleXLSX::parse($request->file);
                $data = $xlsx->rows();
                foreach ($data as $key => $val) {
                    if ($exelArrayLength == 0) {
                        $exelArrayLength = count($val);
                    }
                    foreach ($val as $dataKey => $dataVal) {
                        if (filter_var($dataVal , FILTER_VALIDATE_EMAIL)) {
                            array_push($contactEmailxlsx , $dataVal);
                        } else {
                            array_push($contactNameXlsx , $dataVal);
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
                $emailGroupName = array_merge($emailGroupName , $nameEmailArray);
                $excelEmail = array_values($contactEmailxlsx);
                array_push($allEmail , $excelEmail);
            }
        }

        if (!$user->email) {
            return response()->json(['status' => 'error' , 'message' => "Please add your email in profile."] , 400);
        }

        $contactNewArray = [];

        if (empty($allEmail)) {
            return response()->json(['status' => 'error' , 'message' => "No Valid Input Email address not found."] , 400);
        }

        foreach ($allEmail as $childArray) {
            foreach ($childArray as $value) {
                $contactNewArray[] = $value;
            }
        }
        $contactNewArray = array_unique($contactNewArray);

        $content = buildDomDocument(offensiveMsgBlock($request->message));
        $setTimeInDelay = 0;
        if ($request->schedule == 2) {
            $setTimeInDelay = $request->shedule_date;
        } else {
            $setTimeInDelay = Carbon::now();
        }

        foreach ($contactNewArray as $key => $value) {
            $emailLog = new EmailLog();
            $emailLog->user_id = $user->id;
            $emailLog->from_name = $request->from_name ?? $emailMethod->driver_information->from->name;
            $emailLog->reply_to_email = $request->reply_to_email ?? $emailMethod->driver_information->from->address;
            $emailLog->sender_id = $emailMethod->id;
            $emailLog->to = $value;
            $emailLog->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
            $emailLog->subject = $request->subject;
            if (array_key_exists($value , $emailGroupName)) {
                $emailLog->message = str_replace('{{name}}' , $emailGroupName ? $emailGroupName[$value] : $value , $content);
            } else {
                $emailLog->message = str_replace('{{name}}' , $value , $content);
            }

            $general = GeneralSetting::first();

            //adding free watermark
            if (!$user->ableToSendWithoutWatermark() && $general->free_watermark) {
                $emailLog->message .= "<br><br><em>" . $general->free_watermark . "</em>";
            }

            $emailLog->status = $request->schedule == 2 ? 2 : 1;
            $emailLog->schedule_status = $request->schedule;
            $emailLog->save();
            if ($emailLog->status == 1) {
                if (count($contactNewArray) == 1 && $request->schedule == 1) {
                    ProcessEmail::dispatch($emailLog->id , $user->id , 'user');
                } else {
                    ProcessEmail::dispatch($emailLog->id , $user->id , 'user')->delay(Carbon::parse($setTimeInDelay));
                }
            }
        }
        return response()->json(['status' => 'success' , 'message' => "New Email request sent, please see in the Email history for final status"]);
    }

}