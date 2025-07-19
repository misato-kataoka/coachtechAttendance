<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as AttendanceRequest;
use App\Http\Requests\RequestRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $requests = $user->requests()
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('requests.list', [
            'requests' => $requests
        ]);
    }

    public function show($request_id)
    {
        $request = \App\Models\Request::with('attendance.user', 'attendance.rests', 'attendance.pendingRequest')->findOrFail($request_id);

        $attendance = $request->attendance;

        return view('attendances.show', ['attendance' => $attendance]);
    }

    public function store(RequestRequest $request)
    {
        $validated = $request->validated();

        $newRequest = new AttendanceRequest();

        AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $validated['attendance_id'],
            'status' => AttendanceRequest::STATUS_PENDING,

            'reason' => $validated['remarks'],
            'corrected_start_time' => $validated['start_time'],
            'corrected_end_time' => $validated['end_time'],
        ]);

        return redirect()->route('request.list')->with('success', '修正内容を申請しました');
    }
}
