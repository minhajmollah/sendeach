<?php

use App\Http\Controllers\CreditsController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PaymentMethod\CoinbaseCommerce;
use App\Http\Controllers\PaymentMethod\PaymentController;
use App\Http\Controllers\PaymentMethod\PaymentWithFlutterwave;
use App\Http\Controllers\PaymentMethod\PaymentWithInstamojo;
use App\Http\Controllers\PaymentMethod\PaymentWithPaypal;
use App\Http\Controllers\PaymentMethod\PaymentWithPayStack;
use App\Http\Controllers\PaymentMethod\PaymentWithPaytm;
use App\Http\Controllers\PaymentMethod\PaymentWithRazorpay;
use App\Http\Controllers\PaymentMethod\PaymentWithStripe;
use App\Http\Controllers\PaymentMethod\SslCommerzPaymentController;
use App\Http\Controllers\User\AndroidApiController;
use App\Http\Controllers\User\EmailContactController;
use App\Http\Controllers\User\EmailTemplateController;
use App\Http\Controllers\User\GeneralSettingController;
use App\Http\Controllers\User\AdvancedSettingController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\MailConfigurationController;
use App\Http\Controllers\User\ManageEmailController;
use App\Http\Controllers\User\ManageSMSController;
use App\Http\Controllers\User\PhoneBookController;
use App\Http\Controllers\User\PlanController;
use App\Http\Controllers\User\SanctumController;
use App\Http\Controllers\User\SmsGatewayController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\User\Whatsapp\DesktopController;
use App\Http\Controllers\User\Whatsapp\WebController;
use App\Http\Controllers\User\WhatsappBusiness\WhatsappBusinessAccountController;
use App\Http\Controllers\WebChatController;
use App\Http\Controllers\Whatsapp\DeviceController;
use App\Http\Controllers\Whatsapp\MessageDeleteController;
use App\Http\Controllers\Whatsapp\WebDeviceController;
use App\Http\Controllers\WhatsappBusiness\WhatsappBusinessMessagingController;
use App\Http\Controllers\WhatsappBusiness\WhatsappBusinessTemplateController;
use App\Http\Middleware\ValidateCaptcha;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Auth\AuthenticatedSessionController;

// landing page
Route::view("/" , "homepage")->name("home");


Route::view("/terms-and-conditions" , "website.terms-and-services")->name("terms-and-conditions");
Route::view("/privacy-policy" , "website.privacy-policy")->name("privacy-policy");
Route::prefix('docs')->name('docs.')->group(function () {
    Route::view("/" , "docs.index")->name("index");
    Route::view("/auth" , "docs.auth")->name("auth");
    Route::view("/api-tokens" , "docs.api-token")->name("api-token");
    Route::view("/developers-api" , "docs.developers-api")->name("developers-api");
    Route::view("/whatsapp/api" , "docs.whatsapp-api")->name("whatsapp-api");
    Route::view("/whatsapp/gateway" , "docs.whatsapp-gateway")->name("whatsapp-gateway");
    Route::view("/business/whatsapp" , "docs.business-whatsapp")->name("business.whatsapp");
    Route::view("/sms/api" , "docs.sms-api")->name("sms-api");
    Route::view("/sms/gateway" , "docs.sms-gateway")->name("sms.gateway");
    Route::view("/email/gateway" , "docs.email-gateway")->name("email.gateway");
    Route::view("/whatsapp-tools" , "docs.whatsapp-tools")->name("whatsapp_tools");
    Route::view("/ai-customer-representatives" , "docs.ai-customer-reps")->name("ai_customer_reps");
    Route::view("/wordpress" , "docs.wordpress")->name("wordpress");
    Route::view("/support" , "docs.support")->name("support");
    Route::get('postman' , function () {
        return response()->download('assets/postman/SendEach API - Docs.postman_collection.json');
    })->name('api.postman');
});

Route::get('queue-work' , function () {
    return Illuminate\Support\Facades\Artisan::call('queue:work' , ['--stop-when-empty' => true]);
})->name('queue.work');

Route::get('cron/run' , [CronController::class , 'run'])->name('cron.run');

Route::get('/go/{short_url}' , function ($short_url) {
    $short_url = \App\Models\ShortUrl::query()->where('short_url' , $short_url)->firstOrFail();
    return redirect($short_url->destination , secure: true);
});

Route::middleware(['auth' , 'maintanance' , 'demo.mode'])->prefix('user')->name('user.')->group(function () {
    Route::middleware(['checkUserStatus'])->group(function () {
        Route::get('dashboard' , [HomeController::class , 'dashboard'])->name('dashboard');
        Route::get('profile' , [HomeController::class , 'profile'])->name('profile');
        Route::post('profile/update' , [HomeController::class , 'profileUpdate'])->name('profile.update');
        Route::get('password' , [HomeController::class , 'password'])->name('password');
        Route::post('password/update' , [HomeController::class , 'passwordUpdate'])->name('password.update');
        Route::post('/update/status',[AdvancedSettingController::class,'updateStatus'])->name('status');
        // Sanctum API Token
        Route::prefix('token')->name('sanctum-token.')->controller(SanctumController::class)->group(function () {
            Route::get('/' , 'index')->name('index');
            Route::post('/' , 'store')->name('store');
            Route::delete('/{token}' , 'destroy')->name('destroy');
        });
     // Advanced setting Routes
     Route::get('advance/setting' , [AdvancedSettingController::class , 'index'])->name('advanced.index');
     Route::post('advance/setting' , [AdvancedSettingController::class , 'save'])->name('advanced.save');
        //credit Log
        Route::get('transaction/log' , [HomeController::class , 'transaction'])->name('transaction.history');
        Route::get('transaction/search' , [HomeController::class , 'transactionSearch'])->name('transaction.search');

        //credit Log
        Route::get('credit/log' , [HomeController::class , 'credit'])->name('credit.history');
        Route::get('credit/search' , [HomeController::class , 'creditSearch'])->name('credit.search');

        //Email credit Log
        Route::get('email/credit/log' , [HomeController::class , 'emailCredit'])->name('credit.email.history');
        Route::get('email/credit/search' , [HomeController::class , 'emailCreditSearch'])->name('credit.email.search');

        //ConfirmEmail
        Route::post('confirmemail' , [HomeController::class , 'confirmEmail'])->name('confirm.email');

        //Phone book
        Route::get('sms/contact/group/{id}' , [PhoneBookController::class , 'smsContactByGroup'])->name('phone.book.sms.contact.group');
        Route::get('sms/groups' , [PhoneBookController::class , 'groupIndex'])->name('phone.book.group.index');
        Route::get('sms/groups/contacts-count' , [PhoneBookController::class , 'groupContactsCount'])->name('phone.book.group.contacts_count');
        Route::get('sms/contact/group/{id}' , [PhoneBookController::class , 'smsContactByGroup'])->name('phone.book.sms.contact.group');
        Route::post('sms/group/store' , [PhoneBookController::class , 'groupStore'])->name('phone.book.group.store');
        Route::post('sms/group/update' , [PhoneBookController::class , 'groupUpdate'])->name('phone.book.group.update');
        Route::post('sms/group/delete' , [PhoneBookController::class , 'groupdelete'])->name('phone.book.group.delete');
        Route::post('sms/group/toggle-active' , [PhoneBookController::class , 'toggleActive'])->name('phone.book.group.toggle_active');
        Route::get('sms/group/unsubscribe/{contact}' , [PhoneBookController::class , 'unsubscribe'])->name('phone.book.group.unsubscribe')->middleware('signed');

        Route::get('sms/contacts' , [PhoneBookController::class , 'contactIndex'])->name('phone.book.contact.index');
        Route::post('sms/contact/store' , [PhoneBookController::class , 'contactStore'])->name('phone.book.contact.store');
        Route::post('sms/contact/update' , [PhoneBookController::class , 'contactUpdate'])->name('phone.book.contact.update');
        Route::post('sms/contact/delete' , [PhoneBookController::class , 'contactDelete'])->name('phone.book.contact.delete');
        Route::post('sms/contact/import' , [PhoneBookController::class , 'contactImport'])->name('phone.book.contact.import');
        Route::get('sms/contact/export' , [PhoneBookController::class , 'contactExport'])->name('phone.book.contact.export');
        Route::get('sms/contact/group/export/{id}' , [PhoneBookController::class , 'contactGroupExport'])->name('phone.book.contact.group.export');

        Route::get('sms/templates' , [PhoneBookController::class , 'templateIndex'])->name('template.index');
        Route::post('sms/template/store' , [PhoneBookController::class , 'templateStore'])->name('phone.book.template.store');
        Route::post('sms/template/update' , [PhoneBookController::class , 'templateUpdate'])->name('phone.book.template.update');
        Route::post('sms/template/delete' , [PhoneBookController::class , 'templateDelete'])->name('phone.book.template.delete');

        //Email
        Route::get('email/groups' , [EmailContactController::class , 'emailGroupIndex'])->name('email.group.index');
        Route::get('email/contact/group/{id}' , [EmailContactController::class , 'emailContactByGroup'])->name('email.contact.group');
        Route::post('email/group/store' , [EmailContactController::class , 'emailGroupStore'])->name('email.group.store');
        Route::post('email/group/update' , [EmailContactController::class , 'emailGroupUpdate'])->name('email.group.update');
        Route::post('email/group/delete' , [EmailContactController::class , 'emailGroupdelete'])->name('email.group.delete');
        Route::get('email/group/unsubscribe/{emailContact}' , [EmailContactController::class , 'unsubscribe'])->name('email.group.unsubscribe')->middleware('signed');

        Route::get('email/contacts' , [EmailContactController::class , 'emailContactIndex'])->name('email.contact.index');
        Route::post('email/contact/store' , [EmailContactController::class , 'emailContactStore'])->name('email.contact.store');
        Route::post('email/contact/update' , [EmailContactController::class , 'emailContactUpdate'])->name('email.contact.update');
        Route::post('email/contact/import' , [EmailContactController::class , 'emailContactImport'])->name('email.contact.import');
        Route::get('email/contact/export' , [EmailContactController::class , 'emailContactExport'])->name('email.contact.export');
        Route::get('email/contact/group/export/{id}' , [EmailContactController::class , 'emailContactGroupExport'])->name('email.contact.group.export');
        Route::post('email/contact/delete' , [EmailContactController::class , 'emailContactDelete'])->name('email.contact.delete');

        Route::get('email/send' , [ManageEmailController::class , 'create'])->name('manage.email.send');
        Route::get('email/log/campaign' , [ManageEmailController::class , 'campaign'])->name('manage.email.campaign');
        Route::get('email/log/all' , [ManageEmailController::class , 'index'])->name('manage.email.index');
        Route::get('email/log/pending' , [ManageEmailController::class , 'index'])->name('manage.email.pending');
        Route::get('email/log/delivered' , [ManageEmailController::class , 'index'])->name('manage.email.delivered');
        Route::get('email/log/processing' , [ManageEmailController::class , 'index'])->name('manage.email.processing');
        Route::get('email/log/failed' , [ManageEmailController::class , 'index'])->name('manage.email.failed');
        Route::get('email/log/schedule' , [ManageEmailController::class , 'index'])->name('manage.email.schedule');
        Route::get('email/view/{id}' , [ManageEmailController::class , 'viewEmailBody'])->name('email.view');
        Route::post('email/store' , [ManageEmailController::class , 'store'])->name('manage.email.store');
        Route::delete('email/delete' , [ManageEmailController::class , 'deleteLogs'])->name('manage.email.delete');
        Route::delete('email/delete/campaign' , [ManageEmailController::class , 'deleteCampaign'])->name('manage.email.delete.campaign');

        //Mail Configration
        Route::get('mail/configuration' , [MailConfigurationController::class , 'index'])->name('mail.configuration');
        Route::post('mail/update/{id}' , [MailConfigurationController::class , 'mailUpdate'])->name('mail.update');
        Route::get('mail/edit/{id}' , [MailConfigurationController::class , 'edit'])->name('mail.edit');
        Route::post('mail/send/method' , [MailConfigurationController::class , 'sendMailMethod'])->name('mail.send.method');
        // test mail route
        Route::post('mail/test/{id}' , [MailConfigurationController::class , 'mailTester'])->name('mail.test');

        //SMS Gateway
        Route::get('sms/gateway' , [SmsGatewayController::class , 'index'])->name('gateway.sms.index');
        Route::get('sms/gateway/edit/{id}' , [SmsGatewayController::class , 'edit'])->name('gateway.sms.edit');
        Route::post('sms/gateway/update/{id}' , [SmsGatewayController::class , 'update'])->name('sms.gateway.update');
        Route::delete('sms/gateway' , [SmsGatewayController::class , 'delete'])->name('sms.gateway.delete');
        Route::post('sms/default/gateway' , [SmsGatewayController::class , 'defaultGateway'])->name('sms.default.gateway');
        Route::post('sms/gateway/anti-block/toggle' , [SmsGatewayController::class , 'updateAntiBlock'])->name('sms.gateway.anti_block.toggle');

        //android gateway
        Route::get('android/gateway' , [AndroidApiController::class , 'index'])->name('gateway.sms.android.index');
        Route::post('android/gateway/store' , [AndroidApiController::class , 'store'])->name('gateway.sms.android.store');
        Route::post('android/gateway/update' , [AndroidApiController::class , 'update'])->name('gateway.sms.android.update');
        Route::get('android/gateway/sim/list/{id}' , [AndroidApiController::class , 'simList'])->name('gateway.sms.android.sim.index');
        Route::post('android/gateway/delete/' , [AndroidApiController::class , 'delete'])->name('gateway.sms.android.delete');
        Route::post('android/gateway/sim/delete/' , [AndroidApiController::class , 'simNumberDelete'])->name('gateway.sms.android.sim.delete');

        //Sms log
        Route::get('sms/send' , [ManageSMSController::class , 'create'])->name('sms.send');
        Route::get('sms/campaign' , [ManageSMSController::class , 'campaign'])->name('sms.campaign');
        Route::get('sms/all' , [ManageSMSController::class , 'index'])->name('sms.index');
        Route::get('sms/pending' , [ManageSMSController::class , 'index'])->name('sms.pending');
        Route::get('sms/delivered' , [ManageSMSController::class , 'index'])->name('sms.delivered');
        Route::get('sms/failed' , [ManageSMSController::class , 'index'])->name('sms.failed');
        Route::get('sms/schedule' , [ManageSMSController::class , 'index'])->name('sms.schedule');
        Route::get('sms/processing' , [ManageSMSController::class , 'index'])->name('sms.processing');
        Route::post('sms/store' , [ManageSMSController::class , 'store'])->name('sms.store');
        Route::delete('sms/delete' , [ManageSMSController::class , 'deleteLogs'])->name('sms.delete');
        Route::delete('sms/delete/campaign' , [ManageSMSController::class , 'deleteCampaign'])->name('sms.delete.campaign');

        //whatsapp log
        Route::get('whatsapp/send' , [WebController::class , 'create'])->name('whatsapp.send');
        Route::get('whatsapp/all' , [WebController::class , 'index'])->name('whatsapp.index');
        Route::get('whatsapp/campaign' , [WebController::class , 'campaign'])->name('whatsapp.campaign');
        Route::get('whatsapp/pending' , [WebController::class , 'index'])->name('whatsapp.pending');
        Route::get('whatsapp/delivered' , [WebController::class , 'index'])->name('whatsapp.delivered');
        Route::get('whatsapp/failed' , [WebController::class , 'index'])->name('whatsapp.failed');
        Route::get('whatsapp/schedule' , [WebController::class , 'index'])->name('whatsapp.schedule');
        Route::get('whatsapp/processing' , [WebController::class , 'index'])->name('whatsapp.processing');
        Route::post('whatsapp/store' , [WebController::class , 'send'])->name('whatsapp.store');
        Route::delete('whatsapp/delete' , [DesktopController::class , 'deleteLogs'])->name('whatsapp.delete');
        Route::delete('whatsapp/delete/campaign' , [WebController::class , 'deleteCampaign'])->name('whatsapp.delete.campaign');

        // Whatsapp Message Deletes
        Route::get('whatsapp/messages/delete' , [MessageDeleteController::class , 'deleteMessageView'])->name('whatsapp.messages.delete.index');
        Route::delete('whatsapp/messages/delete' , [MessageDeleteController::class , 'deleteMessages'])->name('whatsapp.messages.delete');
        Route::delete('whatsapp/messages/search' , [MessageDeleteController::class , 'searchMessageViaKeywords'])->name('whatsapp.messages.search');
        Route::post('whatsapp/messages/auto-delete/toggle' , [MessageDeleteController::class , 'toggleAutoDelete'])->name('whatsapp.messages.auto_delete.toggle');

        // WhatsApp Desktop Log
        Route::get('whatsapp/desktop/send' , [DesktopController::class , 'create'])->name('desktop.whatsapp.send');
        Route::get('whatsapp/desktop/all' , [DesktopController::class , 'index'])->name('desktop.whatsapp.index');
        Route::get('whatsapp/desktop/campaign' , [WebController::class , 'campaign'])->name('desktop.whatsapp.campaign');
        Route::get('whatsapp/desktop/pending' , [DesktopController::class , 'index'])->name('desktop.whatsapp.pending');
        Route::get('whatsapp/desktop/delivered' , [DesktopController::class , 'index'])->name('desktop.whatsapp.delivered');
        Route::get('whatsapp/desktop/failed' , [DesktopController::class , 'index'])->name('desktop.whatsapp.failed');
        Route::get('whatsapp/desktop/schedule' , [DesktopController::class , 'index'])->name('desktop.whatsapp.schedule');
        Route::get('whatsapp/desktop/processing' , [DesktopController::class , 'index'])->name('desktop.whatsapp.processing');
        Route::post('whatsapp/desktop/store' , [DesktopController::class , 'send'])->name('desktop.whatsapp.store');
        Route::get('whatsapp/desktop/gateway/create' , [DesktopController::class , 'createGateway'])->name('desktop.gateway.whatsapp.create');
        Route::delete('whatsapp/desktop/gateway/delete' , [DesktopController::class , 'deleteGateway'])->name('desktop.gateway.whatsapp.delete');
        Route::put('whatsapp/desktop/pause' , [DesktopController::class , 'pauseCampaign'])->name('desktop.whatsapp.pause');
        Route::delete('whatsapp/desktop/messages/delete/logs' , [DesktopController::class , 'deleteLogs'])->name('desktop.whatsapp.messages.delete_logs');
        Route::post('whatsapp/desktop/update/gateway' , [DesktopController::class , 'updateMessageGateway'])->name('desktop.whatsapp.update-gateway');

        //whatsapp Gateway
        Route::get('whatsapp/gateway/create' , [WebDeviceController::class , 'create'])->name('gateway.whatsapp.create');
        Route::post('whatsapp/gateway/create' , [WebDeviceController::class , 'createSession']);
        Route::get('whatsapp/gateway/edit/{id}' , [WebDeviceController::class , 'edit'])->name('gateway.whatsapp.edit');
        Route::post('whatsapp/gateway/update' , [WebDeviceController::class , 'update'])->name('gateway.whatsapp.update');
        Route::get('whatsapp/gateway/disconnect' , [WebDeviceController::class , 'disconnect'])->name('gateway.whatsapp.disconnect');
        Route::post('whatsapp/gateway/delete' , [WebDeviceController::class , 'delete'])->name('gateway.whatsapp.delete');
        Route::post('whatsapp/gateway/qr-code' , [WebDeviceController::class , 'getQR'])->name('gateway.whatsapp.qrcode');
        Route::post('whatsapp/gateway/update-default' , [DeviceController::class , 'updateAuthenticationDefault'])->name('gateway.whatsapp.update_default');
        Route::post('whatsapp/gateway/update-marketing-default' , [DeviceController::class , 'updateMarketingDefault'])->name('gateway.whatsapp.marketing_update_default');
        Route::post('whatsapp/messages/anti-block/toggle' , [DeviceController::class , 'updateAntiBlock'])->name('whatsapp.messages.anti_block.toggle');

        // Whatsapp Business Routes
        Route::get('whatsapp/business/' , [WhatsappBusinessMessagingController::class , 'index'])->name('business.whatsapp.index');
        Route::get('whatsapp/business/create' , [WhatsappBusinessMessagingController::class , 'create'])->name('business.whatsapp.create');
        Route::get('whatsapp/business/sendeach/create' , [WhatsappBusinessMessagingController::class , 'create'])->name('business.whatsapp.sendeach_create');
        Route::get('whatsapp/business/pending' , [WhatsappBusinessMessagingController::class , 'pending'])->name('business.whatsapp.pending');
        Route::get('whatsapp/business/delivered' , [WhatsappBusinessMessagingController::class , 'success'])->name('business.whatsapp.delivered');
        Route::get('whatsapp/business/schedule' , [WhatsappBusinessMessagingController::class , 'schedule'])->name('business.whatsapp.schedule');
        Route::get('whatsapp/business/failed' , [WhatsappBusinessMessagingController::class , 'failed'])->name('business.whatsapp.failed');
        Route::get('whatsapp/business/processing' , [WhatsappBusinessMessagingController::class , 'processing'])->name('business.whatsapp.processing');
        Route::get('whatsapp/business/search/{scope}' , [WhatsappBusinessMessagingController::class , 'search'])->name('business.whatsapp.search');
        Route::post('whatsapp/business/send' , [WhatsappBusinessMessagingController::class , 'send'])->name('business.whatsapp.send');
        Route::post('whatsapp/business/delete' , [WhatsappBusinessMessagingController::class , 'delete'])->name('business.whatsapp.delete');

        // Whatsapp Business Account
        Route::get('whatsapp/business/account/create' , [WhatsappBusinessAccountController::class , 'create'])->name('business.whatsapp.account.create');
        Route::post('whatsapp/business/account/access-token' , [WhatsappBusinessAccountController::class , 'updateAccessToken'])->name('business.whatsapp.account.access_token.store');
        Route::post('whatsapp/business/account/embedded-access-token' , [WhatsappBusinessAccountController::class , 'updateEmbeddedAccessToken'])->name('business.whatsapp.account.embedded_access_token.store');
        Route::get('whatsapp/business/account/sync' , [WhatsappBusinessAccountController::class , 'sync'])->name('business.whatsapp.account.sync');
        Route::post('whatsapp/business/account/store' , [WhatsappBusinessAccountController::class , 'store'])->name('business.whatsapp.account.store');
        Route::post('whatsapp/business/account/phones/register' , [WhatsappBusinessAccountController::class , 'registerPhone'])->name('business.whatsapp.account.phones.register');
        Route::delete('whatsapp/business/account/delete' , [WhatsappBusinessAccountController::class , 'delete'])->name('business.whatsapp.account.delete');

        // Whatsapp Business Template
        Route::get('whatsapp/business/template' , [WhatsappBusinessTemplateController::class , 'index'])->name('business.whatsapp.template.index');
        Route::post('whatsapp/business/template/store' , [WhatsappBusinessTemplateController::class , 'store'])->name('business.whatsapp.template.store');
        Route::get('whatsapp/business/template/create' , [WhatsappBusinessTemplateController::class , 'create'])->name('business.whatsapp.template.create');
        Route::get('whatsapp/business/template/sync' , [WhatsappBusinessTemplateController::class , 'syncTemplates'])->name('business.whatsapp.template.sync');
        Route::get('whatsapp/business/template/{whatsappTemplate}/edit' , [WhatsappBusinessTemplateController::class , 'edit'])->name('business.whatsapp.template.edit');
        Route::put('whatsapp/business/template/{whatsappTemplate}/update' , [WhatsappBusinessTemplateController::class , 'update'])->name('business.whatsapp.template.update');
        Route::delete('whatsapp/business/template/{whatsappTemplate}/delete' , [WhatsappBusinessTemplateController::class , 'delete'])->name('business.whatsapp.template.delete');
        Route::get('whatsapp/business/template/{whatsappTemplate:whatsapp_template_id}' , [WhatsappBusinessTemplateController::class , 'getTemplate'])->name('business.whatsapp.template.getTemplate');
        Route::post('whatsapp/business/template/update-otp' , [WhatsappBusinessTemplateController::class , 'updateOTPTemplate'])->name('business.whatsapp.template.update_otp_template');

        //Plan
        Route::get('plans' , [PlanController::class , 'create'])->name('plan.create');
        Route::post('plan/store' , [PlanController::class , 'store'])->name('plan.store');
        Route::post('plan/renew' , [PlanController::class , 'subscriptionRenew'])->name('plan.renew');

        Route::get('credits/buy' , [CreditsController::class , 'create'])->name('credits.create');
        Route::post('credits/buy' , [CreditsController::class , 'store'])->name('credits.store');
        Route::get('/credits' , [PlanController::class , 'credits'])->name('plan.subscription');

        //Payment
        Route::get('payment/preview' , [PaymentController::class , 'preview'])->name('payment.preview');
        Route::get('payment/confirm' , [PaymentController::class , 'paymentConfirm'])->name('payment.confirm');
        Route::get('manual/payment/confirm' , [PaymentController::class , 'manualPayment'])->name('manual.payment.confirm');
        Route::post('manual/payment/update' , [PaymentController::class , 'manualPaymentUpdate'])->name('manual.payment.update');

        //Payment Action
        Route::post('ipn/strip' , [PaymentWithStripe::class , 'stripePost'])->name('payment.with.strip');
        Route::post('ipn/paypal' , [PaymentWithPaypal::class , 'postPaymentWithpaypal'])->name('payment.with.paypal');
        Route::get('ipn/paypal/status' , [PaymentWithPaypal::class , 'getPaymentStatus'])->name('payment.paypal.status');
        Route::get('ipn/paystack' , [PaymentWithPayStack::class , 'store'])->name('payment.with.paystack');
        Route::post('ipn/pay/with/sslcommerz' , [SslCommerzPaymentController::class , 'index'])->name('payment.with.ssl');
        Route::post('success' , [SslCommerzPaymentController::class , 'success']);
        Route::post('fail' , [SslCommerzPaymentController::class , 'fail']);
        Route::post('cancel' , [SslCommerzPaymentController::class , 'cancel']);
        Route::post('/ipn' , [SslCommerzPaymentController::class , 'ipn']);


        Route::post('ipn/paytm/process' , [PaymentWithPaytm::class , 'getTransactionToken'])->name('paytm.process');
        Route::post('ipn/paytm/callback' , [PaymentWithPaytm::class , 'ipn'])->name('paytm.ipn');

        Route::get('flutterwave/{trx}/{type}' , [PaymentWithFlutterwave::class , 'callback'])->name('flutterwave.callback');

        Route::post('ipn/razorpay' , [PaymentWithRazorpay::class , 'ipn'])->name('razorpay');

        Route::get('instamojo' , [PaymentWithInstamojo::class , 'process'])->name('instamojo');
        Route::post('ipn/instamojo' , [PaymentWithInstamojo::class , 'ipn'])->name('ipn.instamojo');

        Route::get('ipn/coinbase' , [CoinbaseCommerce::class , 'store'])->name('coinbase');
        Route::any('ipn/callback/coinbase' , [CoinbaseCommerce::class , 'confirmPayment'])->name('callback.coinbase');

        //General Setting
        Route::get('general/setting' , [GeneralSettingController::class , 'index'])->name('general.setting.index');
        Route::post('general/setting/store' , [GeneralSettingController::class , 'store'])->name('general.setting.store');

        //Support Ticket
        Route::get('support/tickets' , [SupportTicketController::class , 'index'])->name('ticket.index');
        Route::get('support/create/new/ticket' , [SupportTicketController::class , 'create'])->name('ticket.create');
        Route::post('support/ticket/store' , [SupportTicketController::class , 'store'])->name('ticket.store');
        Route::get('support/ticket/reply/{id}' , [SupportTicketController::class , 'detail'])->name('ticket.detail');
        Route::post('support/ticket/reply/{id}' , [SupportTicketController::class , 'ticketReply'])->name('ticket.reply');
        Route::post('support/closed/{id}' , [SupportTicketController::class , 'closedTicket'])->name('ticket.closed');
        Route::get('support/ticket/file/download/{id}' , [SupportTicketController::class , 'supportTicketDownlode'])->name('ticket.file.download');

        Route::controller(\App\Http\Controllers\AiBotController::class)
            ->prefix('/OpenAI')->name('ai_bots.')->group(function () {
                Route::get('/' , 'index')->name('index');
                Route::get('fine-tune/dataset/chats' , 'getChatsAsFineTuneDataSet')->name('fine_tune.dataset.chats');
                Route::put('/' , 'update')->name('prompt_update');
                Route::put('/enable-pi-ai' , 'enablePIAI')->name('enablePIAI');
                Route::put('/disable-pi-ai' , 'disablePIAI')->name('disablePIAI');
                Route::post('/parse-business-details' , 'parseBusinessDetails')->name('parse_business_details');
                Route::post('/summarize-business-details' , 'summarizeBusinessDetails')->name('summarize_business_details');
                Route::put('/business' , 'updateBusinessInformation')->name('business.update');
                Route::get('/advanced-configurations' , 'advancedSettings')->name('advanced_configurations');
                Route::get('/cancel-fine-tune' , 'cancelFineTune')->name('cancel_fine_tune');
                Route::post('/spin-message' , 'spinMessage')->name('spin_message');
            });

        Route::controller(\App\Http\Controllers\CustomReplyController::class)
            ->prefix('/OpenAI/custom-replies')->name('ai_bots.custom_replies.')->group(function () {
                Route::get('/' , 'index')->name('index');
                Route::get('/import-from-chats' , 'importFromChats')->name('import');
                Route::put('/' , 'update')->name('update');
                Route::get('/delete' , 'delete')->name('delete');
                Route::post('/update-keywords-from-open-ai' , 'updateKeywordsFromAi')
                    ->name('fetch_keywords_from_ai');
                Route::post('/connect-to-human' , 'connectToHuman')
                    ->name('connect_to_human');
                Route::post('/toggle-partial-match' , 'togglePartialMatch')
                    ->name('toggle_partial_match');
            });

        Route::controller(\App\Http\Controllers\Facebook\MessengerController::class)->prefix('facebook/messenger/')
            ->name('facebook.messenger.')->group(function () {
                Route::get('index' , 'index')->name('index');
                Route::put('update/openai-bot' , 'updateOpenAiBot')->name('update.open_ai_bot');
                Route::put('update/greetings-text' , 'updateOpenAiBot')->name('update.greetings_text');
                Route::put('update/openai-bot' , 'updateOpenAiBot')->name('update.open_ai_bot');
                Route::delete('disconnect' , 'disconnect')->name('disconnect');
            });

        Route::controller(\App\Http\Controllers\FacebookLoginController::class)
            ->prefix('facebook/login')->name('facebook.login.')->group(function () {
                Route::post('page' , 'redirectPageCallback')->name('page');
            });

        Route::get('OpenAi/share-link' , [\App\Http\Controllers\ChatShareController::class , 'shareLink'])
            ->name('ai_bots.share_link');
        Route::post('OpenAi/generate-share-link' , [\App\Http\Controllers\ChatShareController::class , 'generateShareName'])
            ->name('ai_bots.generate.share_link');
        Route::put('OpenAi/greetings-text' , [\App\Http\Controllers\ChatShareController::class , 'updateGreetingsText'])
            ->name('ai_bots.greetings_text.update');

        Route::controller(\App\Http\Controllers\Whatsapp\BotController::class)->prefix('whatsapp/bots')
            ->name('whatsapp.bot.')->group(function () {
                Route::get('' , 'index')->name('index');
                Route::put('' , 'update')->name('update');
            });
    });
});


Route::get('/language/change/{lang?}' , [FrontendController::class , 'languageChange'])->name('language.change');
Route::get('/default/image/{size}' , [FrontendController::class , 'defaultImageCreate'])->name('default.image');
Route::get('email/contact/demo/file' , [FrontendController::class , 'demoImportFile'])->name('email.contact.demo.import');
Route::get('sms/demo/import/file' , [FrontendController::class , 'demoImportFilesms'])->name('phone.book.demo.import.file');


Route::get('demo/file/downlode/{extension}' , [FrontendController::class , 'demoFileDownlode'])->name('demo.file.downlode');

Route::get('demo/email/file/downlode/{extension}' , [FrontendController::class , 'demoEmailFileDownlode'])->name('demo.email.file.downlode');

Route::post('/contact-us' , [\App\Http\Controllers\ContactSupportController::class , 'send'])->name('contact_support.send');

Route::withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)->middleware('cors')->group(function (){
    Route::controller(WebChatController::class)
        ->prefix('web-chats')->name('web_chats.')->group(function () {
            Route::get('' , 'index')->name('index');
            Route::post('send' , 'sendMessage')->name('send');
            Route::get('unread' , 'unreadMessages')->name('unreadMessages');
        });
});


Route::prefix('/plugins')
    ->controller(\App\Http\Controllers\PluginController::class)
    ->name('plugins.')->group(function () {
        Route::get('/chat.js' , 'chat')->name('chat.js');
    });

Route::get('chat/{user}' , [\App\Http\Controllers\ChatShareController::class , 'chatView'])->name('ai_bots.public.chat');

Route::controller(\App\Http\Controllers\Facebook\MessengerController::class)->prefix('facebook/messenger/')
    ->name('facebook.messenger.')->group(function () {
        Route::post('webhooks' , 'webhooks')->name('webhooks');
        Route::get('webhooks' , 'webhookSubscribe')->name('webhookSubscribe');
    });

Route::controller(\App\Http\Controllers\FacebookLoginController::class)
    ->prefix('facebook')->name('facebook.')->group(function () {
        Route::any('deauthorize' , 'deauthorize')->name('deauthorize');
        Route::any('delete' , 'delete')->name('delete');
        Route::any('login' , 'login')->name('login');
    });

//Route::get('test-mail', function (){
//    return new \App\Mail\WhatsappDesktopHealthFailure(\App\Models\User::first(), \App\Models\UserWindowsToken::first());
//});

Route::get('/chat' , function () {
    return '<html><head><script src="https://code.jquery.com/jquery-3.7.0.min.js"
            integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script></head><body>
            <button class="btn text-decoration-none whatsapp_float p-0" id="sendeach-chats-button"><i
        class="bi bi-chat-text" style="font-size: 30px"></i></button>

<script src=' . route('plugins.chat.js' , ['access_token' => '175|4H0QeLzc1xEpvTLvjkd65yQgjbGuDs7c6S1yfT9e']) . '></script></body></html>';
});

Route::post('whatsapp/gateway/qr-callback' , [WebDeviceController::class , 'webhook'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
    ->name('gateway.whatsapp.qr_callback');

Route::post('server/update/code', function (){
    \Illuminate\Support\Facades\Artisan::call('update:code');

    return 'Success';
});
