<?php

namespace App\Jobs;

use App\Models\CreditLog;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappLog;
use App\Models\WhatsappCreditLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappService\WhatsappMessagingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWhatsappMessage implements ShouldQueue
{
    use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

    private $to;
    private WhatsappAccount $whatsappBusinessAccount;

    private $phoneNumberId;

    /**
     * @var int
     */
    private $logId;
    /**
     * @var Builder|Builder[]|Collection|Model|null
     */
    protected $WhatsappLog;
    /**
     * @var array
     */
    private $headerParameters;
    /**
     * @var array
     */
    private $bodyParameters;

    private $templateId;
    private $userId;
    private WhatsappTemplate $template;
    private WhatsappPhoneNumber $whatsappPhoneNumber;

    /**
     * @param string $to
     * @param $phoneNumberId
     * @param null $logId
     * @param $templateId
     * @param array|null $bodyParameters
     * @param array|null $headerParameters
     */

    public function __construct(string $to , $phoneNumberId , $logId , $templateId , ?array $bodyParameters = [] , ?array $headerParameters = [] , $userId = null)
    {
        $this->to = $to;
        $this->phoneNumberId = $phoneNumberId;
        $this->bodyParameters = $bodyParameters;
        $this->headerParameters = $headerParameters;
        $this->logId = $logId;
        $this->templateId = $templateId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $this->whatsappPhoneNumber = WhatsappPhoneNumber::where('whatsapp_phone_number_id' , $this->phoneNumberId)->first();
            $this->whatsappBusinessAccount = $this->whatsappPhoneNumber->whatsapp_account;

            /** @var WhatsappTemplate $template */
            $template = WhatsappTemplate::where('whatsapp_template_id' , $this->templateId)->first();

            $this->template = $template;

            $this->WhatsappLog = WhatsappLog::find($this->logId);

            $message = $template->toMessage($this->to , $this->headerParameters , $this->bodyParameters);

            if (!$this->WhatsappLog)
                $this->WhatsappLog = WhatsappLog::startBusinessLog($this->to , $template , $this->headerParameters , $this->bodyParameters ,
                    $this->whatsappPhoneNumber , $this->userId , $this->whatsappBusinessAccount);

            $this->WhatsappLog->initiated_time = $this->WhatsappLog->initiated_time ?? Carbon::now();

            $this->WhatsappLog->status = WhatsappLog::PROCESSING;

            $response = (new WhatsappMessagingService($this->whatsappBusinessAccount, $this->whatsappBusinessAccount->whatsappAccessToken, $this->userId))
                ->send($this->phoneNumberId , $message);

            if (is_null($response)) {
                $this->logError('Error::2 Failed to send the message. No Response');
            } elseif ($response->status() === 200) {
                $this->logSuccess($response->json());
            } else {
                $this->logError(json_encode($response->json()));
            }

            $this->WhatsappLog = null;
        } catch (\Exception $exception) {
            $this->logError('Error::2 Failed to send the message.' , $exception->getMessage());
            $this->fail($exception);
        }
    }


    public function logSuccess($response = "")
    {
        $this->WhatsappLog->status = WhatsappLog::SUCCESS;
        $this->WhatsappLog->response_gateway = json_encode($response);
        $this->WhatsappLog->save();
    }

    public function logError($error , $exceptionMessage = null)
    {
        $error = json_encode($error) . ' WABID: ' . $this->whatsappBusinessAccount . '. Phone Number ID: ' . $this->phoneNumberId;
        logger()->error($error);
        logger()->error($exceptionMessage);
        $this->WhatsappLog->status = WhatsappLog::FAILED;
        $this->WhatsappLog->response_gateway = $error;
        $this->WhatsappLog->save();

        $userId = $this->userId ?? $this->whatsappBusinessAccount->userId;

        $user = User::find($userId);

        if ($user) {
            $phone = WhatsappPhoneNumber::query()->where('whatsapp_phone_number_id' , $this->phoneNumberId)->firstOrFail();

            $credits = $this->template->totalCredit($phone->type);

            $user->credit += $credits;
            $user->save();

            $creditInfo = new CreditLog();
            $creditInfo->user_id = $userId;
            $creditInfo->credit_type = "+";
            $creditInfo->credit = $credits;
            $creditInfo->trx_number = trxNumber();
            $creditInfo->post_credit = $user->credit;
            $creditInfo->details = $credits . " Credit Return " . $this->to . " is Failed";
            $creditInfo->save();
        }
    }
}
