<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SMSMessageSendRequest;
use App\Jobs\ProcessBulkSMS;
use App\Jobs\ProcessBulkSMSViaAndroid;
use App\Jobs\ProcessMobileSMS;
use App\Models\EmailLog;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use App\Models\SMSlog;
use App\Models\GeneralSetting;
use App\Models\CreditLog;
use App\Models\SmsGateway;
use Carbon\Carbon;
use Shuchkin\SimpleXLSX;
use App\Jobs\ProcessSms;
use App\Models\UserFcmToken;
use Illuminate\Validation\Rule;

class ManageSMSController extends Controller
{
    public function create()
    {
        $title = "Compose SMS";
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $groups = $user->group()->get();
        $templates = $user->template()->get();
        $gateways = SmsGateway::select('id', 'name')->where(['user_id' => $user->id, 'user_type' => 'user', 'status' => 1])->get();
        $devices = UserFcmToken::select('id', 'device_id')->where('user_id', $user->id)->get();

        if (count($devices) + count($gateways) < 1) {
            $notify[] = ['error', 'Please connect at least one gateway or mobile device to send messages!'];
            return redirect()->route('user.gateway.sms.index')->withNotify($notify);
        }

        return view('user.sms.create', compact('title', 'groups', 'templates', 'gateways', 'devices'));
    }

    public function index(Request $request)
    {
        $scope = Arr::last(explode('/', $request->path()));

        $title = 'Email Messages | ' . ucfirst($scope);

        $scope = SMSlog::STATUS_SCOPE[$scope] ?? null;

        $search = $request->search;
        $searchDate = $request->date;

        $firstDate = null;
        $lastDate = null;

        if ($searchDate) {
            $searchDate_array = explode('-', $request->date);
            $firstDate = $searchDate_array[0];

            if (count($searchDate_array) > 1) {
                $lastDate = $searchDate_array[1];
            }

            $matchDate = "/\d{2}\/\d{2}\/\d{4}/";
            if ($firstDate && !preg_match($matchDate, $firstDate)) {
                $notify[] = ['error', 'Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($lastDate && !preg_match($matchDate, $lastDate)) {
                $notify[] = ['error', 'Invalid order search date format'];
                return back()->withNotify($notify);
            }
        }

        $batchId = request('batch_id');


        $logs = SMSlog::query()
            ->where('user_id', \auth()->id())
            ->with('smsGateway', 'androidGateway')
            ->orderBy('id', 'desc')
            ->search(scope: $scope, search: $search, startDate: $firstDate, endDate: $lastDate)
            ->when($batchId, fn($q) => $q->where('batch_id', $batchId))
            ->paginate(paginateNumber());

        return view('user.sms.index', compact('title', 'logs', 'search', 'searchDate'));
    }

    public function campaign()
    {
        $title = 'SMS Campaign';

        $reports = SMSlog::withTrashed()
            ->where('user_id', auth()->id())
            ->selectRaw('batch_id, COUNT(*) as total,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS pending,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS processing,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS delivered,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS failed,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS scheduled,
             MIN(initiated_time) as started_at,
             MAX(updated_at) as completed_at,
             message
             ', [SMSlog::PENDING, SMSlog::PROCESSING, SMSlog::SUCCESS, SMSlog::FAILED, SMSlog::SCHEDULE])
            ->groupBy('batch_id')
            ->orderByDesc('started_at')
            ->paginate(paginateNumber());

        $routePrefix = auth('web')->id() ? 'user' : 'admin';
        $routeStr = $routePrefix . '.sms.';
        $deleteRoute = route($routePrefix . '.sms.delete.campaign');

        return view('campaign', compact('title', 'reports', 'routePrefix', 'routeStr', 'deleteRoute'));
    }


    public function store(SMSMessageSendRequest $request)
    {
        $request->handle();
        $request->getAttachment();
        $user = Auth::user();

        $mobileDevices = null;
        if ($request->gateway_or_device_type == 'mobile') {
            $mobileDevices = UserFcmToken::where('user_id', $user->id)
                ->when($request->mobile_device_id, fn($q) => $q->where('id', $request->mobile_device_id))
                ->get();

            if ($mobileDevices->isEmpty()) {
                $notify[] = ['error', 'Invalid mobile device selected!'];
                return back()->withNotify($notify);
            }
        }

        $gateway = null;
        if ($request->gateway_id) {
            $gateway = SmsGateway::where(['user_id' => $user->id, 'user_type' => 'user', 'status' => 1])->where('id', $request->gateway_id)->first();
            if (!$gateway) {
                $notify[] = ['error', 'Invalid gateway selected!'];
                return back()->withNotify($notify);
            }
        }

        $data['schedule_date'] = $request->schedule_date ?: now();
        $data['to'] = $request->to;
        $data['numberGroupName'] = $request->numberGroupName;
        $data['groupIDContact'] = $request->groupIDContact;
        $data['schedule'] = $request->schedule;
        $data['audio'] = $request->audio;
        $data['image'] = $request->image;
        $data['document'] = $request->document;
        $data['video'] = $request->video;
        $data['smsType'] = $request->smsType == "plain" ? 1 : 2;

        $data['message'] = [$request->message];

        if ($spinMessage = $request->spinMessage) {
            $data['message'] = array_merge($data['message'], $spinMessage);
        }

        if ($mobileDevices) {
            dispatch(new ProcessBulkSMSViaAndroid($user->id, $mobileDevices, $data));
        } elseif ($gateway) {
            dispatch(new ProcessBulkSMS($user->id, $gateway, $data));
        } else {
            $notify[] = ['error', 'Invalid mobile device selected!'];
            return back()->withNotify($notify);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => "success",
                'message' => "New SMS request sent, please see in the SMS history for final status",
            ]);
        }

        $notify[] = ['success', 'New SMS request sent, please see in the SMS history for final status'];
        return back()->withNotify($notify);
    }

    public function deleteCampaign()
    {
        try {
            if (SMSlog::query()
                ->where('user_id', \auth()->id())
                ->when(\request('id'), fn($q) => $q->whereIn('batch_id', explode(',', \request('id'))))
                ->unless(\request('id'), fn($q) => $q->whereNull('batch_id'))
                ->forceDelete()) {
                $notify[] = ['success', "Successfully Deleted SMS Campaign"];
            } else {
                $notify[] = ['error', "Error occurred in deleting logs"];
            }

        } catch (\Exception $e) {
            $notify[] = ['error', "Error occurred in deleting logs"];
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
        }

        return back()->withNotify($notify);
    }

    public function deleteLogs(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ], $request->all());

        try {

            $status = SMSlog::STATUS_SCOPE[$request->id] ?? null;

            if ($request->id == 'all') $status = 'all';

            if (SMSlog::query()
                ->where('user_id', \auth()->id())
                ->when(!$status, fn($q) => $q->whereIn('id', explode(',', $request->id)))
                ->when(($request->id != 'all') && $status, fn($q) => $q->where('status', $status))
                ->delete()) {
                $notify[] = ['success', "Logs deleted successfully"];
            } else {
                $notify[] = ['error', "Error occurred in deleting logs"];
            }

        } catch (\Exception $e) {
            $notify[] = ['error', "Error occurred in deleting logs"];
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
        }

        return back()->withNotify($notify);
    }
}
