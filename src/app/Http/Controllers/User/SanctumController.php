<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

class SanctumController extends Controller
{
    public function index()
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::guard('web')->user();
        $data = [
            'title' => "API Tokens",
            'user' => $user,
            'tokens' => $user->tokens()->latest()->get(),
            'abilities' => User::ABILITIES
        ];

        return view('user.sanctum-tokens.index', $data);
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:125'],
            'abilities' => ['nullable', 'array', Rule::in(array_keys(User::ABILITIES))]
        ]);

        /**
         * @var \App\Models\User
         */
        $user = Auth::guard('web')->user();

        $token = $user->createToken($request->name, $request->abilities);

        session()->flash('accessToken', $token->plainTextToken);

        $notify[] = ['success', "API token has been created!"];
        return redirect()->route('user.sanctum-token.index')->withNotify($notify);
    }

    public function destroy(PersonalAccessToken $token)
    {
        $token->delete();

        $notify[] = ['success', "Token has been deleted!"];
        return redirect()->route('user.sanctum-token.index')->withNotify($notify);
    }
}
