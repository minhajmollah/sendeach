<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiBot;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Models\EmailContact;
use App\Models\SMSlog;
use App\Models\EmailLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class CustomerController extends Controller
{

    public function index()
    {
        $title = "All user";
        $customers = User::latest('last_logged_in')->paginate(paginateNumber());
        return view('admin.customer.index', compact('title', 'customers'));
    }

    public function active()
    {
        $title = "Active user";
        $customers = User::active()->paginate(paginateNumber());
        return view('admin.customer.index', compact('title', 'customers'));
    }

    public function banned()
    {
        $title = "Banned user";
        $customers = User::banned()->paginate(paginateNumber());
        return view('admin.customer.index', compact('title', 'customers'));
    }

    public function details($id)
    {
        $title = "User Details";
        /** @var User $user */
        $user = User::findOrFail($id);
        $log['contact'] = Contact::where('user_id', $user->id)->count();
        $log['sms'] = SMSlog::where('user_id', $user->id)->count();
        $log['email_contact'] = EmailContact::where('user_id', $user->id)->count();
        $log['email'] = EmailLog::where('user_id', $user->id)->count();

        $aiBot = AiBot::firstOrCreateModel($user->id, AiBot::CHAT);
        $availableTrailTokens = $aiBot->data['openai']['available_tokens'] ?? null;

        if($user->otp && $user->otp_time > now()->addMinutes(15))
        {
            $user->update(['otp' => null, 'otp_time' => 'null']);
        }

        return view('admin.customer.details', compact('title', 'user', 'log', 'availableTrailTokens'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:120',
            'email' => 'nullable|unique:users,email,'.$id,
            'phone' => 'required|unique:users,phone,'.$id,
            'address' => 'nullable|max:250',
            'city' => 'nullable|max:250',
            'state' => 'nullable|max:250',
            'zip' => 'nullable|max:250',
            'status' => 'required|in:1,2',
            'credit' => 'required|min:0|max:20000',

            'open_ai_tokens' => 'nullable|numeric|min:0|max:10000000'
        ]);
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->credit = $request->credit;
        $user->email_limits = $request->email_limits;
        $user->default_limit = $request->default_limit;
        $user->email_limits=$request->email_limits;
        $aiBot = AiBot::firstOrCreateModel($user->id, AiBot::CHAT);
        Arr::set($aiBot->data, 'openai.available_tokens', $request->open_ai_tokens);
        $aiBot->save();

        $address = [
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip
        ];
        if($request->address || $request->city || $request->state || $request->zip){
            $user->address = $address;
        }

        $user->status = $request->status;
        $user->save();
        $notify[] = ['success', 'User has been updated'];
        return back()->withNotify($notify);
    }

    public function search(Request $request, $scope)
    {

        $search = $request->search;
        $searchDate = $request->date;


        if ($search!="") {
            $customers = User::where(function ($q) use ($search) {
                $q->where('name','like',"%$search%")->orWhere('email', 'like', "%$search%");
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
                $customers = User::whereDate('created_at',Carbon::parse($firstDate));
            }
            if ($lastDate){
                $customers = User::whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
            }
        }

        if ($search=="" && $searchDate=="") {
            $notify[] = ['error','Search data field empty'];
            return back()->withNotify($notify);
        }


        $title = '';
        if ($scope == 'active') {
            $title = 'Active ';
            $customers = $customers->active();
        }elseif($scope == 'banned'){
            $title = 'Banned';
            $customers = $customers->banned();
        }
        $customers = $customers->paginate(paginateNumber());
        $title .= 'User Search - ' . $search;
        return view('admin.customer.index', compact('title', 'search', 'scope', 'customers'));
    }

    public function contact($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." Contact List";
        $users = User::select('id', 'name')->get();
        $contacts = Contact::where('user_id', $user->id)->latest()->with('user', 'group')->paginate(paginateNumber());
        return view('admin.phone_book.sms_contact', compact('title', 'contacts', 'users'));
    }


    public function sms($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." sms list";
        $smslogs = SMSlog::where('user_id', $user->id)->latest()->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
        return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function emailContact($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." email contact list";
        $users = User::select('id', 'name')->get();
        $emailContacts = EmailContact::where('user_id', $user->id)->latest()->with('user', 'emailGroup')->paginate(paginateNumber());
        return view('admin.phone_book.email_contact', compact('title', 'emailContacts', 'users'));
    }


    public function emailLog($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." email list";
        $emailLogs = EmailLog::where('user_id', $user->id)->latest()->with('user','sender')->paginate(paginateNumber());
        return view('admin.email.index', compact('title', 'emailLogs'));
    }

}