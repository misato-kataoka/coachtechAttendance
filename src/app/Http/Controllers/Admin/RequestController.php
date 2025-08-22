<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as AttendanceRequest;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    /**
     * @param HttpRequest
     * @return \Illuminate\View\View
     */
    public function index(HttpRequest $httpRequest)
    {

        $statusFilter = $httpRequest->query('status', 'pending');

        $query = AttendanceRequest::withoutGlobalScopes()->with(['user', 'attendance'])->latest();

        if ($statusFilter === 'pending') {

            $query->where('status', 0);
        } else {
            $query->whereIn('status', [1, 2]);
        }

        $requests = $query->paginate(15);

        return view('admin.requests.index', [
            'requests' => $requests,
            'statusFilter' => $statusFilter
        ]);
    }

    public function show(AttendanceRequest $request)
    {
        $request->load([
            'user',
            'attendance.rests',
            'requestedRests'
        ]);

        return view('admin.requests.show', compact('request'));
    }

    public function update(HttpRequest $httpRequest, AttendanceRequest $request)
    {
        if ($request->status !== 0) {
            return redirect()->route('admin.requests.show', $request)
                ->with('error', 'この申請は既に処理済みです。');
        }

        $action = $httpRequest->input('action');

        DB::beginTransaction();
        try {
            if ($action === 'approve') {

                $attendance = $request->attendance;

                $attendance->start_time = $request->corrected_start_time ?? $attendance->start_time;
                $attendance->end_time = $request->corrected_end_time ?? $attendance->end_time;
                $attendance->save();

                $attendance->rests()->delete();

                foreach ($request->requestedRests as $requestedRest) {
                    if ($requestedRest->start_time && $requestedRest->end_time) {
                        $attendance->rests()->create([
                            'start_time' => $requestedRest->start_time,
                            'end_time' => $requestedRest->end_time,
                        ]);
                    }
                }

                $request->status = 1; // 承認済み
                $request->approved_by = Auth::id();
                $request->approved_at = now();
                $request->save();

                DB::commit();

                return redirect()->route('admin.requests.show', $request)->with('success', '申請を承認し、勤怠データを更新しました。');

            } elseif ($action === 'reject') {
                $request->status = 2; // 却下
                $request->approved_by = Auth::id();
                $request->approved_at = now();
                $request->save();

                DB::commit();

                return redirect()->route('admin.requests.show', $request)->with('success', '申請を却下しました。');
            } else {
                DB::rollBack();
                return redirect()->route('admin.requests.show', $request)->with('error', '無効な操作です。');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.requests.show', $request)->with('error', '処理中にエラーが発生しました。');
        }
    }
}