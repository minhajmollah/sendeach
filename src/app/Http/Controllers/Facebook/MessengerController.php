<?php

namespace App\Http\Controllers\Facebook;

use App\Events\MessengerWebhookReceived;
use App\Http\Controllers\Controller;
use App\Models\AiBot;
use App\Models\FacebookMessenger;
use App\Services\FacebookMessengerService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MessengerController extends Controller
{

    public function index()
    {
        $title = 'Configure Facebook Messenger Bot';

        /** @var FacebookMessenger $facebookMessenger */
        $facebookMessenger = FacebookMessenger::query()->where('user_id' , auth()->id())->first();

        $verificationToken = encrypt(json_encode(['user_id' => auth()->id() ,
            'facebook_messenger_id' => $facebookMessenger?->id]));

        $totalConversations = $facebookMessenger?->chatConversations()?->count() ?: 0;

        $aiBots = AiBot::query()->where('user_id' , auth()->id())->get();

        if ($aiBots->isEmpty()) {
            return to_route(auth()->id() ? 'user.ai_bots.index' : 'admin.ai_bots.index')
                ->withNotify([['error' , 'Please configure your OpenAI Bot to start using the facebook messenger bot.']]);
        }

        return view('facebook.messenger.index' , compact('title' ,
            'facebookMessenger' , 'verificationToken' , 'totalConversations' , 'aiBots'));
    }

    public function webhookSubscribe(Request $request)
    {
        $token = json_decode(decrypt($request->get('hub_verify_token')));

        if (!$token) {
            abort(403);
        }

        $facebookMessenger = FacebookMessenger::query()->where([
            'id' => $token->facebook_messenger_id ,
        ])->firstOrFail();

        if ($request->get('hub_mode') == 'subscribe') {

            echo $request->hub_challenge;

            $messenger = new FacebookMessengerService($facebookMessenger);

            $facebookMessenger->challenge = $request->hub_challenge;

            if ($messenger->subscribe()) {
                $facebookMessenger->data['app'] = $messenger->subscribedApps()[0] ?? null;
            }

            $facebookMessenger->save();

            return;
        }

        abort(403);
    }


    public function webhooks(Request $request)
    {
        if ($request->object !== 'page') {
            abort(400);
        }

        logger()->debug(json_encode($request->all()));

        MessengerWebhookReceived::dispatch($request->all());

        return 'EVENT_RECEIVED';
    }

    public function initialize()
    {
        /** @var FacebookMessenger $facebookMessenger */
        $facebookMessenger = FacebookMessenger::query()->firstOrCreate([
            'user_id' => auth()->id() ,
        ] , [
            'ai_bot_id' => AiBot::userBot(auth()->id())?->id
        ]);

        if ($page_id = request('page_id')) {
            $page = FacebookMessengerService::getPageAccessToken($page_id);

            logger()->debug(json_encode($page));
        } else {
            $pages = FacebookMessengerService::getPagesWithTokens();
            $page = Arr::first($pages);

            logger()->debug(json_encode($pages));
        }

        if (!$page) {
            return back()->withNotify([['error' , 'Unable to get page access token.']]);
        }


        $facebookMessenger->page_access_token = Arr::get($page , 'access_token');
        $facebookMessenger->page_id = Arr::get($page , 'id');
        $facebookMessenger->data = [
            'page' => [
                'name' => Arr::get($page , 'name') ,
                'category' => Arr::get($page , 'category') ,
                'category_list' => Arr::get($page , 'category_list') ,
                'tasks' => Arr::get($page , 'tasks') ,
            ]
        ];

        $messenger = new FacebookMessengerService($facebookMessenger);

        if ($messenger->subscribe()) {
            $facebookMessenger->data['app'] = $messenger->subscribedApps()[0] ?? null;

            logger()->debug(json_encode($facebookMessenger->data['app']));
        }

        $facebookMessenger->status = FacebookMessenger::STATUS_ACTIVE;

        if (!$facebookMessenger->save()) {
            return back()->withNotify([['error' , 'Unable to save page access token. Please try again later']]);
        }
        return back()->withNotify([['success' , 'Successfully Fetched Page Access Token.']]);
    }

    public function subscribe()
    {
        /** @var FacebookMessenger $facebookMessenger */
        $facebookMessenger = FacebookMessenger::query()->firstOrCreate([
            'user_id' => auth()->id() ,
        ] , [
            'ai_bot_id' => AiBot::userBot(auth()->id())?->id
        ]);

        $messenger = new FacebookMessengerService($facebookMessenger);

        if ($messenger->subscribe()) {

            $facebookMessenger->data['app'] = $messenger->subscribedApps()[0] ?? null;

            $facebookMessenger->save();

            return back()->withNotify([['success' , 'Successfully Subscribed to messages.']]);
        }

        return back()->withNotify([['error' , 'Unable to subscribe messages.']]);
    }

    public function updateOpenAiBot()
    {
        $facebookMessenger = FacebookMessenger::query()
            ->where('id' , \request('facebook_messenger_id'))->where('user_id' , auth()->id())
            ->firstOrFail();

        $aiBot = AiBot::query()->where('id' , \request('ai_bot_id'))->where('user_id' , auth()->id())
            ->first();

        if ($aiBot) {
            $facebookMessenger->ai_bot_id = $aiBot->id;
        }

        if ($greetingText = \request('greetings_text')) {
            $facebookMessenger->greetings_text = $greetingText;
        }

        $facebookMessenger->saveOrFail();

        return back()->withNotify([['success' , 'Successfully Updated AI Bot for Facebook Page']]);
    }

    public function disconnect()
    {
        /** @var FacebookMessenger $facebookMessenger */
        $facebookMessenger = FacebookMessenger::query()->where([
            'user_id' => auth()->id() ,
        ])->firstOrFail();

        try {
            $messenger = new FacebookMessengerService($facebookMessenger);

            if (!$messenger->unsubscribe()) {
                $notify = [['error' , 'Unable to unsubscribe the webhooks. Please remove our APP from your meta business dashboard.']];
            } else {
                $notify = [['success' , 'Successfully disconnected your facebook page.']];
            }

            $facebookMessenger->delete();

        } catch (\Exception $exception) {

            return back()->withNotify([['error' , 'Unable to disconnect. Please remove our APP from your meta business dashboard.']]);
        }

        return back()->withNotify($notify);
    }
}
