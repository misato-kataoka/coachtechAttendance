<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\Request as EloquentRequest;
use App\Http\Requests\AttendanceRequest;
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
        $currentDate = Carbon::createFromDate($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $userId = Auth::id();

        $attendances = Attendance::with('rests')
                                ->where('user_id', $userId)
                                ->whereYear('work_date', $year)
                                ->whereMonth('work_date', $month)
                                ->get()
                                ->keyBy(function ($item) {
                                    return Carbon::parse($item->work_date)->format('j');
                                });

        $calendarDays = [];
        $daysInMonth = $currentDate->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $attendance = $attendances->get($day);

            $totalRestSeconds = 0;
            $actualWorkSeconds = 0;

            if ($attendance && $attendance->end_time) {
                // 1. 休憩時間の合計を計算
                if ($attendance->rests) {
                    $totalRestSeconds = $attendance->rests->sum(function ($rest) {
                        $restStart = Carbon::parse($rest->start_time);
                        $restEnd = Carbon::parse($rest->end_time);
                        return $restStart->diffInSeconds($restEnd);
                    });
                }

                // 2. 実働時間を計算
                $startTime = Carbon::parse($attendance->start_time);
                $endTime = Carbon::parse($attendance->end_time);
                $totalWorkSeconds = $startTime->diffInSeconds($endTime);
                $actualWorkSeconds = $totalWorkSeconds - $totalRestSeconds;
            }

            $calendarDays[] = [
                'date' => $date,
                'attendance' => $attendance,
                'totalRest' => gmdate('H:i', $totalRestSeconds),
                'actualWork' => gmdate('H:i', $actualWorkSeconds),
            ];
        }

        return view('attendances.list', [
            'calendarDays' => $calendarDays,
            'currentDate'  => $currentDate,
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
            'year'         => $year,
            'month'        => $month,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with([
            'rests',
            'user',
            'pendingRequest.requestedRests'
            ])->findOrFail($id);

        return view('attendances.show', [
            'attendance' => $attendance,
        ]);
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $workDate = $attendance->work_date->format('Y-m-d');

    try {
        DB::beginTransaction();

        $newRequest = EloquentRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'corrected_start_time' => $workDate . ' ' . $request->input('start_time'),
            'corrected_end_time'   => $workDate . ' ' . $request->input('end_time'),
            'remarks' => $request->input('remarks'),
            'status' => \App\Models\Request::STATUS_PENDING,
        ]);

        if ($request->has('rests')) {
            foreach ($request->input('rests') as $restData) {
                if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                    $newRequest->requestedRests()->create([
                        'start_time' => $restData['start_time'],
                        'end_time' => $restData['end_time'],
                    ]);
                }
            }
        }

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', '申請処理中にエラーが発生しました。');
    }

    return redirect()->route('attendance.show', $id)
                    ->with('success', '勤怠修正の申請を送信しました。');
}
}
