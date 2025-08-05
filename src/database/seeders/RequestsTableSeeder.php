<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use Carbon\Carbon;

class RequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 既存のデータをクリア
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AttendanceRequest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 申請理由と時間変更のパターンを定義 (実際のカラム名に合わせる)
        $requestPatterns = [
            ['remarks' => '遅刻の為', 'type' => 'start_time', 'adjustment_hours' => 1],
            ['remarks' => '電車遅延の為', 'type' => 'start_time', 'adjustment_hours' => 2],
            ['remarks' => '体調不良の為', 'type' => 'end_time', 'adjustment_hours' => -2],
            ['remarks' => '私事都合にて', 'type' => 'end_time', 'adjustment_hours' => -3],
        ];

        // 対象の一般ユーザー (ID: 2から7) を取得
        $users = User::whereBetween('id', [2, 7])->get();

        foreach ($users as $user) {
            // ユーザーごとに2件の申請を作成
            for ($i = 0; $i < 2; $i++) {
                // ユーザー自身の勤怠記録から、まだ申請に出していないものをランダムに1件取得
                $attendance = Attendance::where('user_id', $user->id)
                                ->whereNotNull('start_time')
                                ->whereNotNull('end_time')
                                ->whereDoesntHave('requests') // この勤怠に対する申請がまだ存在しないことを確認
                                ->inRandomOrder()
                                ->first();

                // 対象の勤怠記録が見つからなければ、次のループへ
                if (!$attendance) {
                    continue;
                }

                // 4つの申請パターンからランダムに1つ選択
                $pattern = collect($requestPatterns)->random();

                // 修正後の時間を初期化
                $correctedStartTime = null;
                $correctedEndTime = null;

                // パターンに応じて、修正後の時間(corrected_~_time)を計算
                if ($pattern['type'] === 'start_time') {
                    // 出勤時刻の修正申請の場合
                    $originalStartTime = Carbon::parse($attendance->start_time);
                    $correctedStartTime = $originalStartTime->addHours($pattern['adjustment_hours'])->format('H:i:s');
                } else { // 'end_time'
                    // 退勤時刻の修正申請の場合
                    $originalEndTime = Carbon::parse($attendance->end_time);
                    $correctedEndTime = $originalEndTime->addHours($pattern['adjustment_hours'])->format('H:i:s');
                }

                // データベースに申請レコードを作成 (実際のテーブル構造に完全一致)
                AttendanceRequest::create([
                    'user_id'              => $user->id,
                    'attendance_id'        => $attendance->id,
                    'corrected_start_time' => $correctedStartTime,
                    'corrected_end_time'   => $correctedEndTime,
                    'remarks'              => $pattern['remarks'],
                    'status'               => 0, // 0: 承認待ち
                    // created_atとupdated_atはLaravelが自動で設定
                ]);
            }
        }
    }
}