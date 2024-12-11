<?php

namespace Database\Seeders;

use App\Models\WhatsappAccessToken;
use App\Models\WhatsappBusinessMessageRate;
use App\Models\WhatsappTemplate;
use Illuminate\Database\Seeder;

class WhatsappBusinessMessageRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        WhatsappBusinessMessageRate::query()->delete();

        $data = [
            ['category' => 'OTHER' , 'credits' => 2 , 'type' => WhatsappAccessToken::TYPE_EMBEDDED_FORM] ,
            ['category' => 'OTHER' , 'credits' => 2 , 'type' => WhatsappAccessToken::TYPE_OWN] ,
            ['category' => WhatsappTemplate::CATEGORY_AUTHENTICATION , 'credits' => 1 , 'type' => WhatsappAccessToken::TYPE_OWN] ,
            ['category' => WhatsappTemplate::CATEGORY_MARKETING , 'credits' => 4 , 'type' => WhatsappAccessToken::TYPE_OWN] ,
            ['category' => WhatsappTemplate::CATEGORY_UTILITY , 'credits' => 2 , 'type' => WhatsappAccessToken::TYPE_OWN] ,
            ['category' => WhatsappTemplate::CATEGORY_AUTHENTICATION , 'credits' => 1 , 'type' => WhatsappAccessToken::TYPE_EMBEDDED_FORM] ,
            ['category' => WhatsappTemplate::CATEGORY_UTILITY , 'credits' => 2 , 'type' => WhatsappAccessToken::TYPE_EMBEDDED_FORM] ,
            ['category' => WhatsappTemplate::CATEGORY_MARKETING , 'credits' => 4 , 'type' => WhatsappAccessToken::TYPE_EMBEDDED_FORM] ,
        ];

        WhatsappBusinessMessageRate::query()->insert($data);
    }
}
