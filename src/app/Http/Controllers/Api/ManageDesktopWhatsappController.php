<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MessageRequest;
use App\Http\Requests\Api\MessageUpdateRequest;
use App\Http\Requests\Api\PCMessageDeleteRequest;
use App\Models\GeneralSetting;
use App\Models\UserWindowsToken;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Models\WhatsappPCMessageDelete;
use App\Services\WhatsappService\WebApiService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class ManageDesktopWhatsappController extends Controller
{
    public function desktopMessages(MessageRequest $request)
    {
        $token = null;

        if ($request->windows_token) {
            $token = UserWindowsToken::query()
                ->where('user_id' , auth()->id())
                ->where('token' , $request->windows_token)->first();
        } else {
            return response()->json(['status' => 'failed' ,
                'message' => 'No Windows token is passed.'] , 400);
        }

        if (!$token) $token = UserWindowsToken::query()->where('user_id' , auth()->id())
            ->where('status' , WhatsappDevice::STATUS_CONNECTED)
            ->latest()->first();

        if ($token) {
            $token->last_called_at = now();
            $token->save();
        }

        $limit = $request->limit ?: 10;

        $logs = WhatsappLog::query()
            ->byDesktop(auth()->id() , $token , status: null)
            ->toProcess()
            ->limit($limit)
            ->select(['id' , 'user_id' , 'to' , 'initiated_time' , 'message' , 'document' , 'audio' ,
                'image' , 'video' , 'status' , 'file_caption' ,
                'response_gateway' , 'created_at' , 'updated_at' , 'auto_delete'])
            ->get();


        if(!WhatsappLog::canSend('User::'.auth()->id().'::desktop-gateway::'.$token->device_id, $logs->count()))
        {
            return response()->json([]);
        }

        if ($logs->isNotEmpty()) {

            WhatsappLog::query()
                ->byDesktop(auth()->id() , $token , status: null)
                ->toProcess()
                ->limit($limit)
                ->update(['status' => WhatsappLog::PROCESSING]);

            $logs = $logs->map(function ($data) {
                $data->file = $data->file ?? $data->audio ?? $data->document ?? $data->image ?? $data->video;

                unset($data->audio , $data->document , $data->image , $data->video);

                return $data;
            });
        }

        return response()->json($logs);
    }

    public function isUserEnabledAutoDelete()
    {
        $user = auth()->user();

        if ($user->auto_delete_whatsapp_pc_messages) {

            return response()->json([
                'status' => 'success' ,
                'message' => 'User Has Enabled Auto Delete.'
            ]);
        }

        return response()->json([
            'status' => 'failed' ,
            'message' => 'User Has Not enabled Auto Delete'
        ]);
    }

    public function pullMessagesToDelete()
    {
        $user = auth()->user();

        if ($user->auto_delete_whatsapp_pc_messages) {
            $logs = WhatsappLog::query()->orderBy('id' , 'DESC')
                ->where('user_id' , auth()->id())
                ->where('created_at' , '>=' , $user->auto_delete_whatsapp_pc_messages)
                ->whereNull('auto_deleted_at')
                ->where('status' , WhatsappLog::SUCCESS)
                ->where('gateway' , WhatsappLog::GATEWAY_DESKTOP)
                ->select(['id' , 'user_id' , 'to' , 'initiated_time' , 'message' , 'document' , 'audio' ,
                    'image' , 'video' , 'status' , 'schedule_status' ,
                    'response_gateway' , 'created_at' , 'updated_at'])->get();

            return response()->json([
                'status' => 'status' ,
                'data' => $logs
            ]);
        }

        return response()->json([
            'status' => 'failed' ,
            'message' => 'User has not enabled auto delete Option'
        ] , 400);
    }

    public function updateMessage(MessageUpdateRequest $request): JsonResponse
    {
        $whatsappLog = WhatsappLog::query()
            ->where('user_id' , auth()->id())
            ->where('gateway' , WhatsappLog::GATEWAY_DESKTOP)
            ->when(is_array($request->log_id) , fn($q) => $q->whereIn('id' , $request->log_id))
            ->unless(is_array($request->log_id) , fn($q) => $q->where('id' , $request->log_id))
            ->update([
                'status' => $request->status ,
                'response_gateway' => $request->message
            ]);

        if ($whatsappLog) {

            if ($request->status == WhatsappLog::SUCCESS && auth()->user()->auto_delete_whatsapp_pc_messages
                && $device = WhatsappDevice::connected(auth()->id())->first()) {

                $logs = WhatsappLog::query()
                    ->where('user_id' , auth()->id())
                    ->where('gateway' , WhatsappLog::GATEWAY_DESKTOP)
                    ->when(is_array($request->log_id) , fn($q) => $q->whereIn('id' , $request->log_id))
                    ->unless(is_array($request->log_id) , fn($q) => $q->where('id' , $request->log_id))
                    ->get();

                foreach ($logs as $log) {
                    $message = WebApiService::searchMessages($device , $log->message , $log->to . '@c.us' , isExact: true);

                    if ($message = Arr::first($message)) {
                        if (WebApiService::deleteMessage($device , $message['id'] , $message['to'])) {
                            $log->auto_deleted_at = now();
                            $log->save();
                        }
                    }
                }
            }

            return response()->json([
                "status" => "success" ,
                "data" => ["whatsappLog" => $whatsappLog]
            ]);
        }

        return response()->json([
            "status" => "failed" ,
            'data' => ["message" => "unable to update log status." , "whatsappLog" => $whatsappLog] ,
        ] , 400);
    }

    public function pullMessageDeleteKeywords(): JsonResponse
    {
        $keywords = WhatsappPCMessageDelete::query()
            ->select('keywords' , 'id')
            ->where('user_id' , auth()->id())->get();

        return response()->json([
            'status' => 'success' ,
            'data' => $keywords->toArray()
        ]);
    }

    public function updateKeywordDeleteStatus(PCMessageDeleteRequest $request)
    {
        $message = WhatsappPCMessageDelete::query()
            ->where('user_id' , auth()->id())
            ->where('id' , $request['id'])
            ->firstOrFail();

        $message->status = $request['status'];
        $message->response = $request['message'];
        $message->saveOrFail();

        return response()->json([
            'status' => 'success' ,
            'message' => 'Successfully updated message delete status'
        ]);
    }

    public function updateDeleteStatus()
    {

        if (auth()->user()->auto_delete_whatsapp_pc_messages) {
            return response()->json([
                'status' => 'failed' ,
                'message' => 'User has not enabled auto delete Option'
            ] , 400);
        }

        $whatsappLog = WhatsappLog::query()
            ->where('user_id' , auth()->id())
            ->where('id' , request('log_id'))
            ->select(['id' , 'user_id' , 'to' , 'initiated_time' , 'message' , 'document' , 'audio' ,
                'image' , 'video' , 'status' , 'schedule_status' ,
                'response_gateway' , 'created_at' , 'updated_at' , 'status'])->firstOrFail();


        $whatsappLog->auto_deleted_at = now();

        if ($whatsappLog->save()) {
            return response()->json([
                "status" => "success" ,
                "data" => ["whatsappLog" => $whatsappLog]
            ]);
        }

        return response()->json([
            "status" => "failed" ,
            'data' => ["message" => "unable to delete message." , "whatsappLog" => $whatsappLog] ,
        ] , 400);
    }

    public function getDesktopVersion(): JsonResponse
    {
        $settings = GeneralSetting::admin();

        return response()->json([
            'version' => $settings->desktop_app_version ,
            'url' => asset('assets/desktop-app/SendEach.msi')
        ]);
    }

    public function uploadDesktopAPP()
    {
        if (!Hash::check('admin_desktop_version' , request('key'))) {
            abort(403);
        }

        $file = base64_decode(request('file'));
        $version = request('version');

        $general = GeneralSetting::admin();

        if ($file && $version && $version >= $general->desktop_app_version) {
            try {

                Storage::disk('assets')->put('desktop-app/SendEach.zip' , $file);
                $general->desktop_app_version = $version;
                $general->saveOrFail();

                return response()->json(['success' => true]);
            } catch (\Exception $exp) {
            }
        }

        abort(400);
    }
}
