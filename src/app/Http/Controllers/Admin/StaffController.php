<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     */
    public function index()
    {
        $staffMembers = User::where('is_admin', false)->paginate(15);

        return view('admin.staff.staff_list', compact('staffMembers'));
    }

    /**
     *
     * @param  \App\Models\User  $staff
     * @return \Illuminate\View\View
     */
    public function show(Request $request, User $staff)
    {
        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($monthInput)->startOfMonth();
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $attendances = Attendance::where('user_id', $staff->id)
            ->whereYear('work_date', $currentDate->year)
            ->whereMonth('work_date', $currentDate->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $calendarDays = [];
        $startDay = $currentDate->copy()->startOfMonth();
        $endDay = $currentDate->copy()->endOfMonth();

        for ($date = $startDay->copy(); $date->lte($endDay); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendances[$dateKey] ?? null;

            $totalRest = '-';
            $actualWork = '-';
            if ($attendance) {
                $totalRest = $attendance->total_break_time_formatted;
                $actualWork = $attendance->total_work_time_formatted;
            }

            $calendarDays[] = [
                'date' => $date->copy(),
                'attendance' => $attendance,
                'totalRest' => $totalRest,
                'actualWork' => $actualWork,
            ];
        }

        return view('admin.staff.show', compact(
            'staff',
            'currentDate',
            'prevMonth',
            'nextMonth',
            'calendarDays'
        ));
    }

    public function showDetail(Attendance $attendance)
    {
        $attendance->load('user', 'rests');

        return view('admin.staff.detail', compact('attendance'));
    }

    public function exportCsv(Request $request, User $staff): StreamedResponse
    {
        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($monthInput);

        $fileName = sprintf('%s_%s_勤怠一覧.csv', $currentDate->format('Ym'), $staff->name);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($staff, $currentDate) {

            $file = fopen('php://output', 'w');

            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['日付', '曜日', '出勤', '退勤', '休憩合計', '実労働時間']);

            $attendances = Attendance::where('user_id', $staff->id)
                ->whereYear('work_date', $currentDate->year)
                ->whereMonth('work_date', $currentDate->month)
                ->orderBy('work_date', 'asc')
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->work_date)->format('Y-m-d');
                });

            $startDay = $currentDate->copy()->startOfMonth();
            $endDay = $currentDate->copy()->endOfMonth();

            for ($date = $startDay->copy(); $date->lte($endDay); $date->addDay()) {
                $dateKey = $date->format('Y-m-d');
                $attendance = $attendances->get($dateKey);
                $row = [$date->format('m/d'), $date->isoFormat('ddd')];

                if ($attendance) {
                    $row[] = \Carbon\Carbon::parse($attendance->start_time)->format('H:i');
                    $row[] = $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-';
                    $row[] = $attendance->total_break_time_formatted;
                    $row[] = $attendance->total_work_time_formatted;
                } else {
                    $row = array_merge($row, ['-', '-', '-', '-']);
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}