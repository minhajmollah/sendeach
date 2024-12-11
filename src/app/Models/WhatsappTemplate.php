<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $language
 * @property string whatsapp_business_id
 * @property Collection $components
 * @property string user_id
 * @property string status
 * @property string category
 * @property int id
 */
class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $guarded = ['created_at', 'updated_at'];

    const CATEGORY_UTILITY = 'UTILITY';
    const CATEGORY_AUTHENTICATION = 'AUTHENTICATION';
    const CATEGORY_MARKETING = 'MARKETING';

    public static $categories = [self::CATEGORY_UTILITY , self::CATEGORY_AUTHENTICATION , self::CATEGORY_MARKETING];

    public static $statuses = ['APPROVED' , 'REJECTED' , 'IN_APPEAL' , 'PAUSED' , 'PENDING' ,
        'PENDING_DELETION' , 'DELETED' , 'DISABLED' , 'PAUSED' , 'LIMIT_EXCEEDED'];

    public static $languages = ['en_US' , 'en'];

    const STATUS_APPROVED = 'APPROVED';
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_PENDING = 'PENDING';

    const STATUS_ERROR = 'ERROR';
    const STATUS_DELETED = 'DELETED';
    const STATUS_DISABLED = 'DISABLED';
    const STATUS_PENDING_DELETION = 'PENDING_DELETION';
    const STATUS_IN_APPEAL = 'IN_APPEAL';
    const STATUS_LIMIT_EXCEEDED = 'LIMIT_EXCEEDED';

    const TYPE_HEADER = 'HEADER';
    const TYPE_BODY = 'BODY';
    const TYPE_FOOTER = 'FOOTER';

    public static function get_otp_template()
    {
        return WhatsappTemplate::where('whatsapp_template_id' , config('whatsapp.templates.user_login_otp'))
            ->where('status' , 'APPROVED')->first();
    }

    public static function get_public_otp_template()
    {
        return WhatsappTemplate::where('whatsapp_template_id' , config('whatsapp.templates.public_login_otp'))
            ->where('status' , 'APPROVED')->first();
    }

    public static function get_user_low_balance_alert()
    {
        return WhatsappTemplate::where('whatsapp_template_id' , config('whatsapp.templates.user_low_balance_alert'))->first();
    }
    public function totalCredit()
    {
        return 1;

        if (!in_array($this->category , static::$categories)) {
            $category = 'OTHER';
        } else {
            $category = $this->category;
        }


        return WhatsappBusinessMessageRate::query()->where('category' , $category)->where('type' , $type)->first()->credits;
    }

    protected function getComponentsAttribute($value): Collection
    {
        return collect(json_decode($value));
    }

    public function toMessage($to , $headerParameters , $bodyParameters): array
    {
        $template = [
            "messaging_product" => "whatsapp" ,
            "recipient_type" => "individual" ,
            "to" => $to ,
            "type" => "template" ,
            "template" => [
                "name" => $this->name ,
                "language" => [
                    "code" => $this->language
                ]
            ]
        ];

        $header = $this->makeTemplateComponent('header' , $headerParameters);
        $body = $this->makeTemplateComponent('body' , $bodyParameters);

        if ($header) {
            $template['template']['components'][] = $header;
        }
        if ($body) {
            $template['template']['components'][] = $body;
        }

        return $template;
    }

    public function toMessageText($headerParameters , $bodyParameters): string
    {

        $body = $this->getComponentText('BODY');

        if ($body && $bodyParameters) {
            return preg_replace_array("/\{\{[0-9]}\}/" , $bodyParameters , $body);
        }

        return $body;
    }

    /**
     * @param $type
     * @param array|null $parameters
     * @return array
     */
    public function makeTemplateComponent($type , ?array $parameters = []): array
    {
        if (!$parameters || !$this->getTemplateParameters(strtoupper($type))) return [];

        $header = ["type" => $type];
        $header['parameters'] = $this->makeTemplateParameters($parameters);

        return $header;
    }

    /**
     * @param array $vars
     * @param string $type
     * @return array
     */
    public function makeTemplateParameters(array $vars , string $type = 'text'): array
    {
        return array_map(function ($var) use ($type) {
            return ['type' => $type , 'text' => $var];
        } , $vars);
    }

    public function getTemplateParameters($type): array
    {

        if ($type == static::TYPE_BODY) {
            return $this->getTemplateBodyParameters();
        } else if ($type == static::TYPE_HEADER) {
            return $this->getTemplateHeaderParameters();
        }

        return array_filter($this->components->where('type' , $type)->pluck('example.' . strtolower($type) . '_text.0')->toArray());
    }

    public function getTemplateHeaderParameters(): array
    {
        return array_filter($this->components->where('type' , static::TYPE_HEADER)->pluck('example.header_text.0')->toArray());
    }

    public function getTemplateBodyParameters(): array
    {
        return array_filter($this->components->where('type' , static::TYPE_BODY)->pluck('example.body_text.0.0')->toArray());
    }

    public function getComponent($type)
    {
        return $this->components->where('type' , $type)->first();
    }

    public function getComponentText($type)
    {
        return optional($this->getComponent($type))->text;
    }

    public function isEditable()
    {
        return in_array($this->status ,
                [WhatsappTemplate::STATUS_APPROVED , WhatsappTemplate::STATUS_REJECTED , WhatsappTemplate::STATUS_PAUSED ,
                    WhatsappTemplate::STATUS_ERROR]) || $this->status === self::STATUS_ERROR;
    }

    public static function rules(): array
    {
        return [
            'name' => ['required' , 'string' , 'max:512'] ,
            'category' => ['required' , Rule::in(static::$categories)] ,
            'components' => ['required' , 'array'] ,
            'components.BODY.text' => ['required' , 'string'] ,
            'language' => ['required' , Rule::in(static::$languages)] ,
            'whatsapp_business_id' => ['required' ,
                Rule::exists('whatsapp_accounts' , 'whatsapp_business_id')
                    ->where('user_id' , auth('web')->id())]
        ];
    }

    public function whatsappBusinessAccount(): BelongsTo
    {
        return $this->belongsTo(WhatsappAccount::class , 'whatsapp_business_id' , 'whatsapp_business_id');
    }

    public function whatsappAccessToken()
    {
        return $this->belongsToMany(
            WhatsappAccessToken::class ,
            WhatsappAccount::class ,
            'whatsapp_business_id' ,
            'whatsapp_access_token_id' ,
            'whatsapp_business_id'
        );
    }
}
