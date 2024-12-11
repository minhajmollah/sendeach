<?php

return [
    'api_version' => env('WHATSAPP_API_VERSION' , 'v16.0') ,
    'app_id' => env('META_APP_ID', '755031505995623'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN' , 'EAAKusni1h2cBAMqAxCZA55bVqPJhp6hWMlhMWZCNWaLYaPzOpUrH9LE2CnxP1rWe8vMtySf7CQhAZBc9ZCM4gee7NvysQgl4VKwMyYrc2zHaOTZBPZAUIRHcVRSQZCn5pLYoIM1J0tWwbwHZCFkTlQLV6Vi4DferFz1GzHpuYz8vam80OSlXoD09k6nc1w1OLFQAlygyKkZAIwgZDZD') ,
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID' , '111459951916144') ,
    'business_manager_id' => env('WHATSAPP_BUSINESS_MANAGER_ID' , '6355933474430695') ,
    'admin_phone_id' => env('WHATSAPP_ADMIN_PHONE_ID' , '100626863011239') ,
    'templates' => [
        'user_login_otp' => env('TEMPLATES_USERS_OTP' , '749798039941522'),
        'user_low_balance_alert' => env('TEMPLATES_USER_LOW_BALANCE_ALERT' , '205051112330968'),
        'public_login_otp' => env('TEMPLATES_PUBLIC_LOGIN_OTP' , '234712169141419')
    ],
    'enable_embedded_form' => env('ENABLE_EMBEDDED_FORM', false)
];
