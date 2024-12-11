<?php

namespace App\Services\WhatsappService;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

class WhatsappMessagingService extends WhatsappBusinessService
{

    /**
     * Send Template to whatsapp user
     * @param string $phoneNumberID Whatsapp Device
     * @param array $template every message can be sent only using templates
     * @param string $to whom to send
     */
    public function sendUsingTemplate(string $phoneNumberID , array $template , string $to)
    {
        if (empty($phoneNumberID) || empty($template) || empty($to)) return null;

        return $this->send($phoneNumberID , [
            "messaging_product" => "whatsapp" ,
            "to" => $to ,
            "type" => "template" ,
            "template" => $template
        ]);
    }

    /**
     * Send Whatsapp message.
     * @param string $phoneNumberId
     * @param array $body
     * @return Response|PromiseInterface|null
     */
    public function send(string $phoneNumberId , array $body)
    {
        if (!$phoneNumberId) return null;

        return $this->http()
            ->post(static::getBaseUrl() . '/' . $phoneNumberId . '/messages' , $this->data($body));
    }
}
