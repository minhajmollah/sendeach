<?php

namespace App\Http\Controllers\User\WhatsappBusiness;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsappAccessTokenRequest;
use App\Jobs\SyncWhatsappAccount;
use App\Models\FacebookLogin;
use App\Models\GeneralSetting;
use App\Models\WhatsappAccessToken;
use App\Models\WhatsappAccount;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappService\WhatsappBusinessService;
use App\Services\WhatsappService\WhatsappMessageTemplateService;
use App\Services\WhatsappService\WhatsappPhoneNumberService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WhatsappBusinessAccountController extends Controller
{
    /**
     * create form show
     */
    public function create()
    {
        $title = "WhatsApp Business Accounts";
        $whatsappNumbers = WhatsappPhoneNumber::orderBy('id' , 'desc')->where('user_id' , auth('web')->id())->paginate(paginateNumber());
        $templates = WhatsappTemplate::orderBy('id' , 'desc')->where('user_id' , auth('web')->id())->get();
        $whatsappAccounts = WhatsappAccount::orderBy('id' , 'desc')->where('user_id' , auth('web')->id())->paginate(paginateNumber());


        $data = [
            'title' => $title ,
            'whatsappNumbers' => $whatsappNumbers ,
            'whatsappAccounts' => $whatsappAccounts ,
            'hasAccessOwnToken' => WhatsappAccessToken::query()->where('user_id' , auth('web')->id())
                ->where('type' , WhatsappAccessToken::TYPE_OWN)->exists() ,
            'hasAccessEmbeddedToken' => WhatsappAccessToken::query()->where('user_id' , auth('web')->id())
                ->where('type' , WhatsappAccessToken::TYPE_EMBEDDED_FORM)->exists() ,
            'templates' => $templates,
        ];
        return view('user.whatsapp_business.create' , $data);
    }

    public function sync()
    {
        SyncWhatsappAccount::dispatch();

        $notify[] = ['success' , 'Started Syncing your whatsapp business phone numbers and templates from all your business account. Please check back after 1-2 Minutes.'];
        return back()
            ->withNotify($notify);
    }

    /**
     * whatsapp store method
     *
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'whatsapp_business_id' => 'required|unique:whatsapp_accounts,whatsapp_business_id' ,
        ]);

        $whatsappPhoneService = new WhatsappPhoneNumberService($request->whatsapp_business_id);

        $whatsappAccount = $whatsappPhoneService->syncBusinessAccountDetails();

        if (!$whatsappAccount) throw ValidationException::withMessages([
            'whatsapp_business_id' => 'Invalid Whatsapp Business Account. Could not verify the whatsapp account' ,
        ]);

        $whatsappPhoneService->syncPhoneNumbers();

        $whatsappTemplateService = new WhatsappMessageTemplateService($request->whatsapp_business_id);
        $whatsappTemplateService->syncTemplates();

        $notify[] = ['success' , 'Whatsapp Device successfully added'];
        return back()->withNotify($notify);
    }

    /**
     * whatsapp account delete method
     * @return mixed
     */
    public function delete()
    {
        $whatsappAccount = WhatsappAccount::query()
            ->where('user_id' , auth('web')->id())
            ->where('whatsapp_business_id' , request('whatsapp_business_id'))
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $whatsappAccount->whatsapp_phone_numbers()->delete();
            $whatsappAccount->whatsapp_templates()->delete();
            $whatsappAccount->deleteOrFail();
            DB::commit();
            $notify[] = ['success' , 'Whatsapp Account successfully Deleted'];
            return back()->withNotify($notify);
        } catch (Exception $exception) {
            DB::rollBack();
            return back()->withNotify([['error' , 'Unable to delete the whatsapp account.' . $exception->getMessage()]]);
        }
    }

    public function updateAccessToken(WhatsappAccessTokenRequest $request)
    {
        $request->handle();

        $authUserID = auth('web')->id();

        $whatsappAccessToken = WhatsappAccessToken::query()
            ->where('user_id' , $authUserID)
            ->where('type' , WhatsappAccessToken::TYPE_OWN)
            ->first();

        if ($whatsappAccessToken) {
            $whatsappAccessToken->access_token = $request->accessToken;
            $whatsappAccessToken->save();
        } else {
            WhatsappAccessToken::create([
                'access_token' => $request->accessToken ,
                'user_id' => $authUserID ,
                'type' => WhatsappAccessToken::TYPE_OWN
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true , 'message' => 'Successfully added Access Token.']);
        }

        $notify[] = ['success' , 'System User Access Token Added.'];
        return back()->withNotify($notify);
    }

    public function updateEmbeddedAccessToken(WhatsappAccessTokenRequest $request)
    {
        $request->handle();

        $authUserID = auth('web')->id();

        try {
            $whatsappAccessToken = WhatsappAccessToken::query()
                ->where('user_id' , $authUserID)
                ->where('type' , WhatsappAccessToken::TYPE_EMBEDDED_FORM)
                ->first();

            if ($whatsappAccessToken) {
                $whatsappAccessToken->access_token = $request->accessToken;
                $whatsappAccessToken->save();
            } else {
                $whatsappAccessToken = WhatsappAccessToken::create([
                    'access_token' => $request->accessToken ,
                    'user_id' => $authUserID ,
                    'type' => WhatsappAccessToken::TYPE_EMBEDDED_FORM
                ]);
            }

            $facebookLogin = FacebookLogin::query()->where('whatsapp_access_token_id' , $whatsappAccessToken->id)->first();

            $data = array_merge($request->all() ,
                ['whatsapp_access_token_id' => $whatsappAccessToken->id , 'user_id' => $authUserID]);

            if ($facebookLogin) {
                $facebookLogin->update($data);
            } else {
                FacebookLogin::query()->create($data);
            }

        } catch (Exception $exception) {
            return response()->json(['success' => false , 'message' => 'Unable to store Access Token.' . $exception->getMessage()] , 500);
        }

        SyncWhatsappAccount::dispatch(auth('web')->id());

        return response()->json(['success' => true , 'message' => 'Successfully added Access Token.']);
    }

    public function registerPhone(Request $request)
    {
        Validator::validate($request->all() , ['pin' => 'required|digits:6']);

        $whatsappPhoneNumber = WhatsappPhoneNumber::query()->where('whatsapp_phone_number_id' , $request['whatsapp_phone_number_id'])->firstOrFail();

        $phoneNumberService = new WhatsappPhoneNumberService($whatsappPhoneNumber->whatsapp_account , null , auth('web')->id());

        $response = $phoneNumberService->register($whatsappPhoneNumber->whatsapp_phone_number_id , $request['pin'])->json();

        $whatsappPhoneNumber->pin = $request['pin'];

        if (Arr::get($response , 'success')) {
            $whatsappPhoneNumber->is_registered = true;

            return back()->withNotify([['success' , 'Registered Successfully']]);
        } elseif (Arr::get($response , 'error')) {
            $whatsappPhoneNumber->is_registered = false;

            return back()->withNotify([['error' , Arr::get($response , 'error.message')]]);
        }

        $whatsappPhoneNumber->save();

        return back()->withNotify([['error' , 'Unable to register. Please try again later.']]);
    }

    public function activateWhatsappAccount(WhatsappAccount $whatsappAccount)
    {

        $user = auth()->user();

        if ($user->whatsapp_credit < 1000) {
            return back()->withNotify([['error' , 'Please maintain at least 1000 credits to activate your whatsapp account']]);
        }

        $accessToken = WhatsappAccessToken::getAdminAccessToken()->first();

        $whatsappService = new WhatsappBusinessService(null , $accessToken , null);

        $creditLine = $whatsappService->getCreditLine()->json();

        if ($extendedId = Arr::get($creditLine , 'data.0.id')) {
            $response = $whatsappService->attachCreditLine($extendedId , $whatsappAccount->whatsapp_business_id)->json();

            if ($allocation_config_id = Arr::get($response , 'allocation_config_id')) {
                $whatsappAccount->extended_credit_id = $extendedId;
                $whatsappAccount->$allocation_config_id = $$allocation_config_id;
                $whatsappAccount->save();

                return back()->withNotify([['success' , 'Successfully activated your whatsapp account.']]);
            }
        }

        return back()->withNotify([['error' , 'Unable to Activate Whatsapp Account']]);
    }
}
