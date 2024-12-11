<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WebBotRestriction;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdvancedSettingController extends Controller
{
   public function index(){
    $title='Advanced Setting';
    $user_id=auth()->user()->id;
    $restrictions= WebBotRestriction::where('user_id',$user_id)->get();
    $hasRestrictions = count($restrictions) > 0 ? true : false;
    return view('user.setting.advance-setting',compact('title','restrictions','hasRestrictions'));
   }
   public function save(Request $request){
    $user_id = Auth::user()->id;
    Validator::extend('unique_domain_for_user', function ($attribute, $value, $parameters, $validator) {
        $userId = auth()->id();
        $domain = parse_url('http://' . $value, PHP_URL_HOST); // Prepend 'http://' to the domain before extracting

        // Check if the domain is taken by another user
        $exists = DB::table('web_bot_restrictions')
                    ->where('domain_name', $domain)
                    ->where('user_id', '<>', $userId)
                    ->exists();

  // Dynamically set the error message with the domain name
  $validator->addReplacer('unique_domain_for_user', function ($message, $attribute, $rule, $parameters) use ($domain) {
    return str_replace(':domain', $domain, $message .'If you have any query please contact with support');
});

return !$exists;
}, 'This domain :domain is already taken.');

    $request->validate([
        'doamin_name' => 'required|array',
        'doamin_name.*' => [
            'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}(\/[\w\-\/]*\*?)?$/i',
            'unique_domain_for_user',
        ],
    ]);




    // Check if at least one record exists for this user
    $hasRecords = WebBotRestriction::where('user_id', $user_id)->exists();

    // If records exist, delete them (this is one approach, adjust as necessary)
    if ($hasRecords) {
        WebBotRestriction::where('user_id', $user_id)->delete();
    }

    // Loop through each domain name and create a new record
    foreach ($request->input('doamin_name') as $domain) {
        WebBotRestriction::create([
            'domain_name' => $domain,
            'status' => $request->status ? $request->status: 0,
            'user_id' => $user_id
        ]);
    }








    $notify[] = ['success', 'Advanced setting set successfully'];
    return back()->withNotify($notify);
   }
   public function domain(Request $request){
    $domain = $request->input('domain');
    $segments = explode('/', $domain);
    $restriction = null;

    // Loop through the segments and try to match with wildcard in the database
    for ($i = 1; $i <= count($segments); $i++) {
        $partialDomainWithSlash = implode('/', array_slice($segments, 0, $i)) . '/*';
        $restriction = WebBotRestriction::where('domain_name', $partialDomainWithSlash)->first();

        if ($restriction) {
            break;
        }

        $partialDomainWithoutSlash = rtrim($partialDomainWithSlash, '/*') . '*';
        $restriction = WebBotRestriction::where('domain_name', $partialDomainWithoutSlash)->first();

        if ($restriction) {
            break;
        }
    }
// If no match found, attempt to check without trailing slash if it exists, or with a trailing slash if it doesn't.
if (!$restriction) {
    if (substr($domain, -1) == "/") {
        $alternativeDomain = rtrim($domain, '/');
    } else {
        $alternativeDomain = $domain . '/';
    }
    $restriction = WebBotRestriction::where('domain_name', $alternativeDomain)->orWhere('domain_name', $domain)->first();
}





    if (!$restriction) {

   // Extract just the domain from the request URL
$parsedDomain = parse_url('http://' . $domain); // prepend with 'http://' to ensure correct parsing
$requestDomainName = $parsedDomain['host'] ?? '';

$matchedEntry = null;

// Fetch all entries from the database


$allRestrictions = WebBotRestriction::all();

foreach ($allRestrictions as $entry) {
    $entryDomain = parse_url('http://' . $entry->domain_name, PHP_URL_HOST); // prepend with 'http://'

    if ($requestDomainName == $entryDomain) {
       if($entry->status==1){
        $restriction = $entry;
        $restriction['status'] = 4;
        break;
       }else{
        return response()->json($restriction);
       }
    }

    }

    }
    return response()->json($restriction);




















   }
   public function updateStatus(Request $request)
{
    // Validate input
    $request->validate([
        'status' => 'required|in:0,1,2,3',
    ]);

    $user_id = Auth::user()->id;

    // Assuming you're updating the existing WebBotRestriction record
    $restriction = WebBotRestriction::where('user_id', $user_id)->update(['status'=>$request->status]);



    return response()->json($restriction);
}

}
