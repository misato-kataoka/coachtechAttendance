<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Request as AttendanceRequestModel;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     */
    public function index(Request $request)
    {
        $date = Carbon::parse($request->input('date', 'today'));

        $prevMonth = $date->copy()->subMonthNoOverflow();
        $nextMonth = $date->copy()->addMonthNoOverflow();
        $prevDay = $date->copy()->subDay()->toDateString();
        $nextDay = $date->copy()->addDay()->toDateString();

        $attendances = Attendance::with('user')
            ->where('work_date', $date->toDateString())
            ->paginate(10);

        return view('admin.attendances.index', compact(
            'attendances',
            'date',
            'prevDay',
            'nextDay',
            'prevMonth',
            'nextMonth'
        ));
    }


    public function show(Attendance $attendance)
    {
        $request = AttendanceRequestModel::where('attendance_id', $attendance->id)
                                ->where('status', 0)
                                ->with('user', 'attendance.rests', 'requestedRests')
                                ->first();

    if (!$request) {
        $attendance->load('user', 'rests');
        return view('admin.attendances.show', ['attendance' => $attendance]);
    }

    return view('admin.requests.show', ['request' => $request]);
}

    public function update(AttendanceRequest $request, Attendance $attendance)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $attendance->update([
                'start_time' => $validatedData['start_time'],
                'end_time'   => $validatedData['end_time'],
                'remarks'    => $validatedData['remarks'],
            ]);

            if (isset($validatedData['rests'])) {
                foreach ($validatedData['rests'] as $index => $restData) {
                    $restId = $restData['id'] ?? null;

                    if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                        Rest::updateOrCreate(
                            ['id' => $restId, 'attendance_id' => $attendance->id],
                            ['start_time' => $restData['start_time'], 'end_time' => $restData['end_time']]
                        );
                    }
                    elseif (empty($restData['start_time']) && empty($restData['end_time']) && $restId) {
                        Rest::find($restId)->delete();
                    }
                }
            }

            DB::table('requests')
                ->where('attendance_id', $attendance->id)
                ->where('status', 0)
                ->delete();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('管理者による勤怠情報の更新に失敗しました: ' . $e->getMessage());
            return back()->with('error', '勤怠情報の更新に失敗しました。');
        }

        return redirect()->route('admin.staff.show', ['staff' => $attendance->user_id, 'month' => \Carbon\Carbon::parse($attendance->work_date)->format('Y-m')])
                        ->with('success', $attendance->user->name . 'さんの勤怠情報を更新しました。');
    }
}