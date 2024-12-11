<?php

namespace App\Http\Controllers\WhatsappBusiness;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsappTemplateRequest\WhatsappTemplateStoreRequest;
use App\Http\Requests\WhatsappTemplateRequest\WhatsappTemplateUpdateRequest;
use App\Jobs\SyncWhatsappAccount;
use App\Models\WhatsappAccount;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappService\WhatsappMessageTemplateService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WhatsappBusinessTemplateController extends Controller
{
    public function index()
    {
        $title = "Manage Template List";

        $whatsappBusinessAccounts = WhatsappAccount::query()->where('user_id' , auth('web')->id())->get();

        $prefix = $this->getRoutePrefix();

        if ($whatsappBusinessAccounts->isEmpty()) {
            $notify[] = ['error' , 'Please add atleast one Whatsapp Business account.'];
            return redirect()->route($prefix . '.business.whatsapp.account.create')->withNotify($notify);
        }
        $whatsappBusinessId = request('whatsapp_business_id' , $whatsappBusinessAccounts->first()->whatsapp_business_id);

        $templates = WhatsappTemplate::query()
            ->where('user_id' , auth('web')->id())
            ->where('whatsapp_business_id' , $whatsappBusinessId)
            ->paginate(paginateNumber());

        return view('user.whatsapp_template.index' , compact('title' , 'templates' , 'whatsappBusinessAccounts'));
    }

    public function syncTemplates()
    {
        SyncWhatsappAccount::dispatch();

        $notify[] = ['success' , 'Started Syncing your whatsapp business phone numbers and templates from all your business account. Please check back after 1-2 Minutes.'];
        return back()->withNotify($notify);
    }

    public function getTemplate($whatsappTemplate)
    {
        $whatsappTemplate = WhatsappTemplate::query()
            ->where('whatsapp_template_id' , $whatsappTemplate)
            ->where(function ($query) {
                return $query->where('user_id' , auth('web')->id())->orWhere('is_public' , true);
            })
            ->first();

        if (!$whatsappTemplate) return response()->json(['error' => 'Template not found for user'] , 404);

        return $whatsappTemplate;
    }

    public function create()
    {
        $title = "Create Template";

        $whatsappBusinessAccounts = WhatsappAccount::query()->where('user_id' , auth('web')->id())->get();

        return view('user.whatsapp_template.create' , compact('title' , 'whatsappBusinessAccounts'));
    }

    public function togglePublic()
    {
        $whatsappTemplate = WhatsappTemplate::query()->where('whatsapp_template_id' , request('whatsapp_template_id'))->firstOrFail();

        $whatsappTemplate->is_public = !$whatsappTemplate->is_public;

        $whatsappTemplate->save();

        return response()->json(['success' => true , 'is_public' => $whatsappTemplate->is_public]);
    }

    public function store(WhatsappTemplateStoreRequest $request)
    {
        $components = $request->handle();

        $whatsappTemplate = new WhatsappTemplate([
            'name' => $request->name ,
            'category' => $request->category ,
            'language' => $request->language ,
            'components' => json_encode($components) ,
            'whatsapp_business_id' => $request->whatsapp_business_id ,
            'user_id' => auth('web')->id()
        ]);

        $whatsappAccount = WhatsappAccount::query()->where('whatsapp_business_id' , $request->whatsapp_business_id)->firstOrFail();

        $whatsappTemplateService = new WhatsappMessageTemplateService($whatsappAccount , $whatsappAccount->whatsappAccessToken , auth('web')->id());

        if ($whatsappTemplateService->createTemplate($whatsappTemplate)) {
            return redirect()->route($this->getRoutePrefix() . 'business.whatsapp.template.index')->withNotify([['success' , 'Successfully Created template.']]);
        }

        return $this->errorResponse($whatsappTemplateService);
    }

    public function edit(WhatsappTemplate $whatsappTemplate)
    {
        if (!$whatsappTemplate->isEditable()) {
            $notify[] = ['error' , 'Only template with status APPROVED, REJECTED, PAUSED Can be edited.'];
            return redirect()->route($this->getRoutePrefix() . 'business.whatsapp.template.index')->withNotify($notify);
        }

        $title = "Edit Template";
        $whatsappBusinessAccounts = WhatsappAccount::query()->where('user_id' , auth('web')->id())->get();

        return view('user.whatsapp_template.create' , compact('title' , 'whatsappTemplate' , 'whatsappBusinessAccounts'));
    }

    public function update(WhatsappTemplate $whatsappTemplate , WhatsappTemplateUpdateRequest $request)
    {
        $components = $request->handle();

        $whatsappTemplate->update([
            'components' => json_encode($components) ,
        ]);

        $whatsappAccount = WhatsappAccount::query()->where('whatsapp_business_id' , $whatsappTemplate->whatsapp_business_id)->firstOrFail();
        $whatsappTemplateService = new WhatsappMessageTemplateService($whatsappAccount , $whatsappAccount->whatsappAccessToken , auth('web')->id());

        if ($whatsappTemplate->status == WhatsappTemplate::STATUS_ERROR) {
            if ($whatsappTemplateService->createTemplate($whatsappTemplate)) {
                return redirect()->route($this->getRoutePrefix() . 'business.whatsapp.template.index')->withNotify([['success' , 'Successfully Created template.']]);
            }
        } else {
            if ($whatsappTemplateService->updateTemplate($whatsappTemplate)) {
                return redirect()->route($this->getRoutePrefix() . 'business.whatsapp.template.index')
                    ->withNotify([['success' , 'Whatsapp Template has been updated successfully.']]);
            }
        }

        return $this->errorResponse($whatsappTemplateService);
    }

    public function delete($whatsappTemplate)
    {

        $whatsappTemplate = WhatsappTemplate::query()->where('user_id' , auth('web')->id())->where('id' , $whatsappTemplate)->first();

        if (!$whatsappTemplate) {
            return back()->withNotify([['error' , 'Whatsapp Template Not Found.']]);
        }

        $whatsappTemplateService = new WhatsappMessageTemplateService(
            $whatsappTemplate->whatsappBusinessAccount ,
            $whatsappTemplate->whatsappAccessToken->first() ,
            auth('web')->id()
        );

        if ($whatsappTemplate->status === WhatsappTemplate::STATUS_ERROR) {
            $whatsappTemplate->delete();

            return back()->withNotify([['success' , 'Successfully deleted the template.']]);
        }

        if ($whatsappTemplateService->deleteTemplate($whatsappTemplate)) {
            return back()->withNotify([['success' , 'Successfully deleted the template.']]);
        }

        return $this->errorResponse($whatsappTemplateService);
    }

    private function errorResponse($whatsappTemplateService , $route = null)
    {

        if (!$route) $route = $this->getRoutePrefix() . '.business.whatsapp.template.index';

        $error = $whatsappTemplateService->lastResponse->json();

        $errorMessage = Arr::get($error , 'error.error_user_msg');
        $errorTitle = Arr::get($error , 'error.error_user_title');

        $notify[] = ['error' , $errorTitle];
        $notify[] = ['error' , $errorMessage];

        return redirect()->route($route)->withNotify($notify);
    }

    private function getRoutePrefix()
    {
        return auth('web')->id() ? 'user' : 'admin';
    }

    public function updateOTPTemplate()
    {
        $whatsapp_template_id = Validator::validate(request()->all() , [
            'whatsapp_template_id' => ['required' , Rule::exists('whatsapp_templates')->where('user_id' , auth()->id())]
        ])['whatsapp_template_id'];

        $user = auth()->user();

        $user->whatsapp_business_otp_template_id = $whatsapp_template_id;
        $user->save();

        return back()->withNotify([['success' , 'Updated Your OTP Template.']]);
    }
}
