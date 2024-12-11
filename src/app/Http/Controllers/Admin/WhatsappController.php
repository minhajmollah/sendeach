<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageSendRequest;
use App\Jobs\ProcessWhatsapp;
use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\Group;
use App\Models\Template;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Rules\MessageFileValidationRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shuchkin\SimpleXLSX;


class WhatsappController extends Controller
{
    public function index()
    {
        $title = "All Whatsapp Message History";
        $whatsappLogs = WhatsappLog::orderBy('id' , 'DESC')->with(['user' , 'whatsappGateway', 'whatsappPCGateway'])
            ->whereIn('gateway' , [WhatsappLog::GATEWAY_DESKTOP , WhatsappLog::GATEWAY_WEB])
            ->paginate(paginateNumber());

        return view('admin.whatsapp_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function pending()
    {
        $title = "Pending Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::PENDING)
            ->whereIn('gateway' , [WhatsappLog::GATEWAY_DESKTOP , WhatsappLog::GATEWAY_WEB])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsappGateway')->paginate(paginateNumber());
        return view('admin.whatsapp_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function success()
    {
        $title = "Delivered Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::SUCCESS)
            ->whereIn('gateway' , [WhatsappLog::GATEWAY_DESKTOP , WhatsappLog::GATEWAY_WEB])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsappGateway')->paginate(paginateNumber());
        return view('admin.whatsapp_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function schedule()
    {
        $title = "Schedule Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::SCHEDULE)
            ->whereIn('gateway' , [WhatsappLog::GATEWAY_DESKTOP , WhatsappLog::GATEWAY_WEB])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsappGateway')->paginate(paginateNumber());
        return view('admin.whatsapp_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function failed()
    {
        $title = "Failed Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::FAILED)
            ->whereIn('gateway' , [WhatsappLog::GATEWAY_DESKTOP , WhatsappLog::GATEWAY_WEB])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsappGateway')->paginate(paginateNumber());
        return view('admin.whatsapp_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function processing()
    {
        $title = "Processing Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::PROCESSING)
            ->whereIn('gateway' , [WhatsappLog::GATEWAY_DESKTOP , WhatsappLog::GATEWAY_WEB])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsappGateway')->paginate(paginateNumber());
        return view('admin.whatsapp_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function search(Request $request , $scope)
    {
        $title = "WhatsApp Message History Search";
        $search = $request->search;
        $searchDate = $request->date;

        $firstDate = null;
        $lastDate = null;

        if ($searchDate) {
            $searchDate_array = explode('-' , $request->date);
            $firstDate = $searchDate_array[0];

            if (count($searchDate_array) > 1) {
                $lastDate = $searchDate_array[1];
            }

            $matchDate = "/\d{2}\/\d{2}\/\d{4}/";
            if ($firstDate && !preg_match($matchDate , $firstDate)) {
                $notify[] = ['error' , 'Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($lastDate && !preg_match($matchDate , $lastDate)) {
                $notify[] = ['error' , 'Invalid order search date format'];
                return back()->withNotify($notify);
            }
        }


        $whatsappLogs = WhatsappLog::search($scope, $search, startDate:$firstDate, endDate: $lastDate)
            ->orderBy('id' , 'desc')->with('user' , 'whatsappGateway')->paginate(paginateNumber());

        return view('admin.whatsapp_messaging.index' , compact('title' , 'whatsappLogs' , 'search' , 'searchDate'));
    }

    public function smsStatusUpdate(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:whatsapp_logs,id' ,
            'status' => 'required|in:1,3,4' ,
        ]);

        if ($request->input('smslogid') !== null) {
            $smsLogIds = array_filter(explode("," , $request->input('smslogid')));
            if (!empty($smsLogIds)) {
                $this->whatsappLogStatusUpdate((int)$request->status , (array)$smsLogIds);
            }
        }

        if ($request->has('id')) {
            $this->whatsappLogStatusUpdate((int)$request->status , (array)$request->input('id'));
        }

        $notify[] = ['success' , 'WhatsApp status has been updated'];
        return back()->withNotify($notify);
    }

    private function whatsappLogStatusUpdate(int $status , array $smsLogIds): void
    {
        foreach ($smsLogIds as $smsLogId) {
            $smslog = WhatsappLog::find($smsLogId);

            if (!$smslog) {
                continue;
            }

            $smslog->status = $status;
            $smslog->update();
        }
    }

    public function create()
    {
        $title = "Compose WhatsApp Message";
        $templates = Template::whereNull('user_id')->get();
        $groups = Group::whereNull('user_id')->get();
        $devices = WhatsappDevice::where('user_type' , 'admin')->where('status' , 'connected')->orderBy('name')->get();
        if (count($devices) < 1) {
            $notify[] = ['error' , 'Please connect at least one device to send messages!'];
            return redirect()->route('admin.gateway.whatsapp.create')->withNotify($notify);
        }
        return view('admin.whatsapp_messaging.create' , compact('title' , 'groups' , 'templates' , 'devices'));
    }

    public function send(MessageSendRequest $request)
    {
        $request->handle();

        $whatsappGateway = WhatsappDevice::query()->where('user_type' , 'admin')
            ->where('status' , 'connected');

        if ($request->whatsapp_device) {
            $whatsappGateway = $whatsappGateway->where('id' , $request->whatsapp_device);
        }

        $whatsappGateway = $whatsappGateway->pluck('delay_time' , 'id')->toArray();

        if (count($whatsappGateway) < 1) {
            $notify[] = ['error' , 'No available WhatsApp Gateway'];
            return back()->withInput()->withNotify($notify);
        }

        if ($request->schedule == 2) {
            $setTimeInDelay = Carbon::parse($request->schedule_date);
        } else {
            $setTimeInDelay = Carbon::now();
        }

        $contacts = $request->to;

        $setWhatsAppGateway = $whatsappGateway;
        $i = 1;
        $addSecond = 10;
        $gateWayid = null;

        $postData = $request->getAttachment();

        if (count($contacts) == 1) $addSecond = 1;

        foreach ($contacts as $to) {
            foreach ($setWhatsAppGateway as $key => $appGateway) {
                $addSecond = $appGateway + 5;
                $gateWayid = $key;
                unset($setWhatsAppGateway[$key]);
                if (empty($setWhatsAppGateway)) {
                    $setWhatsAppGateway = $whatsappGateway;
                    $i++;
                }
                break;
            }

            $initiatedTime = $setTimeInDelay->addSeconds($addSecond);

            if (isset($request->numberGroupName[$to])) {
                $finalContent = str_replace('{{name}}' , $request->numberGroupName[$to] , offensiveMsgBlock($request->message));
            } else {
                $finalContent = str_replace('{{name}}' , $to , offensiveMsgBlock($request->message));
            }

            if (isset($request->groupIDContact[$to])) {
                $finalContent = Contact::replaceWithUnsubscribeLink($finalContent , $request->groupIDContact[$to]);
            }

            $log = WhatsappLog::query()->create([
                'whatsapp_id' => $gateWayid ,
                'user_id' => null ,
                'to' => $to ,
                'message' => $finalContent ,
                'gateway' => WhatsappLog::GATEWAY_WEB ,
                'initiated_time' => $initiatedTime ,
                'status' => $request->schedule == 2 ? 2 : 1 ,
                'schedule_status' => $request->schedule ,
                'document' => $request->document ,
                'audio' => $request->audio ,
                'image' => $request->image ,
                'video' => $request->video ,
                'created_at' => now() ,
                'updated_at' => now() ,
            ]);


            ProcessWhatsapp::dispatch($finalContent , $to , $log->id , $postData)->delay($initiatedTime);
        }

        $notify[] = ['success' , 'New WhatsApp Message request sent, please see in the WhatsApp Log history for final status'];
        return back()->withNotify($notify);
    }


    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ], $request->all() );

        try {
            $whatsappLogs = WhatsappLog::query()->whereIn('id' , explode(',' , $request->id));
            $whatsappLogs->delete();
            $notify[] = ['success' , "Successfully SMS log deleted"];
        } catch (\Exception $e) {
            $notify[] = ['error' , "Error occurred in deleting logs. Error is " . $e->getMessage()];
        }

        return back()->withNotify($notify);
    }
}
