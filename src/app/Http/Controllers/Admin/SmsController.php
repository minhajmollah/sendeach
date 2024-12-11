<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMobileSMS;
use Illuminate\Http\Request;
use App\Models\SMSlog;
use App\Models\User;
use App\Models\CreditLog;
use App\Models\Group;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;
use App\Models\Template;
use App\Models\Contact;
use App\Jobs\ProcessSms;
use App\Models\UserFcmToken;
use Carbon\Carbon;
use Shuchkin\SimpleXLSX;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class SmsController extends Controller
{
    public function index()
    {
    	$title = "All SMS History";
    	$smslogs = SMSlog::orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function pending()
    {
    	$title = "Pending SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::PENDING)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function success()
    {
    	$title = "Delivered SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::SUCCESS)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function schedule()
    {
    	$title = "Schedule SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::SCHEDULE)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function failed()
    {
    	$title = "Failed SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::FAILED)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function processing()
    {
        $title = "Processing SMS History";
        $smslogs = SMSlog::where('status',SMSlog::PROCESSING)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
        return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function search(Request $request, $scope)
    {
        $title = "SMS History Search";
        $search = $request->search;
        $searchDate = $request->date;

        if ($search!="") {
            $smslogs = SMSlog::where(function ($q) use ($search) {
                $q->where('to','like', "%$search%")->orWhereHas('user', function ($user) use ($search) {
                    $user->where('email', 'like', "%$search%");
                });
            });
        }

        if ($searchDate!="") {
            $searchDate_array = explode('-',$request->date);
            $firstDate = $searchDate_array[0];
            $lastDate = null;
            if (count($searchDate_array)>1) {
                $lastDate = $searchDate_array[1];
            }
            $matchDate = "/\d{2}\/\d{2}\/\d{4}/";
            if ($firstDate && !preg_match($matchDate,$firstDate)) {
                $notify[] = ['error','Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($lastDate && !preg_match($matchDate,$lastDate)) {
                $notify[] = ['error','Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($firstDate) {
                $smslogs = SMSlog::whereDate('created_at',Carbon::parse($firstDate));
            }
            if ($lastDate){
                $smslogs = SMSlog::whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
            }
        }

        if ($search=="" && $searchDate=="") {
            $notify[] = ['error','Search data field empty'];
            return back()->withNotify($notify);
        }


        if($scope == 'pending') {
            $smslogs = $smslogs->where('status',SMSlog::PENDING);
        }elseif($scope == 'success'){
            $smslogs = $smslogs->where('status',SMSlog::SUCCESS);
        }elseif($scope == 'schedule'){
            $smslogs = $smslogs->where('status',SMSlog::SCHEDULE);
        }elseif($scope == 'failed'){
            $smslogs = $smslogs->where('status',SMSlog::FAILED);
        }
        $smslogs = $smslogs->orderBy('id','desc')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());

        return view('admin.sms.index', compact('title', 'smslogs', 'search', 'searchDate'));
    }

    public function smsStatusUpdate(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:s_m_slogs,id',
            'status' => 'required|in:1,3,4',
        ]);
        $general = GeneralSetting::first();
        $smsGateway = SmsGateway::where('id', $general->sms_gateway_id)->first();

        if(!$smsGateway){
            $notify[] = ['error', 'Invalid Sms Gateway'];
            return back()->withNotify($notify);
        }

        if($request->input('smslogid') !== null){
            $smsLogIds = array_filter(explode(",",$request->input('smslogid')));
            if(!empty($smsLogIds)){
                $this->smsLogStatusUpdate((int) $request->status, (array) $smsLogIds, $general, $smsGateway);
            }
        }

        if($request->has('id')){
            $this->smsLogStatusUpdate((int) $request->status, (array) $request->input('id'), $general, $smsGateway);
        }

        $notify[] = ['success', 'SMS status has been updated'];
        return back()->withNotify($notify);
    }

    private function smsLogStatusUpdate(int $status, array $smsLogIds, GeneralSetting $general, SmsGateway $smsGateway): void
    {
        foreach($smsLogIds as $smsLogId){
            $smslog = SMSlog::find($smsLogId);

            if(!$smslog){
                continue;
            }

            if($status == 1){
                if($general->sms_gateway == 1){
                    $smslog->api_gateway_id = $smsGateway->id;
                    $smslog->android_gateway_sim_id = null;
                }else{
                    $smslog->api_gateway_id = null;
                    $smslog->android_gateway_sim_id = null;
                }
            }
            $smslog->status = $status;
            $smslog->update();
        }

    }

    public function create()
    {
        $title = "Compose SMS";
        $templates = Template::whereNull('user_id')->get();
        $groups = Group::whereNull('user_id')->get();
        $gateways = SmsGateway::select('id', 'name')->where(['user_type' => 'admin', 'status' => 1])->get();
        $devices = UserFcmToken::select('id', 'device_id')->where('user_type', 'admin')->get();

        if (count($devices) + count($gateways) < 1) {
            $notify[] = ['error', 'Please connect at least one gateway or mobile device to send messages!'];
            return redirect()->route('admin.gateway.sms.index')->withNotify($notify);
        }

        return view('admin.sms.create', compact('title', 'groups', 'templates', 'gateways', 'devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'gateway_or_device_type' => ['required', 'string', Rule::in(['mobile', 'gateway'])],
            'mobile_device_id' => [
                Rule::requiredIf($request->gateway_or_device_type == 'mobile'),
                'nullable',
                Rule::exists((new UserFcmToken())->getTable(), 'id')
            ],
            'gateway_id' => [
                Rule::requiredIf($request->gateway_or_device_type == 'gateway'),
                'nullable',
                Rule::exists((new SmsGateway())->getTable(), 'id')
            ],
            'smsType' => 'required|in:plain,unicode',
            'schedule' => 'required|in:1,2',
            'shedule_date' => 'required_if:schedule,2',
            'group_id' => 'nullable|array|min:1',
            'group_id.*' => 'nullable|exists:groups,id',
        ]);

        $mobile_device = null;
        if ($request->mobile_device_id) {
            $mobile_device = UserFcmToken::where('user_type', 'admin')->where('id', $request->mobile_device_id)->first();
            if (!$mobile_device) {
                $notify[] = ['error', 'Invalid mobile device selected!'];
                return back()->withNotify($notify);
            }
        }

        $gateway = null;
        if ($request->gateway_id) {
            $gateway = SmsGateway::where(['user_type' => 'admin', 'status' => 1])->where('id', $request->gateway_id)->first();
            if (!$gateway) {
                $notify[] = ['error', 'Invalid gateway selected!'];
                return back()->withNotify($notify);
            }
        }

        if(!$request->number && !$request->group_id && !$request->file){
            $notify[] = ['error', 'Invalid number collect format'];
            return back()->withNotify($notify);
        }
        $numberGroupName  = [];
        $allContactNumber = [];
        if($request->number){
            $contactNumber = preg_replace('/[ ,]+/', ',', trim($request->number));
            $recipientNumber  = explode(",",$contactNumber);
            array_push($allContactNumber, $recipientNumber);
        }
        if($request->group_id){
            $groupNumber = Contact::active()->whereNull('user_id')->whereIn('group_id', $request->group_id)->pluck('contact_no')->toArray();
            $numberGroupName = Contact::active()->whereNull('user_id')->whereIn('group_id', $request->group_id)->pluck('name','contact_no')->toArray();
            array_push($allContactNumber, $groupNumber);
        }
        if($request->file){
            $extension = strtolower($request->file->getClientOriginalExtension());
            if(!in_array($extension, ['csv','txt','xlsx'])){
                $notify[] = ['error', 'Invalid file extension'];
                return back()->withNotify($notify);
            }
            if($extension == "txt"){
                $contactNumberTxt = file($request->file);
                unset($contactNumberTxt[0]);
                $txtNumber = array_values($contactNumberTxt);
                $txtNumber = preg_replace('/[^a-zA-Z0-9_ -]/s','',$txtNumber);
                array_push($allContactNumber, $txtNumber);
            }
            if($extension == "csv"){
                $contactNumberCsv = array();
                $contactNameCsv = array();
                $nameNumberArray[] = [];
                $csvArrayLength = 0;
                if(($handle = fopen($request->file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE){
                        if($csvArrayLength == 0){
                           $csvArrayLength = count($data);
                        }
                        foreach($data as $dataVal){
                            if(filter_var($dataVal, FILTER_SANITIZE_NUMBER_INT)){
                                array_push($contactNumberCsv, $dataVal);
                            }
                            else{
                                array_push($contactNameCsv, $dataVal);
                            }
                        }
                    }
                }
                for ($i = 0; $i < $csvArrayLength; $i++){
                    unset($contactNameCsv[$i]);
                }
                if((count($contactNameCsv)) == 0){
                    $contactNameCsv = $contactNumberCsv;
                }
                $nameNumberArray = array_combine($contactNumberCsv, $contactNameCsv);
                $numberGroupName = $numberGroupName +  $nameNumberArray;
                $csvNumber = array_values($contactNumberCsv);
                array_push($allContactNumber, $csvNumber);
            }
            if($extension == "xlsx"){
                $nameNumberArray[] = [];
                $contactNameXlsx = array();
                $exelArrayLength = 0;
                $contactNumberxlsx = array();
                $xlsx = SimpleXLSX::parse($request->file);
                $data = $xlsx->rows();
                foreach($data as $key=>$val){
                    if($exelArrayLength == 0){
                        $exelArrayLength = count($val);
                    }
                    foreach($val as $dataKey=>$dataVal){
                        if(filter_var($dataVal, FILTER_SANITIZE_NUMBER_INT)){
                            array_push($contactNumberxlsx, $dataVal);
                        }
                        else{
                            array_push($contactNameXlsx, (string)$dataVal);
                        }
                    }
                }
                for ($i = 0; $i < $exelArrayLength; $i++){
                    unset($contactNameXlsx[$i]);
                }
                if((count($contactNameXlsx)) == 0){
                    $contactNameXlsx = $contactNumberxlsx;
                }
                $nameNumberArray = array_combine($contactNumberxlsx, $contactNameXlsx);
                $numberGroupName = $numberGroupName +  $nameNumberArray;
                $excelNumber = array_values($contactNumberxlsx);
                array_push($allContactNumber, $excelNumber);
            }
        }

        $general = GeneralSetting::first();
        $wordLength = $request->smsType == "plain" ? $general->sms_word_text_count : $general->sms_word_unicode_count;

        $contactNewArray = [];
        foreach($allContactNumber as $childArray){
            foreach($childArray as $value){
                $contactNewArray[] = $value;
            }
        }
        $contactNewArray = array_unique($contactNewArray);
        $messages = str_split($request->message,$wordLength);
        $totalMessage = count($messages);

        $totalNumber = count($contactNewArray);
        $totalCredit = $totalNumber * $totalMessage;

        $setTimeInDelay = 0;
        if($request->schedule == 2){
            $setTimeInDelay = $request->shedule_date;
        }else{
            $setTimeInDelay = Carbon::now();
        }

        if ($mobile_device) {
            $log = new SMSlog();
            $log->sms_type = $request->smsType == "plain" ? 1 : 2;
            $log->to = implode(', ', array_values($contactNewArray));
            $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
            $finalContent = $request->message;
            $log->message = $finalContent;
            $log->status = $request->schedule == 2 ? 2 : 1;
            $log->schedule_status = $request->schedule;
            $log->save();

            if ($log->status == 1) {
                ProcessMobileSMS::dispatchNow($mobile_device, $contactNewArray, $request->smsType, $finalContent, $log->id);
            } else {
                ProcessMobileSMS::dispatch($mobile_device, $contactNewArray, $request->smsType, $finalContent, $log->id)->delay(Carbon::parse($setTimeInDelay));
            }
        } else if ($gateway) {
            foreach ($contactNewArray as $key => $value) {

                $log = new SMSlog();
                if($general->sms_gateway == 1){
                    $log->api_gateway_id = $gateway->id;
                }
                $log->to = $value;
                $log->sms_type = $request->smsType == "plain" ? 1 : 2;
                $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;

                if(array_key_exists($value,$numberGroupName)){
                    $finalContent = str_replace('{{name}}', $numberGroupName ? $numberGroupName[$value]:$value, offensiveMsgBlock($request->message));
                }
                else{
                    $finalContent = str_replace('{{name}}',$value, offensiveMsgBlock($request->message));
                }
                $log->message = $finalContent;
                $log->status = $request->schedule == 2 ? 2 : 1;
                $log->schedule_status = 1;
                $log->save();

                if($general->sms_gateway == 1){
                    if($log->status == 1){
                        if(count($contactNewArray) == 1 && $request->schedule==1){
                            ProcessSms::dispatchNow($value, $request->smsType, $finalContent, $gateway->credential, $gateway->gateway_code, $log->id);
                        }else{
                            ProcessSms::dispatch($value, $request->smsType, $finalContent, $gateway->credential, $gateway->gateway_code, $log->id)->delay(Carbon::parse($setTimeInDelay));
                        }
                    }
                }
            }
        }
        $notify[] = ['success', 'New SMS request sent, please see in the SMS history for final status'];
        return back()->withNotify($notify);
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required'
        ]);
        try {
            $smsLog = SMSlog::query()->whereIn('id', explode(',', $request->id));
            $smsLog->delete();
            $notify[] = ['success', "Successfully SMS log deleted"];
        } catch (\Exception $e) {
            $notify[] = ['error', "Error occurred in SMS delete time. Error is " . $e->getMessage()];
        }
        return back()->withNotify($notify);
    }
}
