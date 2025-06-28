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
                $latestRest = $latestAttendance->rests()->latest()->first();
                if ($latestRest && $latestRest->start_time && !$latestRest->end_time) {
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

    public function restStart(Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)->whereNull('end_time')->first();

        if ($attendance) {
            $existingRest = $attendance->rests()->whereNull('end_time')->exists();

            if(!$existingRest) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => Carbon::now(),
                ]);
            }
        }
        return redirect()->route('dashboard');
    }

    public function restEnd(Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)->whereNull('end_time')->first();

        if ($attendance) {
            $latestRest = $attendance->rests()->whereNull('end_time')->latest()->first();

            if ($latestRest) {
                $latestRest->update([
                    'end_time' => Carbon::now(),
                ]);
            }
        }
        return redirect()->route('dashboard');
    }

    public function list(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $targetDate = Carbon::createFromDate($year, $month, 1);

        $displayMonth = $targetDate->format('Y年m月');

        $prevMonth = $targetDate->copy()->subMonth();
        $nextMonth = $targetDate->copy()->addMonth();

        $attendances = Attendance::with('rests')
            ->where('user_id', Auth::id())
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->orderBy('work_date', 'asc')
            ->get();

        return view('attendances.list', compact(
            'attendances',
            'year',
            'month'
        ));
    }

    public function show(Attendance $attendance)
    {
        if ($attendance->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('attendances.show', compact('attendance'));
    }
}
