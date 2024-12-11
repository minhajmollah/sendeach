<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GroupStoreRequest;
use App\Http\Requests\EmailStoreRequest;
use App\Models\EmailContact;
use App\Models\EmailGroup;
use Illuminate\Http\JsonResponse;

class ManageEmailGroupController extends Controller
{
    public function allGroups()
    {
        return response()->json([
            'success' => true ,
            'group' => EmailGroup::query()->where('user_id' , auth()->id())->get()
        ]);
    }

    public function updateOrCreateGroup(GroupStoreRequest $request)
    {
        $data = array_merge($request->validated() , ['user_id' => auth()->id()]);
        $data['status'] = $data['status'] ?? 1;

        $group = EmailGroup::query()->updateOrCreate(['user_id' => $data['user_id'] , 'name' => $data['name']] , $data);

        return response()->json([
            'success' => true ,
            'group' => $group
        ]);
    }

    public function deleteGroup()
    {
        $group = EmailGroup::query()->where(['user_id' => auth()->id() , 'id' => request('group_id')])->first();

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
        $group = EmailGroup::query()->where(['user_id' => auth()->id() , 'id' => $groupId])->first();

        if (!$group) {
            return response()->json([
                'success' => false ,
                'error' => ['message' => 'unable to find a group']
            ] , 404);
        }

        return response()->json([
            'success' => true ,
            'contacts' => EmailContact::query()
                ->where(['user_id' => auth()->id() , 'email_group_id' => $groupId])
                ->get()
        ]);
    }

    public function addContact($groupId , EmailStoreRequest $request)
    {
        $group = EmailGroup::query()->where(['user_id' => auth()->id() , 'id' => $groupId])->first();

        if (!$group) {
            return response()->json([
                'success' => false ,
                'error' => ['message' => 'unable to find a group']
            ] , 404);
        }

        $data = $request->validated()['data'] ?? [];

        foreach ($data as $contact) {
            $contact['status'] = $contact['status'] ?? 1;
            $isUpdated = EmailContact::query()->updateOrCreate([
                'user_id' => auth()->id() ,
                'email_group_id' => $groupId ,
                'email' => $contact['email'] ,
            ] , array_merge($contact , ['user_id' => auth()->id()]));
        }

        return response()->json([
            'success' => (bool)($isUpdated ?? false) ,
        ]);
    }

    public function deleteContact($groupId): JsonResponse
    {
        $group = EmailGroup::query()->where(['user_id' => auth()->id() , 'id' => $groupId])->first();

        if (!$group) {
            return response()->json([
                'success' => false ,
                'error' => ['message' => 'unable to find a group']
            ] , 404);
        }

        return response()->json([
            'success' => (bool)EmailContact::query()
                ->where(['email_group_id' => $groupId , 'id' => request('contact_id')])
                ->delete()
        ]);
    }
}
