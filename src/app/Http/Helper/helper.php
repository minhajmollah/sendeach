<?php

use App\Jobs\ProcessWhatsapp;
use App\Jobs\ProcessWhatsappMessage;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\User;
use App\Models\UserWindowsToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Models\WhatsappPhoneNumber;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

if (!function_exists('strDec')) {
    function strDec($str)
    {
        return base64_decode($str, true);
    }
}

function StoreImage($file, $location, $size = null, $removefile = null)
{
    if (!file_exists($location)) {
        mkdir($location, 0755, true);
    }
    if ($removefile) {
        if (file_exists($location . '/' . $removefile) && is_file($location . '/' . $removefile)) {
            @unlink($location . '/' . $removefile);
        }
    }
    $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
    $image = Image::make(file_get_contents($file));
    if (isset($size)) {
        $size = explode('x', strtolower($size));
        $image->resize($size[0], $size[1]);
    }
    $image->save($location . '/' . $filename);
    return $filename;
}

function paginateNumber($number = 20)
{
    return $number;
}

function build_post_fields($data, $existingKeys = '', &$returnArray = [])
{
    if (($data instanceof CURLFile) or !(is_array($data) or is_object($data))) {
        $returnArray[$existingKeys] = $data;
        return $returnArray;
    } else {
        foreach ($data as $key => $item) {
            build_post_fields($item, $existingKeys ? $existingKeys . "[$key]" : $key, $returnArray);
        }
        return $returnArray;
    }
}

function filePath()
{
    $path['profile'] = [
        'admin' => [
            'path' => 'assets/dashboard/image/profile',
            'size' => '400x400'
        ],
        'user' => [
            'path' => 'assets/images/user/profile',
            'size' => '400x400'
        ],
    ];
    $path['payment_file'] = [
        'path' => 'assets/payment/data',
    ];
    $path['email_uploaded_file'] = [
        'path' => 'assets/email_uploaded_file',
    ];
    $path['payment_method'] = [
        'path' => 'assets/images/paymentmethod',
        'size' => '600x600'
    ];
    $path['site_logo'] = [
        'path' => 'assets/images/logoIcon',
    ];
    $path['ticket'] = [
        'path' => 'assets/file/ticket',
    ];
    $path['favicon'] = [
        'size' => '128x128',
    ];
    $path['demo'] = [
        'path' => 'assets/file/sms',
        'path_email' => 'assets/file/email',
    ];
    $path['whatsapp'] = [
        'path_document' => 'assets/file/whatsapp/document',
        'path_audio' => 'assets/file/whatsapp/audio',
        'path_image' => 'assets/file/whatsapp/image',
        'path_video' => 'assets/file/whatsapp/video',
    ];
    return $path;
}

function menuActive($routeName, $type = null)
{
    if ($type == 'A') {
        $class = 'auto_height_menu';
    } elseif ($type == 1) {
        $class = 'first_first_menu';
    } elseif ($type == 2) {
        $class = 'first_second_menu';
    } elseif ($type == 3) {
        $class = 'first_third_menu';
    } elseif ($type == 4) {
        $class = 'first_fourth_menu';
    } elseif ($type == 5) {
        $class = 'first_fivth_menu';
    } elseif ($type == 6) {
        $class = 'first_sixth_menu';
    } elseif ($type == 8) {
        $class = 'first_eight_menu';
    } elseif ($type == 9) {
        $class = 'first_nine_menu';
    } elseif ($type == 10) {
        $class = 'first_ten_menu';
    } elseif ($type == 11) {
        $class = 'first_eleven_menu';
    } elseif ($type == 12) {
        $class = 'first_twelve_menu';
    } elseif ($type == 13) {
        $class = 'first_thirty_menu';
    } elseif ($type == 14) {
        $class = 'first_fourteen_menu';
    } elseif ($type == 20) {
        $class = 'first_twenty_menu';
    } elseif ($type == 22) {
        $class = 'first_twenty_two_menu';
    } elseif ($type == 23) {
        $class = 'first_twenty_three_menu';
    } elseif ($type == 24) {
        $class = 'first_first_menu_twenty_four';
    } elseif ($type == 25) {
        $class = 'first_first_menu_twenty_five';
    } elseif ($type == 26) {
        $class = 'first_twenty_six_menu';
    } else {
        $class = 'active';
    }
    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) {
                return $class;
            }
        }
    } elseif (request()->routeIs($routeName)) {
        return $class;
    }
}

function sidebarMenuActive($routeName)
{
    $class = 'active';
    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) {
                return $class;
            }
        }
    }
}

function shortAmount($amount, $length = 2)
{
    $amount = round($amount, $length);
    return $amount;

}

function diffForHumans($date)
{
    return Carbon::parse($date)->diffForHumans();
}


function getDateTime($date, $format = 'Y-m-d h:i A')
{
    return Carbon::parse($date)->translatedFormat($format);
}

function slug($name)
{
    return Str::slug($name);
}

function trxNumber()
{
    $random = strtoupper(Str::random(10));
    return $random;
}

/**
 * Generate a random number of given length which padded left side with 0
 *
 * @param int $digits 4
 * @return string
 */
function randomOtp(int $digits = 4)
{
    return str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
}

function randomNumber()
{
    return mt_rand(1, 10000000);
}

function uploadNewFile($file, $location, $old = null)
{
    if (!file_exists($location)) {
        mkdir($location, 0755, true);
    }
    if (!$location) throw new Exception('File could not been created.');
    if ($old) {
        if (file_exists($location . '/' . $old) && is_file($location . '/' . $old)) {
            @unlink($old . '/' . $old);
        }
    }
    $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
    $file->move($location, $filename);
    return $filename;
}

function showImage($image, $size = null)
{
    if (file_exists($image) && is_file($image)) {
        return asset($image);
    }
    if ($size) {
        return route('default.image', $size);
    }
    return (asset('assets/images/default.jpg'));
}

function number($amount, $length = 2)
{
    $amount = round($amount, $length);
    return $amount;
}

function textSorted($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}

function limit($text, $length)
{
    $value = Str::limit($text, $length);
    return $value;
}

function serverExtensionCheck($name)
{
    if (!extension_loaded($name)) {
        return $response = false;
    } else {
        return $response = true;
    }
}

function checkFolderPermission($name)
{
    $perm = substr(sprintf('%o', fileperms($name)), -4);
    if ($perm >= '0775') {
        $response = true;
    } else {
        $response = false;
    }
    return $response;
}

function charactersLeft()
{
    $user = auth()->user();
    return $user->credit * 160;
}

function charactersLeftWa()
{
    return 1000;
    $user = auth()->user();
    return $user->whatsapp_credit * 320;
}


function curlContent($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


function labelName($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}


function uploadImage($file, $location, $size = null, $old = null, $thumb = null)
{

    if (!file_exists($location)) {
        mkdir($location, 0755, true);
    }
    if ($old) {
        if (file_exists($location . '/' . $old) && is_file($location . '/' . $old)) {
            @unlink($location . '/' . $old);
        }
    }
    $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
    $image = Image::make($file);
    if ($size) {
        $size = explode('x', strtolower($size));
        $image->resize($size[0], $size[1]);
    }
    $image->save($location . '/' . $filename);
    if ($thumb) {
        $thumb = explode('x', $thumb);
        Image::make($file)->resize($thumb[0], $thumb[1])->save($location . '/thumb_' . $filename);
    }
    return $filename;
}

function buildDomDocument($text)
{
    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $text);
    libxml_use_internal_errors(false);
    $imageFile = $dom->getElementsByTagName('img');
    if ($imageFile) {
        foreach ($imageFile as $item => $image) {
            $data = $image->getAttribute('src');
            $check_b64_data = preg_match("/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).base64,.*/", $data);
            if ($check_b64_data) {
                list($type, $data) = explode(';', $data);
                list(, $data) = explode(',', $data);
                $imgeData = base64_decode($data);
                $image_name = time() . $item . '.png';
                $save_path = filePath()['email_uploaded_file']['path'];
                try {
                    Image::make($imgeData)->save($save_path . '/' . $image_name);
                    $getpath = asset('assets/email_uploaded_file/' . $image_name);
                    $image->removeAttribute('src');
                    $image->setAttribute('src', $getpath);
                } catch (Exception $e) {

                }
            }
        }
    }
    $html = $dom->saveHTML();
    $html = html_entity_decode($html, ENT_COMPAT, 'UTF-8');
    return $html;
}

/**
 * language translation
 *
 * @param [string] $keyWord
 * @param [string] $langCode
 * @return $data[$lang_key]
 */
function translate($keyWord, $langCode = null)
{
    try {
        if ($langCode == null) {
            $langCode = App::getLocale();
            if ($langCode == 'En') {
                $langCode = 'en';
            }
        }
        $lang_key = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($keyWord)));
        if ($langCode) {
            $localeTranslateData = file_get_contents(resource_path(config('constants.options.langFilePath')) . $langCode . '.json');
            $localeTranslateDataArray = json_decode($localeTranslateData, true);
            if (is_array($localeTranslateDataArray)) {
                if (!array_key_exists($lang_key, $localeTranslateDataArray)) {
                    $localeTranslateDataArray[$lang_key] = $keyWord;
                    $path = resource_path(config('constants.options.langFilePath')) . $langCode . '.json';
                    File::put($path, json_encode($localeTranslateDataArray));
                }
                $data = $localeTranslateDataArray[$lang_key];
            } else {
                $data = $keyWord;
            }
        }
    } catch (\Exception $ex) {
        $data = $keyWord;
    }
    return $data;
}


/**
 * get lang file
 *
 */
function getLangFile($langCode)
{
    return file_get_contents(resource_path(config('constants.options.langFilePath')) . $langCode . '.json');
}


/**
 *
 */
function offensiveMsgBlock($requestMessage)
{
    $path = base_path('lang/globalworld/offensive.json');
    $offensiveData = json_decode(file_get_contents($path), true);
    $message = explode(' ', $requestMessage);
    foreach ($offensiveData as $key => $value) {
        foreach ($message as $msgKey => $item) {
            if (strtolower($item) == strtolower($key)) {
                $message[$msgKey] = $value;
                Session::put('offsensiveNotify', "& We found some offsensive word");
            }
        }
    }
    $message = implode(' ', $message);

    return $message;
}

/**
 * Decode the login token into OTP and Find User
 *
 * @param string $token
 * @return array
 * @throws Exception
 */
function decode_auth_token(string $token)
{
    if (!$token) {
        throw ValidationException::withMessages(["Verify token missing from request."]);
    }
    $decoded_token = json_decode(base64_decode($token), true);

    if (!isset($decoded_token['id'])) {
        throw ValidationException::withMessages(["Malformed verify token."]);
    }

    $user = User::where('id', $decoded_token['id'])->first();
    if (!$user || !$user->otp || !$user->otp_time || !$user->otp_time->addMinutes(10)->gt(now())) {
        throw ValidationException::withMessages(["Malformed verify token. OTP Expired."]);
    }

    return [
        'phone' => $user->phone,
        'user' => $user,
        'otp_time' => $user->otp_time,
    ];
}

/**
 * Generate otp and send to a whatsapp number
 *
 * @param User $user
 * @param null $whatsappGateway
 * @return User
 */
function generate_and_send_otp(User $user, $whatsappGateway = null)
{
    // Generate OTP & save OTP on user table
    $otp = randomOtp(6);

    $user->update([
        'otp' => $otp,
        'otp_time' => now(),
    ]);

    try {
        send_otp($otp, $user->phone, $user->email, $whatsappGateway, $user);
    } catch (Exception $exception) {
        logger()->error($exception->getMessage());
        logger()->error($exception->getTraceAsString());
    }

    return $user;
}

function send_otp($otp, $phone, $email, $whatsappGateway = null, $user = null)
{


    // OTP Message string
    $messages = "Here is your SendEach OTP [{$otp}]. It was generated on " . now()->format("M d, Y h:i A") . ". Please don't share it with anyone.";

    $defaultWhatsappGateway = GeneralSetting::admin()->default_whatsapp_gateway;

    try {
        if ($defaultWhatsappGateway == WhatsappLog::GATEWAY_BUSINESS) {
            sendOTPViaBusinessGateway(WhatsappPhoneNumber::get_admin_phone(), $phone, WhatsappTemplate::get_otp_template(), $otp);
        } else if ($defaultWhatsappGateway == WhatsappLog::GATEWAY_DESKTOP) {
            sendDesktopMessage([$phone], $whatsappGateway, null, $messages);
        } else {
            sendWebMessage([$phone], $whatsappGateway, null, $messages);
        }
    } catch (Exception $exception) {
        logger()->error($exception->getMessage());
        logger()->error($exception->getTraceAsString());
    }

    if ($email) {

        $message = (new \App\Mail\SendOTP($otp, now()->format("M d, Y h:i A"), is_string($user) ?: ($user?->name ?: $phone)))->render();

      $mailConfig=  MailConfiguration::query()->where('user_type', 'admin')->where('default_use', 1)
      ->orWhere('default_use', 2)->first();


      $mailConfig->sendMail("Your One-Time Password (OTP) for SendEach", $message, $email);
    }
}

function sendOTPViaBusinessGateway(WhatsappPhoneNumber $phone, $to, WhatsappTemplate $template, $otp): WhatsappLog
{
    // Generate whatsapp log
    $log = WhatsappLog::startBusinessLog($to, $template, [], [$otp], $phone, null, $phone->whatsapp_account);
    $log->initiated_time = now();

    // Dispatch whatsapp message job
    ProcessWhatsappMessage::dispatch($to, $phone->whatsapp_phone_number_id, $log->id, $template->whatsapp_template_id, [$otp])
        ->delay(now()->addSeconds(1))->onQueue('otp');

    return $log;
}

function sendWebMessage($recipients, $whatsapp_device, $user, $message): JsonResponse|array
{
    $whatsappGateway = WhatsappDevice::query()->where('user_type', $user ? 'user' : 'admin')
        ->when($user, function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })
        ->where('status', WhatsappDevice::STATUS_CONNECTED);

    if ($whatsapp_device) {
        $whatsappGateway = $whatsappGateway->where('id', $whatsapp_device)->first();
    } else {
        $whatsappGateway = $whatsappGateway->inRandomOrder()->first();
    }

    if (!$whatsappGateway) {
        return response()->json([
            'status' => 'error',
            'data' => ['message' => 'No Whatsapp Gateway Added'],
        ], 400);
    }

    $logs = [];

    foreach ($recipients as $to) {
        $log = WhatsappLog::startLog($whatsappGateway, $user, $to, $message, WhatsappLog::GATEWAY_WEB);

        ProcessWhatsapp::dispatch($message, $to, $log->id, [])->delay(now()->addSeconds(1))->onQueue('otp');

        $logs[] = $log;
    }

    return $logs;
}

function sendDesktopMessage($recipients, $whatsapp_device, $user, $message): JsonResponse|array
{
    if (UserWindowsToken::query()->when($user, fn($q) => $q->where('user_id', $user->id))
        ->unless($user, fn($q) => $q->where('user_type', 'admin'))
        ->where('status', WhatsappDevice::STATUS_CONNECTED)->doesntExist()) {
        return response()->json([
            'status' => 'error',
            'data' => ['message' => 'No Connected Whatsapp PC Device Found. Please connect at least one device.',],
        ], 400);
    }

    if ($whatsapp_device) $whatsapp_device = UserWindowsToken::query()
        ->when($user, fn($q) => $q->where('user_id', $user->id))
        ->where('status', WhatsappDevice::STATUS_CONNECTED)
        ->where('device_id', $whatsapp_device)->first();

    if (!$whatsapp_device) $whatsapp_device = UserWindowsToken::query()
        ->when($user, fn($q) => $q->where('user_id', $user->id))
        ->where('status', WhatsappDevice::STATUS_CONNECTED)->inRandomOrder()->first();

    $logs = [];

    foreach ($recipients as $to) {
        $log = WhatsappLog::startLog($whatsapp_device, $user ?: $whatsapp_device->user_id, $to, $message, WhatsappLog::GATEWAY_DESKTOP);
        $logs[] = $log;
    }

    return $logs;
}

function constructUrl($baseUrl, $paths = []): string
{
    return $baseUrl . '/' . join('/', $paths);
}

function generateKeywords($str)
{
    $min_word_length = 3;
    $avoid = ['the', 'to', 'i', 'am', 'is', 'are', 'he', 'she', 'a', 'an', 'and', 'here', 'there', 'can', 'could', 'were', 'has', 'have', 'had', 'been', 'welcome', 'of', 'home', '&nbsp;', '&ldquo;', 'words', 'into', 'this', 'there'];
    $strip_arr = [",", ".", ";", ":", "\"", "'", "“", "”", "(", ")", "!", "?"];
    $str_clean = str_replace($strip_arr, "", $str);
    $str_arr = explode(' ', $str_clean);
    $clean_arr = [];
    foreach ($str_arr as $word) {
        if (strlen($word) > $min_word_length) {
            $word = strtolower($word);
            if (!in_array($word, $avoid)) {
                $clean_arr[] = $word;
            }
        }
    }
    return implode(',', $clean_arr);
}

function getAccessAttempts($keyUser = null, $attempt = false): int
{
    $keyUser = $keyUser ?: request()->route()->getName() . ':' . request()->getClientIp();

    if ($attempt) {
        cache()->put($keyUser, cache()->get($keyUser, 0) + 1);
    }

    return cache()->get($keyUser, 0);
}
