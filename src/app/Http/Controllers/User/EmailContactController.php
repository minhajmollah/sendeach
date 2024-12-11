<?php

namespace App\Http\Controllers\User;

use App\Exports\EmailContactExport;
use App\Http\Controllers\Controller;
use App\Imports\EmailContactImport;
use App\Models\Contact;
use App\Models\EmailContact;
use App\Models\EmailGroup;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EmailContactController extends Controller
{
    public function emailGroupIndex()
    {
        $title = "Manage Email Group";
        $user = Auth::user();
        $groups = $user->emailGroup()->paginate(paginateNumber());
        EmailGroup::systemUnsubscribedGroup($user->id);
        return view('user.email_group.index' , compact('title' , 'groups'));
    }

    public function emailGroupStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255' ,
            'status' => 'required|in:1,2'
        ]);
        $user = Auth::user();
        $data['user_id'] = $user->id;
        EmailGroup::create($data);
        $notify[] = ['success' , 'Email Group has been created'];
        return back()->withNotify($notify);
    }

    public function emailGroupUpdate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255' ,
            'status' => 'required|in:1,2'
        ]);
        $user = Auth::user();
        $group = EmailGroup::where('user_id' , $user->id)->where('id' , $request->id)->firstOrFail();
        if ($group->type == Group::TYPE_SYSTEM) {
            abort(403);
        }
        $data['user_id'] = $user->id;
        $group->update($data);
        $notify[] = ['success' , 'Email Group has been created'];
        return back()->withNotify($notify);
    }

    public function emailGroupdelete(Request $request)
    {
        $user = Auth::user();
        $group = EmailGroup::where('user_id' , $user->id)->where('id' , $request->id)->firstOrFail();
        if ($group->type == Group::TYPE_SYSTEM) {
            abort(403);
        }
        $contact = EmailContact::where('user_id' , $user->id)->where('email_group_id' , $group->id)->delete();
        $group->delete();
        $notify[] = ['success' , 'Email Group has been deleted'];
        return back()->withNotify($notify);
    }

    public function emailContactByGroup($id)
    {
        $group = EmailGroup::where('id' , $id)->firstOrFail();
        $title = "Manage Email Contact List";
        $user = Auth::user();
        $contacts = EmailContact::where('user_id' , $user->id)->where('email_group_id' , $id)->with('emailGroup')->paginate(paginateNumber());
        return view('user.email_contact.index' , compact('title' , 'contacts' , 'user' , 'group'));
    }


    public function emailContactIndex()
    {
        $title = "Manage Email Contact List";
        $user = Auth::user();

        $contacts = $user->emailContact()->with('emailGroup')
            ->whereNotIn('email_group_id', EmailGroup::where(['user_id' => $user->id, 'type' => Group::TYPE_SYSTEM])->select('id'))
            ->select('email_contacts.*');


        if ($search = request('search')) {
            $contacts->join('email_groups' , 'email_groups.id' , '=' , 'email_contacts.email_group_id')
                ->where(function ($q) use ($search) {
                    return $q->whereRaw('email_contacts.email LIKE \'%' . $search . '%\'')
                        ->orWhereRaw('email_groups.name LIKE \'%' . $search . '%\'')
                        ->orWhereRaw('email_contacts.name LIKE \'%' . $search . '%\'');
                });
        }

        $contacts = $contacts->paginate(paginateNumber());

        return view('user.email_contact.index' , compact('title' , 'contacts' , 'user'));
    }

    public function emailContactStore(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'email' => 'required|email|max:120' ,
            'name' => 'required|max:90' ,
            'email_group_id' => 'required|exists:email_groups,id,user_id,' . $user->id ,
            'status' => 'required|in:1,2'
        ]);
        $data['user_id'] = $user->id;
        EmailContact::create($data);
        $notify[] = ['success' , 'Email Contact has been created'];
        return back()->withNotify($notify);
    }

    public function emailContactUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'email' => 'required|email|max:120' ,
            'name' => 'required|max:90' ,
            'email_group_id' => 'required|exists:email_groups,id,user_id,' . $user->id ,
            'status' => 'required|in:1,2'
        ]);
        $data['user_id'] = $user->id;
        $contact = EmailContact::where('user_id' , $user->id)->where('id' , $request->id)->firstOrFail();

        if ($data['status'] == Contact::INACTIVE) {
            $group = EmailGroup::systemUnsubscribedGroup($user->id);
            $group->contacts()->updateOrCreate(Arr::only($data, ['email']), Arr::except($data, 'email_group_id'));
        }

        $contact->update($data);
        $notify[] = ['success' , 'Email Contact has been updated'];
        return back()->withNotify($notify);
    }

    public function emailContactImport(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'email_group_id' => 'required|exists:email_groups,id,user_id,' . $user->id ,
            'file' => 'required|mimes:xlsx' ,
            'email_column' => 'nullable'
        ]);
        $groupId = $request->email_group_id;
        $status = false;

        $importer = new EmailContactImport($groupId , $status , $request->email_column);
        Excel::import($importer , $request->file);

        if ($importer->errors) {
            return back()->withErrors($importer->errors);
        }

        $notify[] = ['success' , 'Email Contact data has been imported'];
        return back()->withNotify($notify);
    }

    public function emailContactExport()
    {
        $status = false;
        return Excel::download(new EmailContactExport($status) , 'email_contact.xlsx');
    }

    public function emailContactGroupExport($groupId)
    {
        $status = false;
        return Excel::download(new EmailContactExport($status , $groupId) , 'email_contact.xlsx');
    }

    public function emailContactDelete(Request $request)
    {
        $user = Auth::user();
        $contact = EmailContact::where('user_id' , $user->id)->where('id' , $request->id)->firstOrFail();

        $contact->delete();
        $notify[] = ['success' , 'Email Contact has been deleted'];
        return back()->withNotify($notify);
    }

    public function unsubscribe(EmailContact $emailContact)
    {
        $emailContact->updateOrFail(['status' => Contact::INACTIVE]);

        $group = EmailGroup::systemUnsubscribedGroup();

        $group->contacts()->updateOrCreate([
            'user_id' => $emailContact->user_id ,
            'email' => $emailContact->email ,
        ] , [
            'name' => $emailContact->name ,
        ]);

        return 'You Have Successfully UnSubscribed.';
    }
}
