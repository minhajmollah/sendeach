<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailSendRequest;
use App\Jobs\ProcessBulkEmail;
use App\Models\EmailLog;
use App\Models\EmailTemplates;
use App\Models\MailConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ManageEmailController extends Controller
{
    public function create()
    {
        $title = "Compose Email";
        $user = Auth::user();
        $emailContacts = $user->emailContact()->get(['email']);
        $emailGroups = $user->emailGroup()->get();
        $mailGateways = MailConfiguration::
        where(['user_id' => auth()->user()->id, 'user_type' => 'user', 'status' => 1])
        ->orWhere(['user_type'=>'default'])
        ->orderBy('name')->get();
        if (count($mailGateways) < 1) {
            $notify[] = ['error', "Please activate at least one gateway to send emails!"];
            return redirect()->route('user.mail.configuration')->withNotify($notify);
        }
        $templates = EmailTemplates::query()->where('status', 4)->get();

        return view('user.email.create', compact('title', 'emailGroups', 'emailContacts', 'mailGateways', 'templates'));
    }

    public function index(Request $request)
    {
        $scope = Arr::last(explode('/', $request->path()));

        $title = 'Email Messages | ' . ucfirst($scope);

        $scope = EmailLog::STATUS_SCOPE[$scope] ?? null;

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


        $logs = EmailLog::query()
            ->where('user_id', \auth()->id())
            ->with('sender')
            ->orderBy('id', 'desc')
            ->search(scope: $scope, search: $search, startDate: $firstDate, endDate: $lastDate)
            ->when($batchId, fn($q) => $q->where('batch_id', $batchId))
            ->paginate(paginateNumber());

        return view('user.email.index', compact('title', 'logs', 'search', 'searchDate'));
    }

    public function campaign()
    {
        $title = 'Email Campaign';

        $reports = EmailLog::withTrashed()
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
             ', [EmailLog::PENDING, EmailLog::PROCESSING, EmailLog::SUCCESS, EmailLog::FAILED, EmailLog::SCHEDULE])
            ->groupBy('batch_id')
            ->orderByDesc('started_at')
            ->paginate(paginateNumber());

        $routePrefix = auth('web')->id() ? 'user' : 'admin';
        $routeStr = $routePrefix . '.manage.email.';
        $deleteRoute = route($routePrefix . '.manage.email.delete.campaign');

        return view('campaign', compact('title', 'reports', 'routePrefix', 'routeStr', 'deleteRoute'));
    }

    public function store(MailSendRequest $request)
    {
    $user = \auth()->user();

    if ($request->mail_gateway) {

        $emailGateway = MailConfiguration::where(['id' => $request->mail_gateway, 'user_id' => auth()->user()->id,'user_type' => 'user', 'status' => 1])
            ->orWhere('user_type', 'default')
            ->first();
    } else {
        $emailGateway = MailConfiguration::where(['user_id' => auth()->user()->id, 'user_type' => 'user', 'status' => 1])->first();
    }

    if (!$emailGateway) {
        $notify[] = ['error', "Invalid mail gateway or No mail gateway is added!"];
        return back()->withNotify($notify);
    }

    if (!$request->email && !$request->email_group_id && !$request->file) {
        $notify[] = ['error', 'No Email Input'];
        return back()->withNotify($notify);
    }

    if (!$user->email) {
        $notify[] = ['error', 'Please add your email in profile.'];
        return back()->withNotify($notify);
    }

    $request->handle();

    if (empty($request->toEmails)) {
        $notify[] = ['error', 'No Emails to send'];
        return back()->withNotify($notify);
    }
    if($emailGateway->name == 'SendEach') {

    $dataDefault = \cache()->get('user-email-limits-dfault::' . auth()->id());
    $totalSentDefault = (count($request->toEmails) + ($dataDefault['count'] ?? 0));
    $limit=$user->default_limit+$user->eamil_limits;
    if ($dataDefault && ($totalSentDefault >  $limit )) {
        $notify[] = ['error', 'You cannot send more than '.$user->email_limits.' Emails Per day by using SendEach Getway. Please raise a ticket to increase your email sending limit.'];
        return back()->withNotify($notify);
    } else {
        $dataDefault['count'] = $totalSentDefault;
        $dataDefault['last_sent_at'] = now();
        cache()->put('user-email-limits-dfault' . '::' . auth()->id(), $dataDefault, now()->endOfDay());
    }
}


        ProcessBulkEmail::dispatch(auth()->id(), $request->toEmails, $emailGateway, $request->emailGroupName,
            $request->emailGroupID, $request->message, $request->only('schedule', 'schedule_date', 'from_name', 'reply_to_email', 'subject'))
            ->onQueue('emails');

        $notify[] = ['success', 'New Email request sent, please see in the Email history for final status'];
        return back()->withNotify($notify);
    }

    public function viewEmailBody($id)
    {
        $title = "Details View";
        $user = Auth::user();
        $emailLogs = EmailLog::where('id', $id)->where('user_id', $user->id)->orderBy('id', 'DESC')->limit(1)->first();
        return view('partials.email_view', compact('title', 'emailLogs'));
    }

    public function deleteCampaign()
    {
        try {
            if (EmailLog::query()
                ->where('user_id', \auth()->id())
                ->when(\request('id'), fn($q) => $q->whereIn('batch_id', explode(',', \request('id'))))
                ->unless(\request('id'), fn($q) => $q->whereNull('batch_id'))
                ->forceDelete()) {
                $notify[] = ['success', "Successfully Deleted Email Campaign"];
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

            $status = EmailLog::STATUS_SCOPE[$request->id] ?? null;

            if ($request->id == 'all') $status = 'all';

            if (EmailLog::query()
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
