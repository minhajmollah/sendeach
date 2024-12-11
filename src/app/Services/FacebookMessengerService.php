<?php

namespace App\Services;

use App\Models\FacebookMessenger;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class FacebookMessengerService
{

    const BASE_URL = 'https://graph.facebook.com/v16.0';

    private FacebookMessenger $facebookMessenger;

    public function __construct(FacebookMessenger $facebookMessenger)
    {
        $this->facebookMessenger = $facebookMessenger;
    }


    public function subscribe($fields = 'messages, message_deliveries, message_reads'): bool|null
    {
        $response = Http::withToken($this->facebookMessenger->page_access_token)->post(
            static::BASE_URL . '/' . $this->facebookMessenger->page_id . '/subscribed_apps' ,
            ['subscribed_fields' => $fields]
        );

        return Arr::get($response->json() , 'success');
    }

    public function unsubscribe(): bool|null
    {
        $response = Http::withToken($this->facebookMessenger->page_access_token)->delete(
            static::BASE_URL . '/' . $this->facebookMessenger->page_id . '/subscribed_apps');

        return Arr::get($response->json() , 'success');
    }

    public function subscribedApps($fields = 'messages'): array|null
    {
        return static::formatResponse(Http::withToken($this->facebookMessenger->page_access_token)->get(
            static::BASE_URL . '/' . $this->facebookMessenger->page_id . '/subscribed_apps' ,
        ));
    }

    public static function getPageAccessToken($pageId , $fields = 'access_token,category,category_list,id' ,
                                              $userAccessToken = null): array|null
    {
        $userAccessToken = $userAccessToken ?: config('facebook_messenger.facebook_user_access_token');

        return Http::withToken($userAccessToken)
            ->get(static::BASE_URL . '/' . $pageId , ['fields' => $fields])->json();
    }

    public static function getPagesWithTokens($userAccessToken = null): array|null
    {
        $userAccessToken = $userAccessToken ?: config('facebook_messenger.facebook_user_access_token');

        return static::formatResponse(Http::withToken($userAccessToken)
            ->get(static::BASE_URL . '/me/accounts'));
    }

    public function replyMessage(string $message , $psid): array|null
    {
        return Http::withToken($this->facebookMessenger->page_access_token)
            ->post(static::BASE_URL . '/' . $this->facebookMessenger->page_id . '/messages' , [
                'messaging_type' => 'RESPONSE' ,
                'recipient' => [
                    'id' => $psid
                ] ,
                'message' => [
                    'text' => $message
                ]
            ])->json();
    }


    /**
     * @param string $psid // Sender Recipient ID can be obtained when messages are received.
     * @param string $senderAction // Value can be ['mark_seen', 'typing_on']
     * @return array
     */
    public function senderAction(string $psid , string $senderAction = 'typing_on'): array
    {
        return Http::withToken($this->facebookMessenger->page_access_token)
            ->post(constructUrl(self::BASE_URL , [$this->facebookMessenger->page_id , 'messages']) , [
                'sender_action' => $senderAction ,
                'recipient' => [
                    'id' => $psid
                ]
            ])->json();
    }

    public function greetingsText(string $psid , string $message)
    {
        return Http::withToken($this->facebookMessenger->page_access_token)
            ->post(constructUrl(self::BASE_URL , ['/me/messenger_profile']) , [
                'greeting' => [
                    [
                        "locale" => "default" ,
                        "text" => $message
                    ]
                ] ,
                'recipient' => [
                    'id' => $psid
                ]
            ])->json();
    }

    private static function formatResponse(PromiseInterface|Response $response): array|null
    {
        return $response->ok() ? ($response->json()['data'] ?? null) : null;
    }
}
