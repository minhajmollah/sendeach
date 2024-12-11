<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\NotifyLowBalanceToUser;
use App\Models\Admin;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\SupportFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


class SupportTicketController extends Controller
{
    public function index()
    {
        $title = "Support Ticket";
        $user = auth()->user();
        $tickets = SupportTicket::where('user_id', $user->id)->latest()->paginate(paginateNumber());
        return view('user.support.index', compact('title', 'tickets'));
    }

    public function create()
    {
        $title = "Create new ticket";
        return view('user.support.create', compact('title'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'subject' => 'required|max:255',
            'priority' => 'required|in:1,2,3',
            'message' => 'required',
        ]);
        $user = auth()->user();
        $supportTicket = new SupportTicket();
        $supportTicket->ticket_number = randomNumber();
        $supportTicket->user_id = $user->id;
        $supportTicket->name = @$user->user;
        $supportTicket->subject = $request->subject;
        $supportTicket->priority = $request->priority;
        $supportTicket->status = 1;
        $supportTicket->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $supportTicket->id;
        $message->admin_id = null;
        $message->message = $request->message;
        $message->save();

        if($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                try {
                    $supportFile = new SupportFile();
                    $supportFile->support_message_id = $message->id;
                    $supportFile->file = uploadNewFile($file, filePath()['ticket']['path']);
                    $supportFile->save();
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'Could not upload your ' . $file];
                    return back()->withNotify($notify);
                }
            }
        }

        $messages = 'Hi Admin, \n \n A new ticket has been created';
        $emailTo = Admin::query()->first()?->email ?? "jappads@gmail.com";

        Mail::send([] , [] , function ($message) use ($messages , $emailTo) {
            $message->to($emailTo)
                ->subject("New Ticket has been created")
                ->html($messages , 'text/html' , 'utf-8');
        });

        $notify[] = ['success', "Support ticket has been created"];
        return redirect()->route('user.ticket.index')->withNotify($notify);
    }

    public function detail($id)
    {
        $title = "Ticket Reply";
        $user = auth()->user();
        $ticket = SupportTicket::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        return view('user.support.detail', compact('title', 'ticket'));
    }

    public function ticketReply(Request $request, $id)
    {
        $user = auth()->user();
        $supportTicket = SupportTicket::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        $supportTicket->status = 3;
        $supportTicket->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $supportTicket->id;
        $message->admin_id = null;
        $message->message = $request->message;
        $message->save();
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                try {
                    $supportFile = new SupportFile();
                    $supportFile->support_message_id = $message->id;
                    $supportFile->file = uploadNewFile($file, filePath()['ticket']['path']);
                    $supportFile->save();
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'Could not upload your ' . $file];
                    return back()->withNotify($notify);
                }
            }
        }
        $messages = 'Hi Admin, \n \n There is a reply to your ticket. Please login to check it out';
        $emailTo = Admin::query()->first()?->email ?? "jappads@gmail.com";

        Mail::send([] , [] , function ($message) use ($messages , $emailTo) {
            $message->to($emailTo)
                ->subject("New Ticket Reply")
                ->html($messages , 'text/html' , 'utf-8');
        });

        $notify[] = ['success', "Support ticket replied successfully"];
        return back()->withNotify($notify);
    }

    public function closedTicket($id)
    {
        $user = auth()->user();
        $supportTicket =  SupportTicket::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        $supportTicket->status = 4;
        $supportTicket->save();
        $notify[] = ['success', "Support ticket has been closed"];
        return back()->withNotify($notify);
    }

    public function supportTicketDownlode($id)
    {
        $supportFile = SupportFile::findOrFail(decrypt($id));
        $file = $supportFile->file;
        $path = filePath()['ticket']['path'].'/'.$file;
        $title = slug('file').'-'.$file;
        $mimetype = mime_content_type($path);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($path);
    }
}
