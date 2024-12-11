<?php

namespace App\Http\Controllers\WhatsappBusiness;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\WhatsappMessagingTrait;
use App\Http\Requests\WhatsappMessageSendRequest;
use App\Models\Group;
use App\Models\User;
use App\Models\WhatsappCreditLog;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;


class WhatsappBusinessMessagingController extends Controller
{

    use WhatsappMessagingTrait;

    public function index()
    {
        $title = "All Whatsapp Message History";
        $whatsappLogs = WhatsappLog::orderBy('id' , 'DESC')
            ->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
            })
            ->whereIn('gateway', [WhatsappLog::GATEWAY_BUSINESS, WhatsappLog::GATEWAY_BUSINESS_OWN])
            ->with('user' , 'whatsapp_phone_number')->paginate(paginateNumber());
        return view('whatsapp_business_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function pending()
    {
        $title = "Pending Whatsapp Message History";
        $whatsappLogs = WhatsappLog::query()->where('status' , WhatsappLog::PENDING)
            ->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
            })
            ->whereIn('gateway', [WhatsappLog::GATEWAY_BUSINESS, WhatsappLog::GATEWAY_BUSINESS_OWN])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsapp_phone_number')->paginate(paginateNumber());
        return view('whatsapp_business_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function success()
    {
        $title = "Delivered Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::SUCCESS)
            ->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
            })
            ->whereIn('gateway', [WhatsappLog::GATEWAY_BUSINESS, WhatsappLog::GATEWAY_BUSINESS_OWN])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsapp_phone_number')->paginate(paginateNumber());
        return view('whatsapp_business_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function schedule()
    {
        $title = "Schedule Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::SCHEDULE)
            ->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
            })
            ->whereIn('gateway', [WhatsappLog::GATEWAY_BUSINESS, WhatsappLog::GATEWAY_BUSINESS_OWN])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsapp_phone_number')->paginate(paginateNumber());
        return view('whatsapp_business_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function failed()
    {
        $title = "Failed Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::FAILED)
            ->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
            })
            ->whereIn('gateway', [WhatsappLog::GATEWAY_BUSINESS, WhatsappLog::GATEWAY_BUSINESS_OWN])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsapp_phone_number')->paginate(paginateNumber());
        return view('whatsapp_business_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function processing()
    {
        $title = "Processing Whatsapp Message History";
        $whatsappLogs = WhatsappLog::where('status' , WhatsappLog::PROCESSING)
            ->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
            })
            ->whereIn('gateway', [WhatsappLog::GATEWAY_BUSINESS, WhatsappLog::GATEWAY_BUSINESS_OWN])
            ->orderBy('id' , 'DESC')->with('user' , 'whatsapp_phone_number')->paginate(paginateNumber());
        return view('whatsapp_business_messaging.index' , compact('title' , 'whatsappLogs'));
    }

    public function search(Request $request , $scope)
    {
        $title = "WhatsApp Message History Search";
        $search = $request->search;
        $searchDate = $request->date;

        if ($search) {
            $whatsappLogs = WhatsappLog::where(function ($q) use ($search) {
                $q->where('to' , 'like' , "%$search%");
            })
                ->whereIn('gateway', [WhatsappLog::GATEWAY_BUSINESS, WhatsappLog::GATEWAY_BUSINESS_OWN])
                ->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
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
                $whatsappLogs = WhatsappLog::whereDate('created_at' , Carbon::parse($firstDate))
                    ->when(auth('web')->id() , function ($query) {
                        return $query->where('user_id' , auth('web')->id());
                    });
            }
            if ($lastDate) {
                $whatsappLogs = WhatsappLog::whereDate('created_at' , '>=' , Carbon::parse($firstDate))
                    ->when(auth('web')->id() , function ($query) {
                        return $query->where('user_id' , auth('web')->id());
                    })
                    ->whereDate('created_at' , '<=' , Carbon::parse($lastDate));
            }
        }

        if (empty($search) && empty($searchDate)) {
            $notify[] = ['error' , 'Search data field empty'];
            return back()->withNotify($notify);
        }


        if ($scope == 'pending') {
            $whatsappLogs = $whatsappLogs->where('status' , WhatsappLog::PENDING);
        } elseif ($scope == 'success') {
            $whatsappLogs = $whatsappLogs->where('status' , WhatsappLog::SUCCESS);
        } elseif ($scope == 'schedule') {
            $whatsappLogs = $whatsappLogs->where('status' , WhatsappLog::SCHEDULE);
        } elseif ($scope == 'failed') {
            $whatsappLogs = $whatsappLogs->where('status' , WhatsappLog::FAILED);
        }
        $whatsappLogs = $whatsappLogs->orderBy('id' , 'desc')->with('user' , 'whatsapp_phone_number')->paginate(paginateNumber());

        return view('whatsapp_business_messaging.index' , compact('title' , 'whatsappLogs' , 'search' , 'searchDate'));
    }

    public function create()
    {
        $templates = WhatsappTemplate::query();

        if (request()->routeIs('user.business.whatsapp.sendeach_create')) {
            if (auth('web')->user()->credit < 1) {
                return redirect()->route('user.credits.create')
                    ->withNotify([['error' , 'You don\'t have enough credit to use sendEach whatsapp business gateway']]);
            }
            $templates = $templates->where('is_public' , true);
            $title = "SendEach Compose WhatsApp Message";
        } else {
            $templates = $templates->where('user_id' , auth('web')->id())->where('status' , 'APPROVED');
            $title = "Compose WhatsApp Message";
        }

        $templates = $templates->get();

        if (count($templates) < 1) {
            $notify[] = ['error' , 'Please add/create at least one Whatsapp Business Template to send messages!'];
            return redirect()->route($this->getRoutePrefix() . '.business.whatsapp.account.create')->withNotify($notify);
        }

        $whatsappPhoneNumbers = WhatsappPhoneNumber::with('whatsapp_account');

        if (request()->routeIs('user.business.whatsapp.sendeach_create')) {
            $whatsappPhoneNumbers = $whatsappPhoneNumbers->where('is_public' , true);
        } else {
            $whatsappPhoneNumbers = $whatsappPhoneNumbers->where('user_id' , auth('web')->id());
        }

        $whatsappPhoneNumbers = $whatsappPhoneNumbers->get();

        $groups = Group::where('user_id' , auth('web')->id())->get();
        if (count($whatsappPhoneNumbers) < 1) {
            $notify[] = ['error' , 'Please connect at least one Whatsapp Phone Number to send messages!'];
            return redirect()->route($this->getRoutePrefix() . '.business.whatsapp.account.create')->withNotify($notify);
        }
        return view('whatsapp_business_messaging.create' , compact('title' , 'templates' , 'whatsappPhoneNumbers' , 'groups'));
    }

    public function send(WhatsappMessageSendRequest $whatsappMessageSendRequest)
    {
        $whatsappMessageSendRequest->handle();

        if ($this->sendBusinessWhatsappMessage($whatsappMessageSendRequest , auth('web')->user())) {
            return back()->withNotify([['success' , 'New WhatsApp Message request sent, please see in the WhatsApp Log history for final status']]);
        }

        return back()->withNotify([['error' , 'No Message has been sent']]);
    }

    public function delete(Request $request)
    {
        return back()->withNotify([['error' , "This functionality is disabled for now."]]);

        $this->validate($request , [
            'id' => 'required'
        ]);

        try {
            $whatsappLog = WhatsappLog::where('id' , $request->id)->when(auth('web')->id() , function ($query) {
                return $query->where('user_id' , auth('web')->id());
            })->first();

            if (!$whatsappLog) {
                return back()->withNotify([['error' , "Unable to find the log."]]);
            }

            if ($whatsappLog->status == 1) {
                $user = User::find($whatsappLog->user_id);
                if ($user) {
                    $messages = str_split($whatsappLog->message , 160);
                    $totalcredit = count($messages);

                    $user->credit += $totalcredit;
                    $user->save();

                    $creditInfo = new WhatsappCreditLog();
                    $creditInfo->user_id = $whatsappLog->user_id;
                    $creditInfo->type = "+";
                    $creditInfo->credit = $totalcredit;
                    $creditInfo->trx_number = trxNumber();
                    $creditInfo->post_credit = $user->whatsapp_credit;
                    $creditInfo->details = $totalcredit . " Credit Return " . $whatsappLog->to . " is Falied";
                    $creditInfo->save();
                }
            }
            $whatsappLog->delete();
            $notify[] = ['success' , "Successfully Whatsapp Business log deleted"];
        } catch (\Exception $e) {
            $notify[] = ['error' , "Error in deleting log. Error is"];
        }
        return back()->withNotify($notify);
    }

    private function getRoutePrefix()
    {
        return auth('web')->id() ? 'user' : 'admin';
    }
}
