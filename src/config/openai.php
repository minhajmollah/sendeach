<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY') ,
    'organization' => env('OPENAI_ORGANIZATION') ,
    'chat_model' => env('OPENAI_CHAT_MODEL' , 'gpt-3.5-turbo') ,
    'web_rate_limit' => env('OPENAI_WEB_RATE_LIMIT' , 3) ,
    'whatsapp_rate_limit' => env('OPENAI_WHATSAPP_RATE_LIMIT' , 2) ,
    'chat' => [
        'default_system_text' => 'You Should Act as Customer service representative.
Always limit answer to less than 20 words. Keep reply only related to our business. Our Business Information is given in triple quotes.' ,
        'default_assistant_text' => '' ,
    ] ,
    'greetings_text' => 'Welcome to my business?',
    'tokens_commission' => 30 ,
    'price_per_1000_tokens' => [
        \App\Models\AiBot::CHAT_GPT_35 => 0.0004 ,
        \App\Models\AiBot::FINE_TUNE_ADA => 0.0004 ,
    ],
    'max_business_text_length' => 8000
];
