<?php

namespace App\Services\WhatsappService;

use App\Models\WhatsappTemplate;
use Illuminate\Support\Arr;

class WhatsappMessageTemplateService extends WhatsappBusinessService
{

    public function getBaseUrl()
    {
        return sprintf("%s/%s/message_templates" , parent::getBaseUrl() , $this->whatsappBusinessId);
    }

    public function getAllTemplates($fields = [])
    {
        $response = $this->http()
            ->get(self::getBaseUrl() , $this->data(['fields' => $fields]));

        $this->lastResponse = $response;

        return $response;
    }

    public function syncTemplates()
    {
        $templates = $this->getAllTemplates();
        $responseJsonData = $templates->json();

        // Log Error
        if ($templates->status() !== 200) {
            self::logError($responseJsonData);
            return $responseJsonData;
        }

        $templates = $this->getData($responseJsonData['data']);

        return WhatsappTemplate::query()->upsert($templates, ['whatsapp_business_id', 'whatsapp_template_id'],
            ['name', 'status', 'category', 'language', 'rejected_reason', 'components', 'user_id']);
    }

    /**
     * @param $data
     * @return array|array[]
     */
    public function getData($data): array
    {
        return array_map(function ($template) {
            $newTemplate = [];
            $newTemplate['name'] = $template['name'];
            $newTemplate['status'] = $template['status'];
            $newTemplate['category'] = $template['category'];
            $newTemplate['language'] = $template['language'];
            $newTemplate['rejected_reason'] = empty($template['rejected_reason']) ? null : $template['rejected_reason'];

            $newTemplate['components'] = json_encode($template['components']);
            $newTemplate['user_id'] = $this->userId;
            $newTemplate['whatsapp_template_id'] = $template['id'];
            $newTemplate['whatsapp_business_id'] = $this->whatsappBusinessId;
            return $newTemplate;
        } , $data);
    }


    public function createTemplate(WhatsappTemplate $template , $allow_category_change = true): bool
    {

        $data = array_merge(Arr::only($template->toArray() , ['name' , 'language' , 'category' , 'components']) , [
            'allow_category_change' => $allow_category_change
        ]);

        $response = static::Http()->post(WhatsappMessageTemplateService::getBaseUrl() ,$this->data($data));

        return $this->setTemplate($template, $response);
    }

    public function updateTemplate(WhatsappTemplate $template): bool
    {
        $data = Arr::only($template->toArray() , ['components']);

        $response = static::Http()->post(sprintf("%s/%s" , parent::getBaseUrl(), $template->whatsapp_template_id) ,$this->data($data));

        return $this->setTemplate($template, $response);
    }

    public function deleteTemplate(WhatsappTemplate $template): bool
    {
        $response = static::Http()->delete(WhatsappMessageTemplateService::getBaseUrl() , $this->data(['name' => $template->name]));

        $this->lastResponse = $response;

        if ($response->status() == 200 && Arr::get($response->json() , 'success')) {
            $template->delete();

            return true;
        }

        logger()->error($response->json());

        return false;
    }

    private function setTemplate(WhatsappTemplate $template, $response): bool
    {
        $this->lastResponse = $response;

        if ($response->status() == 200 && Arr::get($response->json() , 'success')) {
            $data = $response->json();

            $status = Arr::get($data , 'status');
            $category = Arr::get($data , 'category');
            $id = Arr::get($data , 'id');

            if ($status) $template->status = $status;
            if ($category) $template->category = $category;
            if($id) $template->whatsapp_template_id = $id;

            $template->save();

            return true;
        }

        $template->status = WhatsappTemplate::STATUS_ERROR;
        $template->save();

        logger()->error($response->json());

        return false;
    }
}
