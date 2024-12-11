<?php

namespace App\Services\WhatsappService;

use App\Models\WhatsappAccessToken;
use App\Models\WhatsappAccount;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class WhatsappBusinessService
{

    public ?string $whatsappBusinessId = null;

    public ?WhatsappAccount $whatsappAccount = null;

    public ?WhatsappAccessToken $accessToken = null;

    public ?WhatsappAccessToken $userAccessToken = null;

    public ?int $userId = null;

    public Response|null|PromiseInterface $lastResponse = null;

    public function __construct(WhatsappAccount|string $whatsappBusinessId = null , WhatsappAccessToken $accessToken = null , $userId = null)
    {
        $this->userId = $userId ?? auth('web')->id();

        $this->setBusinessId($whatsappBusinessId);

        $this->setAccessToken($accessToken);
    }

    public function setBusinessId($whatsappBusinessId = null): WhatsappBusinessService
    {

        if ($whatsappBusinessId instanceof WhatsappAccount) {
            $this->whatsappBusinessId = $whatsappBusinessId->whatsapp_business_id;
            $this->whatsappAccount = $whatsappBusinessId;
        } elseif ($whatsappBusinessId) {
            $this->whatsappBusinessId = $whatsappBusinessId;
            $this->whatsappAccount = WhatsappAccount::query()->where('whatsapp_business_id' , $whatsappBusinessId)->first();
        } // Set Config Whatsapp Business ID ony for Admin
//        elseif (!$this->userId) {
//            $this->whatsappBusinessId = config('whatsapp.business_account_id');
//            $this->whatsappAccount = WhatsappAccount::query()->where('whatsapp_business_id' , $this->whatsappBusinessId)->first();
//        }

        return $this;
    }

    public function setAccessToken(WhatsappAccessToken $accessToken = null): WhatsappBusinessService
    {
        if ($accessToken && $accessToken->type == WhatsappAccessToken::TYPE_OWN) {
            $this->accessToken = $accessToken;
        } elseif ($accessToken && $accessToken->type == WhatsappAccessToken::TYPE_EMBEDDED_FORM) {
            $this->accessToken = WhatsappAccessToken::query()->where('access_token' , config('whatsapp.access_token'))->first();
            $this->userAccessToken = $accessToken;
        }
//        // If there is business id and no accessToken set access token and user access token based on business ID
//        elseif ($this->whatsappBusinessId) {
//            $accessToken = WhatsappAccount::query()->where('whatsapp_business_id' , $this->whatsappBusinessId)->first()->whatsappAccessToken;
//
//            // Set access token for user by determining token type
//            if ($accessToken->type === WhatsappAccessToken::TYPE_EMBEDDED_FORM) {
//                $this->accessToken = WhatsappAccessToken::query()->where('access_token' , config('whatsapp.access_token'))->first();
//                $this->userAccessToken = $accessToken;
//            } else {
//                $this->accessToken = $accessToken;
//            }
//        }
//
//        if (!$this->accessToken) {
//            $this->accessToken = WhatsappAccessToken::query()->where('access_token' , config('whatsapp.access_token'))->first();
//        }

        return $this;
    }

    public function http(): PendingRequest
    {
        return Http::withToken($this->accessToken->access_token)
            ->accept('application/json');
    }

    public function getBaseUrl()
    {
        return 'https://graph.facebook.com/' . config('whatsapp.api_version');
    }

    static function logError($responseJsonData)
    {
        logger()->error($responseJsonData);
    }

    public function getBusinessAccount($fields = [])
    {
        return $this->http()
            ->get(sprintf("%s/%s" , static::getBaseUrl() , $this->whatsappBusinessId) , ['fields' => $fields]);
    }

    public function getClientBusinessAccounts($fields = [])
    {
        return $this->http()
            ->get(sprintf("%s/%s/client_whatsapp_business_accounts" , static::getBaseUrl() , config('whatsapp.business_manager_id')) ,
                ['fields' => $fields , 'input_token' => $this->userAccessToken->access_token]);
    }

    public function getOwnedBusinessAccounts($fields = [])
    {
        return $this->http()
            ->get(sprintf("%s/%s/owned_whatsapp_business_accounts" , static::getBaseUrl() , config('whatsapp.business_manager_id')) ,
                ['fields' => $fields]);
    }

    public function syncBusinessAccountDetails()
    {
        if ($this->userAccessToken) {
            $businessAccount = $this->getClientBusinessAccounts();
        } else {
            $businessAccount = $this->getOwnedBusinessAccounts();
        }

        // Log Error
        if ($businessAccount->status() !== 200) {
            self::logError($businessAccount->json());
            return null;
        }

        $responseJsonData = $businessAccount->json()['data'];

        if (!$responseJsonData && !is_array($responseJsonData)) return null;

        $businessAccounts = [];

        foreach ($responseJsonData as $accounts) {
            $businessAccount = [];
            $businessAccount['user_id'] = $this->userId;
            $businessAccount['currency'] = $accounts['currency'] ?? null;
            $businessAccount['whatsapp_business_id'] = $accounts['id'];
            $businessAccount['name'] = $accounts['name'];
            $businessAccount['timezone_id'] = $accounts['timezone_id'];
            $businessAccount['message_template_namespace'] = $accounts['message_template_namespace'];
            $businessAccount['whatsapp_access_token_id'] = $this->userAccessToken?->id ?? $this->accessToken?->id;

            $businessAccounts[] = $businessAccount;
        }

        return WhatsappAccount::query()->upsert(
            $businessAccounts , ['whatsapp_business_id'] , ['user_id' , 'currency' , 'name' , 'message_template_namespace' , 'timezone_id' , 'whatsapp_access_token_id']
        );
    }

    public function debugToken($token): PromiseInterface|Response
    {
        return $this->http()->get(sprintf("%s/debug_token" , static::getBaseUrl()) , ['input_token' => $token]);
    }

    public function getCreditLine(array $fields = ['id' , 'legal_entity_name']): PromiseInterface|Response
    {
        return Http::withToken($this->accessToken->access_token)
            ->accept('application/json')
            ->get(sprintf("%s/%s/extendedcredits" , static::getBaseUrl() , config('whatsapp.business_manager_id')) , [
                'fields' => $fields
            ]);
    }

    public function attachCreditLine(string $extendedCreditId , string $whatsappAccountId , string $wabaCurrency = 'USD')
    {
        return Http::withToken($this->accessToken->access_token)
            ->accept('application/json')
            ->post(sprintf("%s/%s/whatsapp_credit_sharing_and_attach" , static::getBaseUrl() , $extendedCreditId) , [
                'waba_id' => $whatsappAccountId ,
                'waba_currency' => $wabaCurrency
            ]);
    }

    public function revokeCreditSharing(string $allocationConfigId)
    {
        return Http::withToken($this->accessToken->access_token)
            ->accept('application/json')
            ->delete(sprintf("%s/%s" , static::getBaseUrl() , $allocationConfigId));
    }

    protected function data($data = [])
    {
        if ($this->userAccessToken) {
            $data = array_merge(['input_token' => $this->userAccessToken->access_token] , $data);
        }

        return $data;
    }
}
