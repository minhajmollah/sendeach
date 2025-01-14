<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel server requirements, you can add as many
    | as your application require, we check if the extension is enabled
    | by looping through the array and run "extension_loaded" on it.
    |
    */
    'core' => [
        'appVersion' => '1.3.7' ,
        'minPhpVersion' => '7.4.0' ,
        'web_api_url' => env('WHATSAPP_WEB_API_URL' , 'http://144.217.88.62:3000') ,
        'web_api_key' => env('WHATSAPP_WEB_API_KEY') ,
    ] ,
    'greetings_text_postfix' => 'Chatting will be assisted by SendEach AI Chatbot.' ,
    'final' => [
        'key' => true ,
        'publish' => false ,
    ] ,
    'requirements' => [
        'php' => [
            'openssl' ,
            'pdo' ,
            'mbstring' ,
            'tokenizer' ,
            'JSON' ,
            'cURL' ,
        ] ,
        'apache' => [
            'mod_rewrite' ,
        ] ,
    ] ,

    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        '.env' => '666' ,
        'storage/framework/' => '775' ,
        'storage/logs/' => '775' ,
        'bootstrap/cache/' => '775' ,
    ] ,

    /*
    |--------------------------------------------------------------------------
    | Environment Form Wizard Validation Rules & Messages
    |--------------------------------------------------------------------------
    |
    | This are the default form field validation rules. Available Rules:
    |
    */
    'environment' => [
        'form' => [
            'rules' => [
                'app_name' => 'required|string|max:50' ,
                'environment' => 'required|string|max:50' ,
                'environment_custom' => 'required_if:environment,other|max:50' ,
                'app_debug' => 'required|string' ,
                'app_log_level' => 'required|string|max:50' ,
                'app_url' => 'required|url' ,
                'database_connection' => 'required|string|max:50' ,
                'database_hostname' => 'required|string|max:50' ,
                'database_port' => 'required|numeric' ,
                'database_name' => 'required|string|max:50' ,
                'database_username' => 'required|string|max:50' ,
                'database_password' => 'nullable|string|max:50' ,
                'envato_key' => 'required|string|max:100' ,
                'email_id' => 'nullable|string|max:50' ,
                'broadcast_driver' => 'required|string|max:50' ,
                'cache_driver' => 'required|string|max:50' ,
                'session_driver' => 'required|string|max:50' ,
                'queue_driver' => 'required|string|max:50' ,
                'redis_hostname' => 'required|string|max:50' ,
                'redis_password' => 'required|string|max:50' ,
                'redis_port' => 'required|numeric' ,
                'mail_driver' => 'required|string|max:50' ,
                'mail_host' => 'required|string|max:50' ,
                'mail_port' => 'required|string|max:50' ,
                'mail_username' => 'required|string|max:50' ,
                'mail_password' => 'required|string|max:50' ,
                'mail_encryption' => 'required|string|max:50' ,
                'pusher_app_id' => 'max:50' ,
                'pusher_app_key' => 'max:50' ,
                'pusher_app_secret' => 'max:50' ,
            ] ,
        ] ,
    ] ,

    /*
    |--------------------------------------------------------------------------
    | Installed Middleware Options
    |--------------------------------------------------------------------------
    | Different available status switch configuration for the
    | canInstall middleware located in `canInstall.php`.
    |
    */
    'installed' => [
        'redirectOptions' => [
            'route' => [
                'name' => 'dashboard' ,
                'data' => [] ,
            ] ,
            'abort' => [
                'type' => '502' ,
            ] ,
            'dump' => [
                'data' => 'This software already installed.' ,
            ] ,
        ] ,
    ] ,

    /*
    |--------------------------------------------------------------------------
    | Selected Installed Middleware Option
    |--------------------------------------------------------------------------
    | The selected option fo what happens when an installer instance has been
    | Default output is to `/resources/views/error/404.blade.php` if none.
    | The available middleware options include:
    | route, abort, dump, 404, default, ''
    |
    */
    'installedAlreadyAction' => 'dump' ,

    /*
    |--------------------------------------------------------------------------
    | Updater Enabled
    |--------------------------------------------------------------------------
    | Can the application run the '/update' route with the migrations.
    | The default option is set to False if none is present.
    | Boolean value
    |
    */
    'updaterEnabled' => 'true' ,

];
