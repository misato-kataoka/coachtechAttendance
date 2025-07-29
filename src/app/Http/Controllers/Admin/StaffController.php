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
    public function show(Request $request, User $staff) // Request $request を引数に追加
    {
        // 1. 月の決定とナビゲーション情報の生成
        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($monthInput)->startOfMonth(); // ビューに合わせて変数名を$currentDateに変更
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        // 2. 表示月の勤怠データを取得し、日付をキーにした連想配列に変換
        $attendances = Attendance::where('user_id', $staff->id)
            ->whereYear('work_date', $currentDate->year)
            ->whereMonth('work_date', $currentDate->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        // 3. リスト表示用の配列を作成
        $calendarDays = [];
        $startDay = $currentDate->copy()->startOfMonth();
        $endDay = $currentDate->copy()->endOfMonth();

        // 4. 月の初日から最終日までを1日ずつループ
        for ($date = $startDay->copy(); $date->lte($endDay); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendances[$dateKey] ?? null;

            // 5. ビューが要求する休憩時間と実働時間を計算
            $totalRest = '-';
            $actualWork = '-';
            if ($attendance) {
                // モデルのアクセサ（total_break_time_formatted, total_work_time_formatted）を利用
                $totalRest = $attendance->total_break_time_formatted;
                $actualWork = $attendance->total_work_time_formatted;
            }

            // 6. 1日分のデータを配列に追加
            $calendarDays[] = [
                'date' => $date->copy(),
                'attendance' => $attendance,
                'totalRest' => $totalRest,
                'actualWork' => $actualWork,
            ];
        }

        // 7. 必要なデータをすべてビューに渡す
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
        // 1. 月の決定
        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($monthInput);

        // 2. CSVのファイル名を決定
        $fileName = sprintf('%s_%s_勤怠一覧.csv', $currentDate->format('Ym'), $staff->name);

        // 3. ブラウザにCSVファイルをダウンロードさせるためのヘッダー情報
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // 4. CSVを生成して出力するコールバック関数
        $callback = function() use ($staff, $currentDate) {
            // 出力バッファをオープン
            $file = fopen('php://output', 'w');

            // UTF-8のBOM（バイトオーダーマーク）を追加 (Excelでの文字化け対策)
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー行を書き込む
            fputcsv($file, ['日付', '曜日', '出勤', '退勤', '休憩合計', '実労働時間']);

            // 5. 表示月の日毎のデータを取得して書き込む (showメソッドとほぼ同じロジック)
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

        // 6. ストリームとしてレスポンスを返す
        return new StreamedResponse($callback, 200, $headers);
    }
}