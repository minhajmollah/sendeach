<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomReplyUpdateRequest;
use App\Models\AiBot;
use App\Models\AiBotResponse;
use App\Models\BotCustomReplies;
use App\Models\Chat;
use App\Models\ChatConversation;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CustomReplyController extends Controller
{
    public function index()
    {
        $title = 'Manage Custom Replies';

        $aiBot = AiBot::userBot(auth()->id());

        if (strlen($aiBot->data['business_text'] ?? '') < 10) {
            return to_route(auth()->id() ? 'user.ai_bots.index' : 'admin.ai_bots.index')
                ->withNotify([['error', 'To begin utilizing custom replies, kindly provide your business information.']]);
        }

        $search = request('search');

        $replies = $search ? BotCustomReplies::search($search, $aiBot) : BotCustomReplies::query()->where('user_id', auth()->id())
            ->addSelect(['id', 'message', 'reply', 'keywords', 'ai_bot_id', 'ai_bot_response_id', 'to_pause', 'pause_duration', 'is_partial_match'])
            ->orderBy('ai_bot_response_id', 'ASC')
            ->paginate(paginateNumber());

        $conversations = ChatConversation::with('messageable')
            ->where('messageable_type', '<>', Chat::MESSAGEABLE_TYPE_GUEST)
            ->where('ai_bot_id', $aiBot->id)
            ->get();

        $totalChats = Chat::query()->whereIn(
            'conversation_id',
            ChatConversation::query()->whereIn('ai_bot_id', [$aiBot->id])->select('id')
        )->count();

        return view('ai-bots.replies', compact('title', 'replies', 'aiBot', 'conversations', 'totalChats'));
    }

    public function importFromChats()
    {
        $responseIds = BotCustomReplies::query()
            ->where('user_id' , auth()->id())
            ->whereNotNull('ai_bot_response_id')
            ->select('ai_bot_response_id');

        $chats = AiBotResponse::asChats(
            AiBotResponse::byUser(auth()->id())
                ->whereNotIn('id' , $responseIds)
                ->orderByDesc('created_at')
                ->whereNotNull('chat_conversation_id')
                ->get()
                ->unique('message'),
            ['user_id' => auth()->id()]
        );

        if ($chats->count() < 500) {
            return back()->withNotify([['error', 'To import into custom replies, it\'s essential to have a minimum of 500 chats with AI.']])->withInput();
        }

        $chats = $chats->map(function ($chat) {
            $chat['keywords'] = generateKeywords($chat['message']);
            return $chat;
        });

        BotCustomReplies::query()->insert($chats->toArray());

        return back()->withNotify([['success', 'Successfully imported chats.']])->withInput();
    }

    public function update(CustomReplyUpdateRequest $request)
    {
        $customs = request('customs', []);

        try {
            DB::beginTransaction();

            $insert = [];
            $upsert = [];

            BotCustomReplies::query()
                ->where('user_id', auth()->id())
                ->whereIn('id', explode(',', request('toDelete')))
                ->delete();

            foreach ($customs as $custom) {

                if (isset($custom['id'])) {
                    $upsert[] = [
                        'reply' => $custom['reply'],
                        'message' => $custom['message'] ?? '',
                        'keywords' => join(", ", array_map(fn($key) => strtolower(trim($key)), explode(',', $custom['keywords']))),
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'id' => $custom['id'],
                        'to_pause' => (boolean)($custom['to_pause'] ?? false),
                        'pause_duration' => $custom['pause_duration'] ?? 0,
                        'is_partial_match' => (boolean)($custom['is_partial_match'] ?? false),
                    ];
                } else {
                    $insert[] = [
                        'reply' => $custom['reply'],
                        'message' => $custom['message'] ?? '',
                        'keywords' => join(" ", array_map(fn($key) => strtolower(trim($key)), explode(',', $custom['keywords']))),
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'is_partial_match' => (boolean)($custom['is_partial_match'] ?? false),
                        'to_pause' => (boolean)($custom['to_pause'] ?? false),
                        'pause_duration' => $custom['pause_duration'] ?? 0,
                    ];
                }
            }

            BotCustomReplies::query()->insert($insert);
            BotCustomReplies::query()->upsert($upsert, ['user_id', 'id']);

            DB::commit();

            return back()->withNotify([['success', 'Successfully updated custom replies.']]);
        } catch (\Exception $exception) {
            DB::rollBack();
            logger()->error($exception);
            return back()->withNotify([['error', 'Unable to update bot custom replies.']]);
        }
    }

    public function delete()
    {

        try {

            if (!request('message_id')) {
                return back()->withNotify([['error', 'Unable to delete.']]);
            }

            if (BotCustomReplies::query()
                ->where('user_id', auth()->id())
                ->when(request('message_id') != 'delete-all',
                    fn($q) => $q->whereIn('id', explode(',', request('message_id'))))
                ->delete()) {

                return back()->withNotify([['success', 'Successfully deleted.']]);
            }

        } catch (\Exception $exception) {

            logger()->error(json_encode($exception));

            return back()->withNotify([['error', 'Something went wrong.']]);
        }

        return back()->withNotify([['error', 'Unable to delete.']]);
    }

    public function updateKeywordsFromAi()
    {
        $aiBot = AiBot::query()->where('user_id', auth()->id())
            ->where('name', AiBot::CHAT)->firstOrFail();

        if (!$aiBot->chargeCredits()) {
            return back()->withNotify([['error', 'Insufficient Credits to use this feature.']]);
        }

        BotCustomReplies::query()
            ->orderBy('created_at')
            ->where('user_id', auth()->id())
            ->whereNull('keywords')
            ->orWhereRaw('LENGTH(keywords) < 5')
            ->chunk(3, function ($replies) use ($aiBot) {
                $replyString = $replies->map(function ($reply) {
                    return "User: $reply->message \n Bot: $reply->reply";
                })->join("\n");

                $keywords = $aiBot->getKeywords($replyString);

                if (!$keywords) return;

                if (is_string($keywords[0])) {
                    $keywords = [$keywords];
                }

                foreach ($replies as $i => $reply) {
                    $reply->update(['keywords' => join(' , ', array_map(fn($key) => strtolower(trim($key)), $keywords[$i] ?? []))]);
                }
            });

        return back()->withNotify([['success', 'Successfully fetched and updated keywords.']]);
    }

    public function connectToHuman()
    {
        /** @var ChatConversation $conversation */
        $conversation = ChatConversation::query()->findOrFail(request('sender'));
        $duration = (integer)request('duration') ?: 1;

        $conversation->data = $conversation->data ?: [];
        $conversation->data['paused_at'] = now();
        $conversation->data['pause_duration'] = $duration;
        $conversation->saveOrFail();

        return back()->withNotify([
            ['success', 'Successfully paused the Conversation with AI for ' . $duration . ' Minutes.']
        ]);
    }

    public function togglePartialMatch()
    {
        $isPartialMatch = (boolean)request('is_partial_match');

        $aiBot = AiBot::userBot(auth()->id());
        $aiBot->data = $aiBot->data ?: [];
        Arr::set($aiBot->data, 'custom_replies.is_partial_match', $isPartialMatch);
        $aiBot->saveOrFail();

        return back()->withNotify([
            ['success', 'Successfully updated keyword match type.']
        ]);
    }
}
