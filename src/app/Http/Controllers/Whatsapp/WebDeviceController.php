<?php

namespace App\Http\Controllers\Whatsapp;

use App\Events\NewWhatsappWebMessage;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Services\WhatsappService\WebApiService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WebDeviceController extends Controller
{
    /**
     * create form show
     */
    public function create()
    {
        $title = "WhatsApp Device";

        $whatsAppDevices = WhatsappDevice
            ::byUserType(auth()->id())
            ->orderBy('id' , 'desc')
            ->with('user')
            ->paginate(paginateNumber());

        $data = [
            'title' => $title ,
            'whatsAppDevices' => $whatsAppDevices ,
        ];

        if (session()->has('new_device_id')) {
            $data['popup_scan_btn_id'] = "whatsappDevice_" . session()->get('new_device_id');
        }

        return view('whatsapp.web_device.create' , $data);
    }

    public function edit($id)
    {
        $title = "WhatsApp Device Edit";

        $whatsapp = WhatsappDevice::byUserType(auth()->id())->where('id' , $id)->firstOrFail();

        return view('whatsapp.web_device.edit' , [
            'title' => $title ,
            'whatsapp' => $whatsapp ,
        ]);
    }

    /**
     * whatsapp update method
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:wa_device,name,' . $request->id ,
            'number' => 'required|numeric|unique:wa_device,number,' . $request->id ,
            'delay_time' => 'required' ,
        ]);

        $whatsapp = WhatsappDevice::byUserType(auth()->id())
            ->where('id' , $request->id)->firstOrFail();

        $whatsapp->update($data);

        $notify[] = ['success' , 'WhatsApp Device successfully Updated'];
        return back()->withNotify($notify);
    }


    public function delete(Request $request)
    {
        session()->remove('new_device_id');

        $whatsapp = WhatsappDevice::byUserType(auth()->id())
            ->where('id' , $request->id)
            ->firstOrFail();

        try {
            if ($whatsapp->status != WhatsappDevice::STATUS_DISCONNECTED && !WebApiService::deleteSession($whatsapp->session_id)) {
                $notify[] = ['error' , 'Unable to delete whatsapp session. Please logout manually'];
            }

            $whatsapp->delete();

        } catch (Exception $e) {
            $notify[] = ['error' , 'Unable to delete whatsapp session. Please logout manually.'];
        }

        $notify[] = ['success' , 'Whatsapp Device successfully Deleted'];
        return back()->withNotify($notify);
    }

    public function disconnect()
    {
        session()->remove('new_device_id');

        $whatsappDevice = WhatsappDevice::byUserType(auth()->id())
            ->where('id' , \request('id'))->firstOrFail();

        $whatsappDevice->status = WhatsappDevice::STATUS_DISCONNECTED;
        $whatsappDevice->qr = null;
        $whatsappDevice->saveOrFail();

        WebApiService::deleteSession($whatsappDevice->session_id);

        return back()->withNotify([['success' , 'Successfully disconnected session']]);
    }


    public function createSession(Request $request)
    {
        $data = $request->validate([
            'name' => 'required' ,
            'number' => 'required|numeric|unique:wa_device,number,' . $request->id ,
            'delay_time' => 'required' ,
        ]);

        try {
            DB::beginTransaction();

            $data['status'] = WhatsappDevice::STATUS_INITIATE;
            $data['session_id'] = Str::random(40);
            $data['user_type'] = auth()->id() ? 'user' : 'admin';
            $data['user_id'] = auth()->id();

            if (WebApiService::createSession($data['session_id']) && $whatsappDevice = WhatsappDevice::query()->create($data)) {
                DB::commit();
                session()->put('new_device_id' , $whatsappDevice->id);

                return back()->withNotify([['success' , 'Successfully Created Session']]);
            }

        } catch (Exception $exception) {
            DB::rollBack();

            logger()->error($exception->getMessage());
        }

        return back()->withNotify([['error' , 'Unable to create a session']]);
    }

    public function webhook()
    {
        $whatsappDevice = WhatsappDevice::query()
            ->where('session_id' , \request('sessionId'))
            ->firstOrFail();

        $dataType = \request('dataType');

        if ($dataType == 'message' && (explode('@' , \request('data.message.from' , ''))[1] ?? '') === 'c.us'
            && \request('data.message._data.id.fromMe') === false) {
            NewWhatsappWebMessage::dispatch($whatsappDevice , \request('data.message'));
        }

        $data = \request('data');

        if (in_array($dataType , ['loading_screen' , 'authenticated' , 'ready' , 'qr']) && $data) {
            $whatsappDevice->data = ['dataType' => $dataType , 'data' => $data];
        }

        $qr = $data['qr'] ?? null;

        if ($qr) {
            $whatsappDevice->qr = $data['qr'];
            session()->put('new_device_id' , $whatsappDevice->id);

        } else {
            $whatsappDevice->qr = null;
            session()->put('new_device_id' , null);
        }

        if (($dataType == 'loading_screen' && Arr::get($data , 'percent') == 100)
            || $dataType == 'ready' || $dataType == 'authenticated') {

            $whatsappDevice->status = WhatsappDevice::STATUS_CONNECTED;
            $whatsappDevice->save();

        } elseif ($dataType == 'disconnected') {

            $whatsappDevice->status = WhatsappDevice::STATUS_DISCONNECTED;

        }

        $whatsappDevice->saveOrFail();

        return 'SUCCESS';
    }

    public function getQR(): JsonResponse
    {
        $whatsappDevice = WhatsappDevice::byUserType(auth()->id())
            ->where('id' , \request('id'))
            ->firstOrFail();

        if ($whatsappDevice->status == WhatsappDevice::STATUS_DISCONNECTED) {
            WebApiService::deleteSession($whatsappDevice->session_id);

            if (WebApiService::createSession($whatsappDevice->session_id)) {
                $whatsappDevice->status = WhatsappDevice::STATUS_INITIATE;
                $whatsappDevice->save();
            }
        }

        return response()->json([
            'qr' => $whatsappDevice->qr ,
            'session_id' => $whatsappDevice->session_id ,
            'id' => $whatsappDevice->id ,
            'status' => $whatsappDevice->status ,
            'data' => $whatsappDevice->data
        ]);
    }
}
