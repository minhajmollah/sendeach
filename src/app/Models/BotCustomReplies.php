<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;

class BotCustomReplies extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'message', 'reply', 'ai_bot_id', 'keywords', 'to_pause', 'pause_duration', 'is_partial_match'];

    public function scopeSearch(Builder $builder, $search)
    {

        $booleanSearch = explode(',', $search);
        $booleanSearchQuery = '';

//        foreach ($booleanSearch as $bs)
//        {
//            $sub = explode(' ', $search);
//
//            foreach ($sub as $s)
//            {
//                $booleanSearchQuery .= '+'.$s;
//            }
//        }

        return $builder->when($search, function (Builder $query) use ($booleanSearchQuery, $search) {
            return $query
                ->selectRaw('MATCH (message, keywords) AGAINST (\'' . $search . '\'
                 IN NATURAL LANGUAGE MODE)')
                ->whereRaw('MATCH (message, keywords) AGAINST (\'' . $search . '\'
                 IN NATURAL LANGUAGE MODE)');
        });
    }

    public static function search($searchStr, AiBot $aiBot = null)
    {
        $searchStr = preg_replace('/\s+/',' ', trim(htmlspecialchars(strtolower(addslashes($searchStr)))));

        $search = explode(' ', $searchStr);

        $expression = '';

        foreach ($search as $i => $key) {
            $expression .= "(keywords like '%$key%' )";

            if ($i < count($search) - 1) {
                $expression .= ' + ';
            }
        }

        $messages = BotCustomReplies::query()
            ->where('user_id', $aiBot?->user?->id)
            ->selectRaw("$expression AS search")
            ->addSelect(['id', 'message', 'reply', 'keywords', 'ai_bot_id', 'ai_bot_response_id', 'to_pause', 'pause_duration', 'is_partial_match'])
            ->orderByRaw('1 DESC')
            ->get();


        $messages = $messages->map(function ($message) use ($searchStr) {
            $message->rank = 0;
            $keywords = explode(',', $message->keywords);
            foreach ($keywords as $keyword) {
                $keyword = strtolower(trim($keyword));
                if (preg_match("/\b$keyword\b/", $searchStr)) {
                    $message->rank += 2;
                } else if ($message->is_partial_match && preg_match("/$keyword/", $searchStr)) {
                    $message->rank += 1;
                }
            }

            return $message;
        });

        if (!$aiBot || !Arr::get($aiBot->data, 'custom_replies.is_partial_match')) {

            $messages = $messages->filter(fn($message) => $message->search && $message->rank);
        }

        $messages = $messages->sortByDesc(['rank', 'search']);

        return $messages;
    }

    public static function generateCustomAutoReplies($businessText, $userId)
    {
        $aiBot = AiBot::userBot(auth()->id());

        if (!$aiBot->chargeCredits()) {
            return null;
        }

        $data = [
            'model' => $aiBot->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Provide your answer in JSON form. Reply with only the answer in JSON form and include no other commentary: generate at least 6 chats for my given business information each chat containing 4 keywords and its corresponding reply.'],
                ['role' => 'user', 'content' => "
                        Business Information: Users Business Information Goes Here."],
                ['role' => 'assistant', 'content' => '[{"keywords": "qualities of your business , business unique , business apart , business values", "reply": "We promise honesty as our best policy and offer quality Indian products at cheap prices."}]'],
                ['role' => 'user', 'content' => "Business Information: " . $businessText],
            ],
            'temperature' => $aiBot->temperature,
            'n' => $aiBot->n ?: 1,
            'user' => $aiBot->user?->email ?: 'Admin',
        ];

        logger()->debug(json_encode($data));

        $result = AiBot::getOpenAIClient()->chat()->create($data);

        $aiBot->total_tokens_used += $result->usage->totalTokens;
        $aiBot->save();

        logger()->debug(json_encode($result));

        $replies = json_decode($result->choices[0]->message->content, true);


        if ($replies) {
            $replies = array_map(function ($reply) use ($userId) {
                return [
                    'reply' => $reply['reply'],
                    'keywords' => is_array($reply['keywords']) ? join(', ', $reply['keywords']) : (string)$reply['keywords'],
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $replies);

            BotCustomReplies::query()->insert($replies);
        }


    }
}
