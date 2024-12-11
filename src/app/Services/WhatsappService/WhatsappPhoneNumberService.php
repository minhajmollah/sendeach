<?php

namespace App\Services\WhatsappService;

use App\Models\WhatsappAccessToken;
use App\Models\WhatsappPhoneNumber;

class WhatsappPhoneNumberService extends WhatsappBusinessService
{

    public function getNameStatus($phoneNumberId)
    {
        return $this->getPhoneNumber($phoneNumberId , ['name_status']);
    }

    public function getPhoneNumber($phoneNumberId , $fields = [])
    {
        return $this->http()
            ->get(sprintf("%s/%s" , parent::getBaseUrl() , $phoneNumberId) , $this->data(['fields' => $fields]));
    }

    public function register($phoneNumberId , $pin)
    {
        return $this->http()
            ->post(sprintf("%s/%s/register" , parent::getBaseUrl() , $phoneNumberId) , $this->data([
                'messaging_product' => 'whatsapp' ,
                'pin' => $pin
            ]));
    }

    public function deRegister($phoneNumberId , $pin)
    {
        return $this->http()
            ->post(sprintf("%s/%s/deregister" , parent::getBaseUrl() , $phoneNumberId) , $this->data());
    }


    public function getAllPhoneNumbers($fields = [])
    {
        return $this->http()
            ->get(sprintf("%s/%s/phone_numbers" , parent::getBaseUrl() , $this->whatsappBusinessId) , $this->data(['fields' => $fields]));
    }

    public function syncPhoneNumbers()
    {
        $phoneNumbers = $this->getAllPhoneNumbers();

        $responseJsonData = $phoneNumbers->json();

        // Log Error
        if ($phoneNumbers->status() !== 200) {
            self::logError($responseJsonData);
            return $responseJsonData;
        }

        $phoneNumbers = $responseJsonData['data'];

        $phoneNumbers = array_map(function ($phoneNumber) {
            $newPhoneNumber['whatsapp_phone_number_id'] = $phoneNumber['id'];
            $newPhoneNumber['verified_name'] = $phoneNumber['verified_name'];
            $newPhoneNumber['quality_rating'] = $phoneNumber['quality_rating'];
            $newPhoneNumber['code_verification_status'] = $phoneNumber['code_verification_status'];
            $newPhoneNumber['display_phone_number'] = $phoneNumber['display_phone_number'];
            $newPhoneNumber['whatsapp_business_id'] = $this->whatsappBusinessId;
            $newPhoneNumber['type'] = $this->userAccessToken ? WhatsappAccessToken::TYPE_EMBEDDED_FORM : WhatsappAccessToken::TYPE_OWN;
            $newPhoneNumber['user_id'] = $this->userId;

            return $newPhoneNumber;
        } , $phoneNumbers);

        return WhatsappPhoneNumber::query()->upsert($phoneNumbers, ['whatsapp_phone_number_id', 'whatsapp_business_id'],
        ['verified_name', 'verified_name', 'quality_rating', 'code_verification_status', 'display_phone_number', 'whatsapp_business_id', 'type', 'user_id']);
    }
}
