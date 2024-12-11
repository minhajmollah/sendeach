<?php

namespace App\Http\Controllers;

use App\Models\AiBot;
use App\Models\FacebookLogin;
use App\Models\FacebookMessenger;
use App\Services\FacebookMessengerService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FacebookLoginController extends Controller
{
    public function redirectPageCallback()
    {
        logger()->debug('Auth Response: ' . \request('auth_response'));

        $authResponse = json_decode(\request('auth_response') , true);

        try {
            DB::beginTransaction();

            /** @var FacebookMessenger $facebookMessenger */
            $facebookMessenger = FacebookMessenger::query()->firstOrCreate([
                'user_id' => auth()->id() ,
            ], [
                'ai_bot_id' => AiBot::userBot(auth()->id())->id
            ]);

            $facebookLogin = FacebookLogin::query()->create(array_merge($authResponse , ['user_id' => auth()->id()]));

            $facebookMessenger->facebook_login_id = $facebookLogin->id;

            $pages = FacebookMessengerService::getPagesWithTokens($facebookLogin->accessToken);
            $page = Arr::first($pages);

            if (!$page) {
                $facebookMessenger->status = FacebookMessenger::STATUS_IN_ACTIVE;
                return back()->withNotify([['error' , 'Unable to get page details.']]);
            }

            $facebookMessenger->data = [
                'page' => [
                    'name' => Arr::get($page , 'name') ,
                    'category' => Arr::get($page , 'category') ,
                    'category_list' => Arr::get($page , 'category_list') ,
                    'tasks' => Arr::get($page , 'tasks') ,
                ]
            ];
            $facebookMessenger->status = FacebookMessenger::STATUS_ACTIVE;
            $facebookMessenger->page_access_token = Arr::get($page , 'access_token');
            $facebookMessenger->page_id = Arr::get($page , 'id');
            $facebookMessenger->saveOrFail();

            $messengerService = new FacebookMessengerService($facebookMessenger);

            if ($messengerService->subscribe()) {
                $facebookMessenger->data['app'] = $messengerService->subscribedApps()[0] ?? null;
            }

            $facebookMessenger->saveOrFail();

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
        }

        return back()->withNotify([['success' , 'Successfully Connected your page']]);
    }

    public function delete()
    {
        logger()->debug(json_encode(request()->all()));
        logger()->debug(json_encode(request()->header()));

        return 'REQUEST_RECEIVED';
    }

    public function deauthorize()
    {
        logger()->debug(json_encode(request()->all()));
        logger()->debug(json_encode(request()->header()));

        return 'REQUEST_RECEIVED';
    }

    public function login()
    {
        logger()->debug(json_encode(request()->all()));
        logger()->debug(json_encode(request()->header()));

        return 'REQUEST_RECEIVED';
    }
}
