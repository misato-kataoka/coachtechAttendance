<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user =Auth::user();
        $status = '勤務外';

        $latestAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->latest()
            ->first();

        if ($latestAttendance) {
            if ($latestAttendance->start_time && !$latestAttendance->end_time) {
                $latestBreak = $latestAttendance->rests()->latest()->first();
                if ($latestBreak && $latestBreak->start_time && !$latestBreak->end_time) {
                    $status = '休憩中';
                } else {
                    $status = '勤務中';
                }
            }

            elseif ($latestAttendance->start_time && $latestAttendance->end_time) {
                $status = '退勤済';
            }
        }
        return view('dashboard', compact('user', 'status'));
    }

    public function start(Request $request)
    {
        $userId = Auth::id();

        $existingAttendance = Attendance::where('user_id', $userId)->whereNull('end_time')->exists();

        if (!$existingAttendance) {
            Attendance::create([
                'user_id' => $userId,
                'work_date' => Carbon::today(),
                'start_time' => Carbon::now(),
                'end_time' => null,
            ]);
        }
        return redirect()->route('dashboard');
    }

    public function end(Request $request)
    {
        $userId = Auth::id();

        $attendance = Attendance::where('user_id', $userId)->whereNull('end_time')->first();

        if ($attendance) {
            $attendance->update([
                'end_time' => Carbon::now(),
            ]);
        }
        return redirect()->route('dashboard');
    }

    public function breakStart(Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)->whereNull('end_time')->first();

        if ($attendance) {
            $existingBreak = $attendance->rests()->whereNull('end_time')->exists();

            if(!$existingBreak) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => Carbon::now(),
                ]);
            }
        }
        return redirect()->route('dashboard');
    }

    public function breakEnd(Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)->whereNull('end_time')->first();

        if ($attendance) {
            $latestBreak = $attendance->rests()->whereNull('end_time')->latest()->first();

            if ($latestBreak) {
                $latestBreak->update([
                    'end_time' => Carbon::now(),
                ]);
            }
        }
        return redirect()->route('dashboard');
    }
}
