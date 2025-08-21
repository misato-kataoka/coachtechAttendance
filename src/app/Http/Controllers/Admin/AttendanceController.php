<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $attendances = Attendance::with('user')
            ->where('work_date', $date->toDateString())
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
        $attendance->load('user', 'rests');
        return view('admin.requests.show', compact('attendance'));
    }

    public function update(AttendanceRequest $request, Attendance $attendance)
    {
        // 1. バリデーション済みのデータを取得
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // 2. 出勤・退勤時間、備考を更新
            $attendance->update([
                'start_time' => $validatedData['start_time'],
                'end_time'   => $validatedData['end_time'],
                'remarks'    => $validatedData['remarks'],
            ]);

            // 3. 休憩時間を更新または新規作成
            if (isset($validatedData['rests'])) {
                foreach ($validatedData['rests'] as $index => $restData) {
                    $restId = $restData['id'] ?? null;

                    // 開始・終了時刻が両方入力されている場合のみ処理
                    if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                        Rest::updateOrCreate(
                            ['id' => $restId, 'attendance_id' => $attendance->id],
                            ['start_time' => $restData['start_time'], 'end_time' => $restData['end_time']]
                        );
                    }
                    // 開始・終了時刻が両方とも空で、かつ既存のIDがある場合 (入力欄をクリアして削除するケース)
                    elseif (empty($restData['start_time']) && empty($restData['end_time']) && $restId) {
                        Rest::find($restId)->delete();
                    }
                }
            }

            // 4. もし、この勤怠に対してユーザーからの「承認待ち」リクエストがあれば、削除する
            DB::table('requests')
                ->where('attendance_id', $attendance->id)
                ->where('status', 0)
                ->delete();

            // 5. すべての処理が成功したら、変更を確定
            DB::commit();

        } catch (\Exception $e) {
            // 6. エラーが発生したら、すべての変更を取り消し
            DB::rollBack();
            Log::error('管理者による勤怠情報の更新に失敗しました: ' . $e->getMessage());
            // エラーメッセージを添えて、直前のページに戻る
            return back()->with('error', '勤怠情報の更新に失敗しました。');
        }

        // 7. 成功メッセージを添えて、スタッフの勤怠一覧ページにリダイレクト
        return redirect()->route('admin.staff.show', ['staff' => $attendance->user_id, 'month' => \Carbon\Carbon::parse($attendance->work_date)->format('Y-m')])
                        ->with('success', $attendance->user->name . 'さんの勤怠情報を更新しました。');
    }
}