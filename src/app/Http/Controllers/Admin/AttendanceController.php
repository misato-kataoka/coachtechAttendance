<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 日付別勤怠一覧ページ
     */
    public function index(Request $request)
    {
        // リクエストから日付を取得（なければ今日）
        $date = Carbon::parse($request->input('date', 'today'));

        // ナビゲーション用の変数を準備
        $prevMonth = $date->copy()->subMonthNoOverflow();
        $nextMonth = $date->copy()->addMonthNoOverflow();
        $prevDay = $date->copy()->subDay()->toDateString();
        $nextDay = $date->copy()->addDay()->toDateString();

        // 指定された日付の勤怠データを取得します。
        // 【最重要修正点】検索対象の列を 'start_time' から 'work_date' に変更しました。
        $attendances = Attendance::with('user')
            ->where('work_date', $date->toDateString()) // ← ここが最重要の修正箇所です！
            ->paginate(10);

        // ビューに変数を渡して表示
        return view('admin.attendances.index', compact(
            'attendances',
            'date',
            'prevDay',
            'nextDay',
            'prevMonth',
            'nextMonth'
        ));
    }

    /**
     * 勤怠詳細ページ (今後のためのプレースホルダー)
     */
    public function show(Attendance $attendance)
    {
        $attendance->load('user');
        return view('admin.attendances.show', compact('attendance'));
    }
}