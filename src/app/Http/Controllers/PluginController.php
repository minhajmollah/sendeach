<?php

namespace App\Http\Controllers;


class PluginController extends Controller
{
    public function chat()
    {

//        $auth_token = request('access_token');
//
//        if(!$auth_token) abort(403);
//
//        $user = PersonalAccessToken::findToken($auth_token)?->tokenable;
//
//        if(!$user) {
//            abort(403);
//        }

        return \Illuminate\Support\Facades\Response::view('plugins.chat' ,
            headers: ['content-type' => 'text/javascript']);
    }
}