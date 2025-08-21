<?php

namespace App\Http\Controllers;

use App\Models\Request as AttendanceRequest;
use App\Http\Requests\RequestRequest;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Attendance;
use App\Models\RequestedRest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index(HttpRequest $request)
    {
        $statusFilter = $request->query('status', 'pending');

        $query = Auth::user()->requests()
            ->with(['user', 'attendance'])
            ->latest();

        if ($statusFilter === 'pending') {
            $query->where('status', AttendanceRequest::STATUS_PENDING);
        } else {
            $query->whereIn('status', [
                AttendanceRequest::STATUS_APPROVED,
            ]);
        }

        $requests = $query->paginate(15);

        return view('requests.list', [
            'requests' => $requests,
            'statusFilter' => $statusFilter
        ]);
    }

    public function show($id)
    {

        $request = AttendanceRequest::with([
            'attendance.user',
            'attendance.rests',
            'requestedRests'
        ])->findOrFail($id);

        $attendance = $request->attendance;

        if (!$attendance) {
            return redirect()->route('request.list')->with('error', '関連する勤怠記録が見つかりませんでした。');
        }

        $displayData = [];

        $displayData['userName'] = $attendance->user->name ?? 'ユーザー不明';
        $displayData['workDate'] = $attendance->work_date;

        $displayData['startTime'] = !empty($request->corrected_start_time) ? $request->corrected_start_time : $attendance->start_time;
        $displayData['endTime']   = !empty($request->corrected_end_time) ? $request->corrected_end_time : $attendance->end_time;

        $displayData['rests'] = $request->requestedRests->isNotEmpty()
            ? $request->requestedRests
            : $attendance->rests;

        $displayData['remarks'] = $request->remarks;


        return view('attendances.show', [
            'attendance' => $attendance,
            'displayData' => $displayData,
        ]);
    }

    // ...

    public function store(RequestRequest $request)
    {
        $validated = $request->validated();

        \DB::beginTransaction();
    try {
        $newRequest = AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $validated['attendance_id'],
            'status' => AttendanceRequest::STATUS_PENDING,
            'remarks' => $validated['remarks'],
            'corrected_start_time' => $validated['start_time'],
            'corrected_end_time' => $validated['end_time'],
        ]);

        if (!empty($validated['rests'])) {
            foreach ($validated['rests'] as $restData) {
                if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                    RequestedRest::create([
                        'request_id' => $newRequest->id,
                        'start_time' => $restData['start_time'],
                        'end_time' => $restData['end_time'],
                    ]);
                }
            }
        }

        \DB::commit();

    } catch (\Exception $e) {
        \DB::rollBack();

        return redirect()->back()->with('error', '申請処理中にエラーが発生しました。')->withInput();
    }

    return redirect()->route('requests.list')->with('success', '修正内容を申請しました');
    }
}
