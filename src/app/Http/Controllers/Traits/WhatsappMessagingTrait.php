<?php

namespace App\Http\Controllers\Traits;

use App\Jobs\ProcessWhatsapp;
use App\Jobs\ProcessWhatsappMessage;
use App\Models\Contact;
use App\Models\CreditLog;
use App\Models\GeneralSetting;
use App\Models\UserWindowsToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSX;

trait WhatsappMessagingTrait
{
    public function sendBusinessWhatsappMessage(Request $whatsappMessageSendRequest , $user)
    {
        if ($whatsappMessageSendRequest->schedule == 2) {
            $setTimeInDelay = Carbon::parse($whatsappMessageSendRequest->shedule_date);
            $status = WhatsappLog::SCHEDULE;
        } else {
            $status = WhatsappLog::PENDING;
            $setTimeInDelay = Carbon::now();
        }

        $phone = WhatsappPhoneNumber::query()->where('whatsapp_phone_number_id' , $whatsappMessageSendRequest->whatsapp_phone_number_id)->firstOrFail();

        $logIds = [];

        foreach ($whatsappMessageSendRequest->to as $to) {

            if ($user?->id && $whatsappMessageSendRequest->template->is_public &&
                $res = $this->updateCredit($to , $whatsappMessageSendRequest->template , $user)) {
                return $res;
            }

            $log = WhatsappLog::startBusinessLog(
                $to , $whatsappMessageSendRequest->template ,
                $whatsappMessageSendRequest->template_var_HEADER , $whatsappMessageSendRequest->template_var_BODY ,
                $phone ,
                $user?->id ,
                $whatsappMessageSendRequest->whatsappBusiness
            );

            $logIds[] = $log->id;

            $log->status = $status;
            $log->initiated_time = $setTimeInDelay;
            $log->save();

            ProcessWhatsappMessage::dispatch(
                $to ,
                $whatsappMessageSendRequest->whatsapp_phone_number_id ,
                $log->id ,
                $whatsappMessageSendRequest->template_id ,
                $whatsappMessageSendRequest->template_var_BODY ,
                $whatsappMessageSendRequest->template_var_HEADER ,
                $user?->id
            )->delay($setTimeInDelay);
        }


        return $logIds;
    }

    public function sendWhatsappWebMessage(Request $request)
    {
        if (!$request->number && !$request->group_id && !$request->file) {
            return $this->sendJsonError('Invalid number collect format');
        }

        $allContactNumber = [];
        $numberGroupName = [];

        if ($request->number) {
            $contactNumber = preg_replace('/[ ,]+/' , ',' , trim($request->number));
            $recipientNumber = explode("," , $contactNumber);
            array_push($allContactNumber , $recipientNumber);
        }
        if ($request->group_id) {
            $groupNumber = Contact::whereNull('user_id')->whereIn('group_id' , $request->group_id)->pluck('contact_no')->toArray();
            $numberGroupName = Contact::whereNull('user_id')->whereIn('group_id' , $request->group_id)->pluck('name' , 'contact_no')->toArray();
            array_push($allContactNumber , $groupNumber);
        }

        if ($request->file) {
            $extension = strtolower($request->file->getClientOriginalExtension());
            if (!in_array($extension , ['csv' , 'txt' , 'xlsx'])) {
                return $this->sendJsonError('Invalid file extension');
            }
            if ($extension == "txt") {
                $contactNumberTxt = file($request->file);
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
                if (($handle = fopen($request->file , "r")) !== FALSE) {
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
                $numberGroupName = $numberGroupName + $nameNumberArray;
                $csvNumber = array_values($contactNumberCsv);
                array_push($allContactNumber , $csvNumber);
            }
            if ($extension == "xlsx") {
                $nameNumberArray[] = [];
                $contactNameXlsx = array();
                $exelArrayLength = 0;
                $contactNumberxlsx = array();
                $xlsx = SimpleXLSX::parse($request->file);
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
                $numberGroupName = $numberGroupName + $nameNumberArray;
                $excelNumber = array_values($contactNumberxlsx);
                array_push($allContactNumber , $excelNumber);
            }
        }

        $contactNewArray = [];
        foreach ($allContactNumber as $childArray) {
            foreach ($childArray as $value) {
                $contactNewArray[] = $value;
            }
        }
        $contactNewArray = array_unique($contactNewArray);
        $general = GeneralSetting::first();
        $wordLenght = $general->whatsapp_word_count;

        $messages = str_split($request->message , $wordLenght);
        $totalMessage = count($messages);
        $totalNumber = count($contactNewArray);
        $totalCredit = $totalNumber * $totalMessage;

        $whatsappGateway = WhatsappDevice::where('user_type' , 'admin')->where('status' , 'connected');
        if ($request->whatsapp_device) {
            $whatsappGateway = $whatsappGateway->where('id' , $request->whatsapp_device);
        }
        $whatsappGateway = $whatsappGateway->pluck('delay_time' , 'id')->toArray();

        if (count($whatsappGateway) < 1) {
            return $this->sendJsonError('Not available WhatsApp Gateway');
        }
        $postData = [];
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_document'];
            if (!file_exists($path)) {
                mkdir($path , 0755 , true);
            }
            try {
                move_uploaded_file($file->getRealPath() , $path . '/' . $fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'pdf';
            $postData['url_file'] = $path . '/' . $fileName;
            $postData['name'] = $fileName;
        }
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_audio'];
            if (!file_exists($path)) {
                mkdir($path , 0755 , true);
            }
            try {
                move_uploaded_file($file->getRealPath() , $path . '/' . $fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'Audio';
            $postData['url_file'] = $path . '/' . $fileName;
            $postData['name'] = $fileName;
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_image'];
            if (!file_exists($path)) {
                mkdir($path , 0755 , true);
            }
            try {
                move_uploaded_file($file->getRealPath() , $path . '/' . $fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'Image';
            $postData['url_file'] = $path . '/' . $fileName;
            $postData['name'] = $fileName;
        }
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $fileName = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_video'];
            if (!file_exists($path)) {
                mkdir($path , 0755 , true);
            }
            try {
                move_uploaded_file($file->getRealPath() , $path . '/' . $fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'Video';
            $postData['url_file'] = $path . '/' . $fileName;
            $postData['name'] = $fileName;
        }
        $delayTimeCount = [];
        $setTimeInDelay = 0;
        if ($request->schedule == 2) {
            $setTimeInDelay = $request->shedule_date;
        } else {
            $setTimeInDelay = Carbon::now();
        }

        $setWhatsAppGateway = $whatsappGateway;
        $i = 1;
        $addSecond = 10;
        $gatewayId = null;
        $logIds = [];
        foreach (array_filter($contactNewArray) as $key => $value) {
            foreach ($setWhatsAppGateway as $key => $appGateway) {
                $addSecond = $appGateway * $i;
                $gatewayId = $key;
                unset($setWhatsAppGateway[$key]);
                if (empty($setWhatsAppGateway)) {
                    $setWhatsAppGateway = $whatsappGateway;
                    $i++;
                }
                break;
            }

            $log = new WhatsappLog();
            if (count($whatsappGateway) > 0) {
                $log->whatsapp_id = $gatewayId;
            }
            $log->to = $value;
            $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
            if (array_key_exists($value , $numberGroupName)) {
                $finalContent = str_replace('{{name}}' , $numberGroupName ? $numberGroupName[$value] : $value , offensiveMsgBlock($request->message));
            } else {
                $finalContent = str_replace('{{name}}' , $value , offensiveMsgBlock($request->message));
            }
            $log->message = $finalContent;
            $log->status = $request->schedule == 2 ? 2 : 1;

            if ($request->hasFile('document')) {
                $log->document = $fileName;
            }
            if ($request->hasFile('audio')) {
                $log->audio = $fileName;
            }
            if ($request->hasFile('image')) {
                $log->image = $fileName;
            }
            if ($request->hasFile('video')) {
                $log->video = $fileName;
            }
            $log->schedule_status = $request->schedule;
            $log->save();
            $logIds[] = $log->id;

            if (count($contactNewArray) == 1 && $request->schedule == 1) {
                dispatch_now(new ProcessWhatsapp($finalContent , $value , $log->id , $postData));
            } else {
                dispatch(new ProcessWhatsapp($finalContent , $value , $log->id , $postData))->delay(Carbon::parse($setTimeInDelay)->addSeconds($addSecond));
            }
        }

        return $logIds;
    }

    private function updateCredit(string $to , WhatsappTemplate $template , $user)
    {
        $credits = $template->totalCredit();

        if ($credits > $user->credit) {

            $user->default_whatsapp_gateway = WhatsappLog::GATEWAY_WEB;
            $user->save();

            $notify[] = ['error' , 'You do not have a sufficient credit for send message'];
            if (request()->expectsJson() || request()->routeIs('api.*')) {
                return response()->json([
                    'status' => "error" ,
                    "message" => "You do not have a sufficient credit for send message. Default gateway switched to WEB." ,
                ]);
            }

            return back()->withNotify($notify);
        }

        $user->credit -= $credits;
        $user->save();

        $creditInfo = new CreditLog();
        $creditInfo->user_id = $user->id;
        $creditInfo->credit_type = "-";
        $creditInfo->credit = $credits;
        $creditInfo->trx_number = trxNumber();
        $creditInfo->post_credit = $user->credit;
        $creditInfo->details = $credits . " credits were cut for " . $to . " number send message";
        $creditInfo->save();

        return null;
    }

    public function sendJsonError($message , $statusCode = 400 , $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'error' ,
            'message' => $message ,
            'data' => $data
        ] , $statusCode);
    }

    public function sendOTPViaBusinessGateway(WhatsappPhoneNumber $phone , $to , WhatsappTemplate $template , $otp)
    {

        if ($template->user_id != auth()->id() &&
            $res = $this->updateCredit($to , $template , auth()->user())) {
            return $res;
        }

        // Generate whatsapp log
        $log = WhatsappLog::startBusinessLog($to , $template , [] , [$otp] , $phone , auth()->id() , $phone->whatsapp_account);
        $log->initiated_time = now();

        // Dispatch whatsapp message job
        ProcessWhatsappMessage::dispatch($to , $phone->whatsapp_phone_number_id , $log->id , $template->whatsapp_template_id , [$otp])
            ->delay(now()->addSeconds(1));

        return $log;
    }

    public function sendWebMessage($recipients , $whatsapp_device , $user , $message)
    {
        if(count($recipients) > 50)
        {
            return response()->json([
                'status' => 'error' ,
                'data' => ['message' => 'A maximum of 50 WhatsApp messages can be transmitted per sending session via the web gateway.' ,] ,
            ] , 400);
        }

        $whatsappGateway = WhatsappDevice::where('user_type' , 'user')->where('user_id' , $user->id)->where('status' , 'connected');

        if ($whatsapp_device) {
            $whatsappGateway = $whatsappGateway->where('id' , $whatsapp_device)->first();
        } else {
            $whatsappGateway = $whatsappGateway->inRandomOrder()->first();
        }

        if (!$whatsappGateway) {
            return response()->json([
                'status' => 'error' ,
                'data' => ['message' => 'No Whatsapp Gateway Added' ,] ,
            ] , 400);
        }

        $logs = [];

        foreach ($recipients as $to) {
            $log = WhatsappLog::startLog($whatsappGateway , $user , $to , $message , WhatsappLog::GATEWAY_WEB);

            ProcessWhatsapp::dispatch($message , $to , $log->id , [])->delay(now()->addSeconds(1));

            $logs[] = $log;
        }

        return $logs;
    }
}
