<?php

namespace App\Http\Controllers\User\Whatsapp;

use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait WhatsappControllerTrait
{
    public function index(Request $request)
    {
        $scope = Arr::last(explode('/', $request->path()));

        $title = 'WhatsApp Messages | ' . ucfirst($scope);

        $scope = WhatsappLog::STATUS_SCOPE[$scope] ?? null;

        $search = $request->search;
        $searchDate = $request->date;

        $firstDate = null;
        $lastDate = null;

        if ($searchDate) {
            $searchDate_array = explode('-', $request->date);
            $firstDate = $searchDate_array[0];

            if (count($searchDate_array) > 1) {
                $lastDate = $searchDate_array[1];
            }

            $matchDate = "/\d{2}\/\d{2}\/\d{4}/";
            if ($firstDate && !preg_match($matchDate, $firstDate)) {
                $notify[] = ['error', 'Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($lastDate && !preg_match($matchDate, $lastDate)) {
                $notify[] = ['error', 'Invalid order search date format'];
                return back()->withNotify($notify);
            }
        }

        $batchId = request('batch_id');

        $gateway = $request->routeIs('user.desktop.*') ? WhatsappLog::GATEWAY_DESKTOP : WhatsappLog::GATEWAY_WEB;

        $whatsApp = WhatsappLog::query()
            ->where('user_id', \auth()->id())
            ->orderBy('id', 'desc')
            ->when($gateway == WhatsappLog::GATEWAY_DESKTOP, fn($q) => $q->with('whatsappPCGateway'))
            ->when($gateway == WhatsappLog::GATEWAY_WEB, fn($q) => $q->with('whatsappGateway'))
            ->search($scope, $search, gateway: $gateway, startDate: $firstDate, endDate: $lastDate)
            ->when($batchId, fn($q) => $q->where('batch_id', $batchId))
            ->paginate(paginateNumber());

        return view('user.whatsapp.index', compact('title', 'whatsApp', 'search', 'searchDate'));
    }

    public function deleteLogs(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ], $request->all());

        try {

            $status = WhatsappLog::STATUS_SCOPE[$request->id] ?? null;

            if ($request->id == 'all') $status = 'all';

            $gateway = $request->routeIs('user.desktop.*') ? WhatsappLog::GATEWAY_DESKTOP : WhatsappLog::GATEWAY_WEB;

            WhatsappLog::query()
                ->where('user_id', \auth()->id())
                ->when(!$status, fn($q) => $q->whereIn('id', explode(',', $request->id)))
                ->when(($request->id != 'all') && $status, fn($q) => $q->where('status', $status))
                ->where('gateway', $gateway)
                ->delete();

            $notify[] = ['success', "Successfully Whatsapp Desktop log deleted"];
        } catch (\Exception $e) {
            $notify[] = ['error', "Error occurred in deleting logs"];
        }

        return back()->withNotify($notify);
    }

    public function campaign()
    {
        $title = 'Whatsapp Campaign';

        $gateway = \request()->routeIs('user.desktop.*') ? WhatsappLog::GATEWAY_DESKTOP : WhatsappLog::GATEWAY_WEB;

        $whatsappReports = WhatsappLog::withTrashed()
            ->where('user_id', auth()->id())
            ->where('gateway', $gateway)
            ->selectRaw('batch_id, COUNT(*) as total,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS pending,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS processing,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS delivered,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS failed,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS scheduled,
             SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS paused,
             MIN(initiated_time) as started_at,
             MAX(updated_at) as completed_at,
             message
             ', [WhatsappLog::PENDING, WhatsappLog::PROCESSING, WhatsappLog::SUCCESS, WhatsappLog::FAILED, WhatsappLog::SCHEDULE, WhatsappLog::PAUSE])
            ->groupBy('batch_id')
            ->orderByDesc('started_at')
            ->paginate(paginateNumber());


        return view('whatsapp.campaign', compact('title', 'whatsappReports'));
    }

    public function deleteCampaign()
    {
        try {
            if (WhatsappLog::query()
                ->where('user_id', \auth()->id())
                ->when(\request('id'), fn($q) => $q->whereIn('batch_id', explode(',', \request('id'))))
                ->unless(\request('id'), fn($q) => $q->whereNull('batch_id'))
                ->forceDelete()) {
                $notify[] = ['success', "Successfully Deleted Whatsapp Campaign"];
            } else {
                $notify[] = ['error', "Error occurred in deleting logs"];
            }

        } catch (\Exception $e) {
            $notify[] = ['error', "Error occurred in deleting logs"];
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());
        }

        return back()->withNotify($notify);
    }
}
