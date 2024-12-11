<?php

namespace App\Services\WhatsappService;

use App\Models\WhatsappDevice;
use Carbon\Carbon;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class WebApiService
{
    public static function http(): PendingRequest
    {
        return Http::withHeaders(['x-api-key' => config('requirements.core.web_api_key')]);
    }

    public static function createSession($session_id): bool
    {
        return static::isSuccess(
            static::http()->get(static::constructUrl(['session' , 'start' , $session_id]))
        );
    }

    public static function terminateInActive(): bool
    {
        return self::isSuccess(static::http()->get(self::constructUrl(['session' , 'terminateInactive'])));
    }

    private static function isSuccess(PromiseInterface|Response $response): bool
    {
        return $response->ok() && Arr::get($response->json() , 'success');
    }

    public static function constructUrl($paths = []): string
    {
        return config('requirements.core.web_api_url') . '/' . join('/' , $paths);
    }

    public static function deleteSession(mixed $session_id): bool
    {
        return static::isSuccess(
            static::http()->get(self::constructUrl(['session' , 'terminate' , $session_id]))
        );
    }

    public static function searchMessages(WhatsappDevice $gateway , $keywords , $chatId = null , int $limit = null , int $page = null , $isExact = false): ?array
    {
        $body = [
            'query' => $keywords ,
        ];

        if ($chatId) $body['options']['chatId'] = $chatId;
        if ($chatId) $body['options']['limit'] = $limit;
        if ($chatId) $body['options']['page'] = $page;

        $response = static::http()
            ->post(self::constructUrl(['client' , 'searchMessages' , $gateway->session_id]) , $body);

        if ($response->ok() && Arr::get($response->json() , 'success')) {
            $messages = $response->json()['messages'];

            return array_filter(array_map(function ($message) {
                $data = Arr::only($message , ['timestamp' , 'from' , 'to' , 'body' , 'id' , 'fromMe']);

                $data['id'] = Arr::get($data , 'id.id');

                try {
                    $data['timestamp'] = Carbon::createFromTimestamp($data['timestamp']);
                } catch (\Exception $exception) {

                }

                return $data;
            } , $messages) , function ($message) use ($keywords , $isExact) {
                if ($isExact) {
                    return ($message['body'] == $keywords) && Arr::get($message , 'fromMe');
                }

                return Arr::get($message , 'fromMe');
            });
        }

        return null;
    }

    public static function deleteMessage(WhatsappDevice $device , $messageId , $chatId , $deleteEmptyChat = true): bool
    {
        $isSuccess = static::isSuccess(
            static::http()->post(self::constructUrl(['message' , 'delete' , $device->session_id]) , [
                'chatId' => $chatId ,
                'messageId' => $messageId
            ])
        );

        if ($isSuccess && $deleteEmptyChat) {
            $chats = self::fetchMessages($device , $chatId) ?: [];

            if (!$chats || (count($chats) <= 2)) {
                return self::deleteChat($device , $chatId);
            }

            return true;
        }

        return false;
    }

    public static function fetchMessages(WhatsappDevice $gateway , $chatId , $searchOptions = []): array|null
    {
        return self::parseData(static::http()
            ->post(self::constructUrl(['chat' , 'fetchMessages' , $gateway->session_id]) , [
                'chatId' => $chatId ,
                'searchOptions' => $searchOptions ,
            ]));
    }

    public static function deleteChat(WhatsappDevice $gateway , $chatId): bool
    {
        return static::isSuccess(
            static::http()->post(self::constructUrl(['chat' , 'delete' , $gateway->session_id]) , [
                'chatId' => $chatId ,
            ])
        );
    }

    public static function send(WhatsappDevice $gateway , $chatId , $content , $contentType = 'string'): string|null
    {
        $response = static::http()
            ->post(self::constructUrl(['client' , 'sendMessage' , $gateway->session_id]) , [
                'chatId' => $chatId ,
                'contentType' => $contentType ,
                'content' => $content ,
            ]);

        if (!$response->ok()) {
            if (Arr::get($response->json() , 'error') == 'session_not_connected') {
                $gateway->status = WhatsappDevice::STATUS_DISCONNECTED;
                $gateway->save();
            }

            return null;
        }

        return Arr::get(
            $response->json() ,
            'message.id.id'
        );
    }

    public static function getContactClassInfo(WhatsappDevice $gateway , $contactId)
    {
        $response = static::http()
            ->post(self::constructUrl(['contact' , 'getClassInfo' , $gateway->session_id]) , [
                'contactId' => $contactId ,
            ]);

        return self::parseData($response , 'contact');
    }

    private static function parseData(PromiseInterface|Response $response , $key = 'messages')
    {
        if ($response->ok() && Arr::get($response->json() , 'success')) {
            return $response->json()[$key] ?? null;
        }

        return null;
    }

}
