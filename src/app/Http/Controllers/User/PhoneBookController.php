<?php

namespace App\Http\Controllers\User;

use App\Exports\ContactExport;
use App\Http\Controllers\Controller;
use App\Imports\ContactImport;
use App\Models\Contact;
use App\Models\Country;
use App\Models\GeneralSetting;
use App\Models\Group;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PhoneBookController extends Controller
{

    public function groupIndex()
    {
        $title = "Manage SMS Group";
        $user = Auth::user();
        $groups = Group::whereNotNull('user_id')->where('user_id', $user->id)->paginate(paginateNumber());
        Group::systemUnsubscribedGroup($user->id);
        return view('user.group.index', compact('title', 'groups'));
    }

    public function groupStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'status' => 'required|in:1,2'
        ]);
        $user = Auth::user();
        $data['user_id'] = $user->id;
        Group::create($data);
        $notify[] = ['success', 'Group has been created'];
        return back()->withNotify($notify);
    }

    public function groupUpdate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'status' => 'required|in:1,2'
        ]);
        $user = Auth::user();
        $group = Group::where('user_id', $user->id)->where('id', $request->id)->firstOrFail();
        if ($group->type == Group::TYPE_SYSTEM) {
            abort(403);
        }
        $data['user_id'] = $user->id;
        $group->update($data);
        $notify[] = ['success', 'Group has been created'];
        return back()->withNotify($notify);
    }

    public function groupdelete(Request $request)
    {
        $user = Auth::user();
        $group = Group::where('user_id', $user->id)->where('id', $request->id)->firstOrFail();
        if ($group->type == Group::TYPE_SYSTEM) {
            abort(403);
        }
        $contact = Contact::where('user_id', $user->id)->where('group_id', $group->id)->delete();
        $group->delete();
        $notify[] = ['success', 'Group has been deleted'];
        return back()->withNotify($notify);
    }

    public function smsContactByGroup($id)
    {
        $group = Group::findOrFail($id);
        $title = "Manage SMS Contact List";
        $user = Auth::user()->load('group');
        $contacts = Contact::where('user_id', $user->id)->where('group_id', $id)->with('group')->paginate(paginateNumber());

        return view('user.contact.index', compact('title', 'contacts', 'user', 'group'));
    }

    public function contactIndex()
    {
        $groups = Group::where('user_id', auth()->user()->id)->where('type', '<>', Group::TYPE_SYSTEM)->get();
        if ($groups->count() < 1) {
            $notify[] = ['warning', "You need to create at least one group before you can create contacts!"];
            return redirect()->route('user.phone.book.group.index')->withNotify($notify);
        }

        $title = "Manage SMS Contact List";

        $contacts = Contact::with('group')->where('contacts.user_id', \auth()->id())
            ->whereIn('group_id', $groups->pluck('id')->toArray())
            ->select('contacts.*');

        if ($search = request('search')) {
            $contacts->join('groups', 'contacts.group_id', '=', 'groups.id')
                ->whereRaw('contact_no LIKE \'%' . $search . '%\'')
                ->orWhereRaw('groups.name LIKE \'%' . $search . '%\'')
                ->orWhereRaw('contacts.name LIKE \'%' . $search . '%\'');
        }

        $contacts = $contacts->paginate(paginateNumber());

        $user = \auth()->user()->setRelation('group', $groups);
        $countries = Country::active()->select('phone_code')->distinct()->orderBy('phone_code')->get();

        return view('user.contact.index', compact('title', 'contacts', 'user', 'countries'));
    }

    public function contactStore(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'contact_no' => 'required|numeric|digits:10',
            'country_code' => 'required|numeric|digits_between:1,3',
            'name' => 'required|max:90',
            'group_id' => 'required|exists:groups,id,user_id,' . $user->id,
            'status' => 'required|in:1,2'
        ]);
        $general = GeneralSetting::first();
        $data['contact_no'] = $data['country_code'] . $data['contact_no'];
        $data['user_id'] = $user->id;
        Contact::create($data);
        $notify[] = ['success', 'Contact has been created'];
        return back()->withNotify($notify);
    }

    public function contactUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'contact_no' => 'required|numeric|digits:10',
            'country_code' => 'required|numeric|digits_between:1,3',
            'name' => 'required|max:90',
            'group_id' => 'required|exists:groups,id,user_id,' . $user->id,
            'status' => 'required|in:1,2'
        ]);
        $data['user_id'] = $user->id;
        $data['contact_no'] = $data['country_code'] . $data['contact_no'];
        $contact = Contact::where('user_id', $user->id)->where('id', $request->id)->firstOrFail();

        if ($data['status'] == Contact::INACTIVE) {
            $group = Group::systemUnsubscribedGroup($user->id);
            $group->contacts()->updateOrCreate(Arr::only($data, ['contact_no']), Arr::except($data, 'group_id'));
        }
        $contact->update($data);
        $notify[] = ['success', 'Contact has been updated'];
        return back()->withNotify($notify);
    }

    public function contactImport(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'group_id' => 'required|exists:groups,id,user_id,' . $user->id,
//            'file'=> 'required|mimes:xlsx',
            'phone_column' => 'nullable'
        ]);
        $groupId = $request->group_id;
        $status = false;
        $importer = new ContactImport($groupId, $status, $request->phone_column);
        Excel::import($importer, $request->file);

        if ($importer->errors) {
            return back()->withErrors($importer->errors);
        }

        $notify[] = ['success', 'New contact has been uploaded'];
        return back()->withNotify($notify);
    }

    public function contactExport(Request $request)
    {
        $status = false;
        return Excel::download(new ContactExport($status), 'sms_contact.xlsx');
    }

    public function contactGroupExport($groupId)
    {
        $status = false;
        return Excel::download(new ContactExport($status, $groupId), 'sms_contact.xlsx');
    }

    public function contactDelete(Request $request)
    {
        $user = Auth::user();
        $contact = Contact::where('user_id', $user->id)->where('id', $request->id)->firstOrFail();

        $contact->delete();
        $notify[] = ['success', 'Contact has been deleted'];
        return back()->withNotify($notify);
    }

    public function templateIndex()
    {
        $title = "Manage Template List";
        $user = Auth::user();
        $templates = $user->template()->paginate(paginateNumber());
        return view('user.template.index', compact('title', 'templates'));
    }

    public function templateStore(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'message' => 'required',
        ]);
        $message = '';
        $user = Auth::user();
        $data = Template::create([
            'name' => $request->name,
            'message' => offensiveMsgBlock($request->message),
            'user_id' => $user->id,
            'status' => 2,
        ]);
        if (offensiveMsgBlock($request->message) != $request->message) {
            $message = session()->get('offsensiveNotify');
        }
        $notify[] = ['success', 'Template has been created with ' . $message];
        return back()->withNotify($notify);
    }

    public function templateUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'message' => 'required',
        ]);
        $message = '';
        $user = Auth::user();
        $template = Template::where('user_id', $user->id)->where('id', $request->id)->firstOrFail();
        $template->update([
            'name' => $request->name,
            'message' => offensiveMsgBlock($request->message),
            'user_id' => $user->id,
            'status' => 2,
        ]);
        if (offensiveMsgBlock($request->message) != $request->message) {
            $message = session()->get('offsensiveNotify');
        }
        $notify[] = ['success', 'Template has been created ' . $message];
        return back()->withNotify($notify);
    }

    public function templateDelete(Request $request)
    {
        $user = Auth::user();
        $template = Template::where('user_id', $user->id)->where('id', $request->id)->firstOrFail();
        $template->delete();
        $notify[] = ['success', 'Template has been deleted'];
        return back()->withNotify($notify);
    }

    public function toggleActive()
    {
        $contacts = explode(',', request('contacts'));

        $contacts = Contact::query()->whereIn('id', $contacts)->get();


        return back()->with([['success', 'Successfully Updated']]);
    }

    public function unsubscribe(Contact $contact)
    {
        $contact->updateOrFail(['status' => Contact::INACTIVE]);

        $group = Group::systemUnsubscribedGroup(auth()->id());

        $group->contacts()->updateOrCreate([
            'user_id' => $contact->user_id,
            'contact_no' => $contact->contact_no,
        ], [
            'name' => $contact->name,
        ]);

        return 'You Have Successfully UnSubscribed.';
    }

    public function groupContactsCount()
    {
        $groupIds = request('groupIds', []);

        return [
            'count' => Contact::active()->where('user_id', auth()->id())
                ->whereNotIn('contact_no',
                    Group::unsubscribedContacts(auth()->id())->pluck('contact_no')->toArray()
                )
                ->whereIn('group_id', $groupIds)->count()
        ];
    }
}
