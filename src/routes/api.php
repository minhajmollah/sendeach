<?php

use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ManageDesktopWhatsappController;
use App\Http\Controllers\Api\ManageSMSController;
use App\Http\Controllers\Api\ManageWhatsappController;
use App\Http\Controllers\Api\PluginOTPController;
use App\Http\Controllers\User\AdvancedSettingController;
use App\Http\Controllers\Api\RegisteredUserAPIController;
use App\Http\Controllers\Api\UserWindowsTokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::post('login', [PassportAuthController::class, 'login']);
Route::get('/restrict/domain',[AdvancedSettingController::class,'domain'])->name('domain');

Route::get('init' , [ManageSMSController::class , 'init']);
Route::post('register' , [RegisteredUserAPIController::class , 'verify'])->name('api.register');
Route::post('login' , [LoginController::class , 'login'])->name('app.login');
Route::post('verify_otp_login' , [LoginController::class , 'verify_otp_login'])->name('app.verify_otp_login');

// Route::middleware('auth:api')->group(function () {
//     Route::post('sim/update', [ManageSMSController::class, 'simInfo']);
//     Route::post('sms/logs', [ManageSMSController::class, 'smsfind']);
//     Route::post('sms/status/update', [ManageSMSController::class, 'smsStatusUpdate']);
//     Route::post('sim/status/update', [ManageSMSController::class, 'simClosed']);
// });

Route::middleware(['auth:sanctum', 'ability:'.\App\Models\User::ABILITY_SEND_WHATSAPP.','.\App\Models\User::ABILITY_SEND_SMS])
    ->name('api.')->group(function () {
    //return $request->user();

    Route::prefix('windows-tokens')->name('windows-token.')->group(function () {
        Route::get('/' , [UserWindowsTokenController::class , 'index'])->name('index');
        Route::get('/{token:device_id}' , [UserWindowsTokenController::class , 'show'])->name('show');
        Route::post('/' , [UserWindowsTokenController::class , 'store'])->name('store');
        Route::patch('/{token:device_id}' , [UserWindowsTokenController::class , 'update'])->name('update');
        Route::delete('/{token:device_id}' , [UserWindowsTokenController::class , 'destroy'])->name('destroy');
    });

    //store fcm token
    Route::post('store_fcm_token' , [CommonController::class , 'storeFcmToken'])->name('app.store_fcm_token');
    Route::post('exist_fcm_token' , [CommonController::class , 'existFcmToken'])->name('app.exist_fcm_token');
    Route::post('exist_device_id' , [CommonController::class , 'existDeviceID'])->name('app.exist_device_id');
    Route::post('send_sms' , [ManageSMSController::class , 'sendSMS'])->name('app.send_sms');
    Route::get('sms/pull-pending/app' , [ManageSMSController::class , 'pullMessages'])->name('app.sms.pull');
    Route::post('delete_fcm_token' , [CommonController::class , 'deleteFcmToken'])->name('app.delete_fcm_token');
    Route::post('update-status' , [ManageSMSController::class , 'smsStatusUpdate']);
    Route::get('/user' , function () {
        return response()->json([
            'user' => auth()->user() ,
        ]);
    });

    Route::prefix('/email')->group(function () {
        Route::post('send' , [\App\Http\Controllers\Api\ManageEmailController::class , 'sendEmail'])->name('email.send');
    });

    Route::controller(\App\Http\Controllers\Api\ManagePhoneGroupController::class)
        ->prefix('phone-groups')->name('groups.phones.')->group(function () {
            Route::get('' , 'allGroups');
            Route::post('' , 'updateOrCreateGroup');
            Route::delete('' , 'deleteGroup');

            Route::get('/{groupId}/phones' , 'contacts');
            Route::post('/{groupId}/phones' , 'addContact');
            Route::delete('/{groupId}/phones' , 'deleteContact');

        });

    Route::controller(\App\Http\Controllers\Api\ManageEmailGroupController::class)
        ->prefix('email-groups')->name('groups.emails.')->group(function () {
            Route::get('' , 'allGroups');
            Route::post('' , 'updateOrCreateGroup');
            Route::delete('' , 'deleteGroup');

            Route::get('/{groupId}/emails' , 'contacts');
            Route::post('/{groupId}/emails' , 'addContact');
            Route::delete('/{groupId}/emails' , 'deleteContact');

        });

    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::post('/business/send' , [ManageWhatsappController::class , 'sendBusiness'])
            ->name('business.send');
        Route::post('/send' , [ManageWhatsappController::class , 'sendMessage'])
            ->name('web.send');
        Route::post('/otp/send' , [ManageWhatsappController::class , 'sendOTP'])
            ->name('web.otp.send');
        Route::post('/otp/verify' , [ManageWhatsappController::class , 'verifyOTP'])
            ->name('web.otp.verify');
        Route::get('/messages' , [ManageWhatsappController::class , 'messages'])
            ->name('messages.index');

        Route::get('/phones' , [ManageWhatsappController::class , 'getPhones'])
            ->name('phones.index');
        Route::get('/templates' , [ManageWhatsappController::class , 'getTemplates'])
            ->name('templates.index');

        Route::withoutMiddleware('throttle:api')->controller(ManageDesktopWhatsappController::class)
            ->group(function () {
                Route::get('/messages/desktop' , 'desktopMessages')
                    ->name('messages.desktop.index');

                Route::put('/messages' , 'updateMessage')
                    ->name('messages.update');

                Route::get('/desktop/messages/delete' , 'pullMessagesToDelete')
                    ->name('messages.desktop.delete');

                Route::put('/desktop/messages/delete' , 'updateDeleteStatus')
                    ->name('messages.desktop.delete.update');

                Route::get('/desktop/delete-keywords' , 'pullMessageDeleteKeywords')
                    ->name('messages.desktop.delete-keywords');

                Route::put('/desktop/delete-keywords' , 'updateKeywordDeleteStatus')
                    ->name('messages.desktop.delete-keywords.update');

                Route::get('/desktop/is-auto-delete-enabled' , 'isUserEnabledAutoDelete')
                    ->name('messages.desktop.enabled_auto_delete');
            });

    });

    Route::prefix('sms')->group(function () {
        // Route::post('/send', [ManageSMSController::class, 'store']);
    });
});

Route::get('whatsapp/desktop/version' , [ManageDesktopWhatsappController::class , 'getDesktopVersion'])->name('messages.desktop.version');
Route::put('whatsapp/desktop/version' , [ManageDesktopWhatsappController::class , 'uploadDesktopAPP'])->name('messages.desktop.version_update');

Route::middleware(['throttle:public-service'])->name('api.')->prefix('public')->group(function () {
    Route::post('whatsapp/otp/send' , [PluginOTPController::class , 'sendOTP'])->name('public.whatsapp.otp.send');
    Route::post('email/otp/send' , [PluginOTPController::class , 'sendOTPViaEmail'])->name('public.email.otp.send');
});
