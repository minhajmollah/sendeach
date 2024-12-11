<?php

namespace App\Http\Controllers\Admin\WhatsappBusiness;

use App\Http\Controllers\Controller;
use App\Jobs\SyncWhatsappAccount;
use App\Models\WhatsappAccount;
use App\Models\WhatsappBusinessMessageRate;
use App\Models\WhatsappPhoneNumber;
use App\Services\WhatsappService\WhatsappMessageTemplateService;
use App\Services\WhatsappService\WhatsappPhoneNumberService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WhatsappBusinessAccountController extends Controller
{
    /**
     * create form show
     */
    public function create()
    {
        $title = "WhatsApp Business Accounts";
        $whatsappNumbers = WhatsappPhoneNumber::whereNull('user_id')->orderBy('id' , 'desc')->paginate(paginateNumber());
        $whatsappAccounts = WhatsappAccount::whereNull('user_id')->orderBy('id' , 'desc')->paginate(paginateNumber());

        $data = [
            'title' => $title ,
            'whatsappNumbers' => $whatsappNumbers ,
            'whatsappAccounts' => $whatsappAccounts ,
            'whatsappRates' => WhatsappBusinessMessageRate::all()->groupBy('type')
        ];
        return view('admin.whatsapp_business.create' , $data);
    }

    public function sync()
    {
        SyncWhatsappAccount::dispatch();

        $notify[] = ['success' , 'Started Syncing your whatsapp business phone numbers and templates from all your business account.'];
        return back()
            ->withNotify($notify);
    }

    /**
     * whatsapp store method
     *
     * @param Request $request
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

    public function togglePublic()
    {
        $phoneNumber = WhatsappPhoneNumber::query()->where('whatsapp_phone_number_id', request('phoneNumberId'))->firstOrFail();

        $phoneNumber->is_public = !$phoneNumber->is_public;

        $phoneNumber->save();

        return response()->json(['success' => true, 'is_public' => $phoneNumber->is_public]);
    }

    public function updateRate()
    {
        $rates = \request('rate');

        foreach ($rates as $rate) {

            $whatsappBusinessMessageRate = WhatsappBusinessMessageRate::query()->findOrFail($rate['id']);

            if ($rate['credits'] < 0) throw ValidationException::withMessages(['credits' => 'Credit value cannot be negative']);

            $whatsappBusinessMessageRate->credits = $rate['credits'];


            if(!$whatsappBusinessMessageRate->save()){
                return back()->withNotify([['error' , 'Unable to update the credit']]);
            }
        }

        return back()->withNotify([['success' , 'Successfully Updated the credits']]);
    }

    /**
     * whatsapp delete method
     *
     * @return mixed
     */
    public function delete()
    {
        $whatsappAccount = WhatsappAccount::where('whatsapp_business_id' , request('whatsapp_business_id'))->firstOrFail();

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
}
