<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GroupStoreRequest;
use App\Http\Requests\Api\PhoneStoreRequest;
use App\Models\Contact;
use App\Models\Group;
use Illuminate\Http\JsonResponse;

class ManagePhoneGroupController extends Controller
{
    public function allGroups()
    {
        return response()->json([
            'success' => true ,
            'group' => Group::query()->where('user_id' , auth()->id())->get()
        ]);
    }

    public function updateOrCreateGroup(GroupStoreRequest $request)
    {
        $data = array_merge($request->validated() , ['user_id' => auth()->id()]);
        $data['status'] = $data['status'] ?? 1;

        $group = Group::query()->updateOrCreate(['user_id' => $data['user_id'] , 'name' => $data['name']] , $data);

        return response()->json([
            'success' => true ,
            'group' => $group
        ]);
    }

    public function deleteGroup()
    {
        $group = Group::query()->where(['user_id' => auth()->id() , 'id' => request('group_id')])->first();

        if (!$group) {
            return response()->json([
                'success' => false ,
                'error' => ['message' => 'unable to find a group']
            ] , 404);
        }

        return response()->json([
            'success' => $group->delete()
        ]);
    }

    public function contacts($groupId)
    {
        $group = Group::query()->where(['user_id' => auth()->id() , 'id' => $groupId])->first();

        if (!$group) {
            return response()->json([
                'success' => false ,
                'error' => ['message' => 'unable to find a group']
            ] , 404);
        }

        return response()->json([
            'success' => true ,
            'contacts' => Contact::query()
                ->where(['user_id' => auth()->id() , 'group_id' => $groupId])
                ->get()
        ]);
    }

    public function addContact($groupId , PhoneStoreRequest $request)
    {
        $group = Group::query()->where(['user_id' => auth()->id() , 'id' => $groupId])->first();

        if (!$group) {
            return response()->json([
                'success' => false ,
                'error' => ['message' => 'unable to find a group']
            ] , 404);
        }

        $data = $request->validated()['data'] ?? [];

        foreach ($data as $contact) {
            $contact['status'] = $contact['status'] ?? 1;

            $isUpdated = $contact = Contact::query()->updateOrCreate([
                'user_id' => auth()->id() ,
                'group_id' => $groupId ,
                'contact_no' => $contact['contact_no'] ,
            ] , array_merge($contact , ['user_id' => auth()->id()]));
        }

        return response()->json([
            'success' => (bool)($isUpdated ?? false) ,
        ]);
    }

    public function deleteContact($groupId): JsonResponse
    {
        $group = Group::query()->where(['user_id' => auth()->id() , 'id' => $groupId])->first();

        if (!$group) {
            return response()->json([
                'success' => false ,
                'error' => ['message' => 'unable to find a group']
            ] , 404);
        }

        return response()->json([
            'success' => (bool)Contact::query()
                ->where(['group_id' => $groupId , 'id' => request('contact_id')])
                ->delete()
        ]);
    }
}
