<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailSendRequest;
use App\Jobs\ProcessBulkEmail;
use App\Jobs\ProcessEmail;
use App\Models\EmailContact;
use App\Models\EmailGroup;
use App\Models\EmailLog;
use App\Models\MailConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ManageEmailController extends Controller
{
    public function index()
    {
        $title = "All Email History";
        $emailLogs = EmailLog::orderBy('id' , 'DESC')->with('sender' , 'user')->paginate(paginateNumber());
        return view('admin.email.index' , compact('title' , 'emailLogs'));
    }

    public function pending()
    {
        $title = "Pending Email History";
        $emailLogs = EmailLog::where('status' , EmailLog::PENDING)->orderBy('id' , 'DESC')->with('sender' , 'user')->paginate(paginateNumber());
        return view('admin.email.index' , compact('title' , 'emailLogs'));
    }

    public function success()
    {
        $title = "Delivered Email History";
        $emailLogs = EmailLog::where('status' , EmailLog::SUCCESS)->orderBy('id' , 'DESC')->with('sender' , 'user')->paginate(paginateNumber());
        return view('admin.email.index' , compact('title' , 'emailLogs'));
    }

    public function schedule()
    {
        $title = "Schedule Email History";
        $emailLogs = EmailLog::where('status' , EmailLog::SCHEDULE)->orderBy('id' , 'DESC')->with('sender' , 'user')->paginate(paginateNumber());
        return view('admin.email.index' , compact('title' , 'emailLogs'));
    }

    public function failed()
    {
        $title = "Failed Email History";
        $emailLogs = EmailLog::where('status' , EmailLog::FAILED)->orderBy('id' , 'DESC')->with('sender' , 'user')->paginate(paginateNumber());
        return view('admin.email.index' , compact('title' , 'emailLogs'));
    }

    public function search(Request $request , $scope)
    {
        $title = "Email History Search";
        $search = $request->search;
        $searchDate = $request->date;

        if ($search) {
            $emailLogs = EmailLog::where(function ($q) use ($search) {
                $q->where('to' , 'like' , "%$search%")->orWhereHas('user' , function ($user) use ($search) {
                    $user->where('email' , 'like' , "%$search%");
                });
            });
        }
        if ($searchDate) {
            $searchDate_array = explode('-' , $request->date);
            $firstDate = $searchDate_array[0];
            $lastDate = null;
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
            if ($firstDate) {
                $emailLogs = EmailLog::whereDate('created_at' , Carbon::parse($firstDate));
            }
            if ($lastDate) {
                $emailLogs = EmailLog::whereDate('created_at' , '>=' , Carbon::parse($firstDate))->whereDate('created_at' , '<=' , Carbon::parse($lastDate));
            }
        }

        if (!$search && !$searchDate) {
            $notify[] = ['error' , 'Search data field empty'];
            return back()->withNotify($notify);
        }

        if ($scope == 'pending') {
            $emailLogs = $emailLogs->where('status' , EmailLog::PENDING);
        } elseif ($scope == 'success') {
            $emailLogs = $emailLogs->where('status' , EmailLog::SUCCESS);
        } elseif ($scope == 'schedule') {
            $emailLogs = $emailLogs->where('status' , EmailLog::SCHEDULE);
        } elseif ($scope == 'failed') {
            $emailLogs = $emailLogs->where('status' , EmailLog::FAILED);
        }
        $emailLogs = $emailLogs->orderBy('id' , 'desc')->with('sender' , 'user')->paginate(paginateNumber());
        return view('admin.email.index' , compact('title' , 'emailLogs' , 'search'));
    }


    public function emailStatusUpdate(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:email_logs,id' ,
            'status' => 'required|in:1,3' ,
        ]);

        if ($request->input('emaillogid') !== null) {
            $emailLogIds = array_filter(explode("," , $request->input('emaillogid')));
            if (!empty($emailLogIds)) {
                $this->emailLogStatusUpdate((int)$request->status , (array)$emailLogIds);
            }
        }

        if ($request->has('id')) {
            $this->emailLogStatusUpdate((int)$request->status , (array)$request->input('id'));
        }

        $notify[] = ['success' , 'Email status has been updated'];
        return back()->withNotify($notify);
    }

    private function emailLogStatusUpdate(int $status , array $emailLogIds)
    {
        foreach ($emailLogIds as $emailLogId) {
            $emailLog = EmailLog::find($emailLogId);

            if (!$emailLog) {
                continue;
            }

            $emailLog->status = $status;
            $emailLog->update();
        }
    }

    public function emailSend($id)
    {

        $emailLog = EmailLog::where('status' , EmailLog::PENDING)->where('id' , $id)->firstOrFail();
        if ($emailLog->status == 1) {
            ProcessEmail::dispatch($emailLog->id);
        }
        $notify[] = ['success' , 'Mail sent'];
        return back()->withNotify($notify);
    }

    public function create()
    {
        $title = "Compose Email";
        $emailGroups = EmailGroup::whereNull('user_id')->get();
        $emailContacts = EmailContact::whereNull('user_id')->with('emailGroup')->paginate(paginateNumber());
        $mailGateways = MailConfiguration::where(['user_type' => 'admin' , 'status' => 1])->orderBy('id')->get();
        if (count($mailGateways) < 1) {
            $notify[] = ['error' , "Please activate at least one gateway to send emails!"];
            return redirect()->route('admin.mail.configuration')->withNotify($notify);
        }
        return view('admin.email.create' , compact('title' , 'emailGroups' , 'emailContacts' , 'mailGateways'));
    }

    public function store(MailSendRequest $request)
    {
        if ($request->mail_gateway) {
            $emailGateway = MailConfiguration::where(['id' => $request->mail_gateway ,
                'user_type' => User::USER_TYPE_ADMIN , 'status' => 1])->first();
        } else {
            $emailGateway = MailConfiguration::where(['user_type' => User::USER_TYPE_ADMIN ,
                'status' => 1])->first();
        }

        if (!$emailGateway) {
            $notify[] = ['error' , "Invalid mail gateway or No mail gateway is added!"];
            return back()->withNotify($notify);
        }

        if (!$request->email && !$request->email_group_id && !$request->file) {
            $notify[] = ['error' , 'No Email Input'];
            return back()->withNotify($notify);
        }

        $request->handle();

        if (empty($request->toEmails)) {
            $notify[] = ['error' , 'No Emails to send'];
            return back()->withNotify($notify);
        }

        ProcessBulkEmail::dispatch(null , $request->toEmails , $emailGateway , $request->emailGroupName ,
            $request->emailGroupID , $request->message , $request->only('schedule' , 'schedule_date' , 'from_name' , 'reply_to_email' , 'subject'));

        $notify[] = ['success' , 'New Email request sent, please see in the Email history for final status'];
        return back()->withNotify($notify);
    }

    public function viewEmailBody($id)
    {
        $title = "Details View";
        $emailLogs = EmailLog::where('id' , $id)->orderBy('id' , 'DESC')->limit(1)->first();
        return view('partials.email_view' , compact('title' , 'emailLogs'));
    }

    public function delete(Request $request)
    {
        $this->validate($request , [
            'id' => 'required'
        ]);
        try {
            $emailLog = EmailLog::query()->whereIn('id' , explode(',' , $request->id));

//            DB::table('jobs')->whereIn('id', $emailLog->pluck('id')->toArray())->delete();

            $emailLog->delete();
            $notify[] = ['success' , "Successfully email log deleted"];
        } catch (Exception $e) {
            $notify[] = ['error' , "Error occour in email delete time. Error is " + $e->getMessage()];
        }
        return back()->withNotify($notify);
    }
}