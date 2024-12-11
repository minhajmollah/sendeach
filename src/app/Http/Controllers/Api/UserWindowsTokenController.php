<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWindowsToken;
use Illuminate\Http\Request;

class UserWindowsTokenController extends Controller
{
    public function index(Request $request)
    {
        /**
         * @var User
         */
        $user = $request->user();

        $user_windows_token = UserWindowsToken::where('user_id', $user->id)->orderBy('id');
        $data = [
            'devices' => $user_windows_token->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function show(Request $request, UserWindowsToken $token)
    {
        /**
         * @var User
         */
        $user = $request->user();

        if($token->user_id != $user->id){
            abort(404);
        }

        $data = [
            'device' => $token,
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        /**
         * @var User
         */
        $user = $request->user();

        $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        $user_windows_token = UserWindowsToken::updateOrCreate([
            'user_id' => $user->id,
            'device_id' => $request->device_id,
        ],[
            'user_id' => $user->id,
            'device_id' => $request->device_id,
            'token' => $request->token,
            'status' => $request->status,
        ]);
        $user_windows_token = $user_windows_token->refresh();


        $data = [
            'device' => $user_windows_token,
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, UserWindowsToken $token)
    {
        /**
         * @var User
         */
        $user = $request->user();

        if($token->user_id != $user->id){
            abort(404);
        }
        $updatedData = $request->validate([
            'token' => ['string', 'max:5000'],
            'status' => ['string', 'max:255'],
        ]);

        $updated = $token->update($updatedData);
        $token = $token->refresh();

        $data = [
            'device' => $token,
        ];

        return response()->json([
            'success' => $updated,
            'data' => $data
        ]);
    }


    public function destroy(Request $request, UserWindowsToken $token)
    {
        /**
         * @var User
         */
        $user = $request->user();

        if($token->user_id != $user->id){
            abort(404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
