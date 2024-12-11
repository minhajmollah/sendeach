<?php

namespace App\Jobs;

use App\Models\WhatsappAccessToken;
use App\Models\WhatsappAccount;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappService\WhatsappBusinessService;
use App\Services\WhatsappService\WhatsappMessageTemplateService;
use App\Services\WhatsappService\WhatsappPhoneNumberService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncWhatsappAccount implements ShouldQueue , ShouldBeUnique
{
    use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

    private $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId = null)
    {
        $this->userId = $userId ?? auth('web')->id();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();

        try {

            $whatsappAccessToken = WhatsappAccessToken::query()->where('user_id' , $this->userId)->get();

            // First Sync using Access Token from config if Admin
            if (!$this->userId) {

                WhatsappAccessToken::query()->whereNull('user_id')->where(function ($query) {
                    return $query->whereNull('type')->orWhere('type' , WhatsappAccessToken::TYPE_OWN);
                })->delete();

                WhatsappAccessToken::query()->create([
                    'access_token' => config('whatsapp.access_token') ,
                    'user_id' => null ,
                    'type' => WhatsappAccessToken::TYPE_OWN
                ]);

                $whatsappAccessToken = WhatsappAccessToken::query()->where('user_id' , $this->userId)->get();
            }

            $this->sync($whatsappAccessToken);

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();

            $this->fail($throwable);
        }
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 60;

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return auth('web')->id() ?? auth('admin')->id() ?? 0;
    }

    /**
     * @param $accessTokens
     * @return void
     */
    private function sync($accessTokens): void
    {
        foreach ($accessTokens as $accessToken) {
            (new WhatsappBusinessService(null , $accessToken , $this->userId))->syncBusinessAccountDetails();
        }

        $whatsappAccounts = WhatsappAccount::with('whatsappAccessToken')->where('user_id' , $this->userId)->get();

        foreach ($whatsappAccounts as $whatsappAccount) {
            $whatsappPhoneService = new WhatsappPhoneNumberService($whatsappAccount , $whatsappAccount->whatsappAccessToken , $this->userId);
            $whatsappTemplateService = new WhatsappMessageTemplateService($whatsappAccount , $whatsappAccount->whatsappAccessToken , $this->userId);

            $whatsappPhoneService->syncPhoneNumbers();
            $whatsappTemplateService->syncTemplates();
        }
    }
}
