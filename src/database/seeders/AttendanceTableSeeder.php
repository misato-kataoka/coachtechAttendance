<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 外部キー制約を一時的に無効化し、テーブルを空にする
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Attendance::truncate();
        DB::table('rests')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 設定項目 ---
        $userIds = range(2, 7);
        $period = CarbonPeriod::create('2025-03-01', '2025-06-30');
        // ----------------

        $this->command->info('Starting Attendance and Rest data seeding...');

        foreach ($userIds as $userId) {
            $user = User::find($userId);

            if (!$user) {
                $this->command->warn("User with ID: {$userId} not found. Seeding will be skipped for this user.");
                continue;
            }

            $this->command->info("Processing data for User: {$user->name} (ID: {$userId})");

            $currentWeek = -1;
            $offDays = [];

            foreach ($period as $date) {
                if ($date->weekOfYear !== $currentWeek) {
                    $currentWeek = $date->weekOfYear;
                    $daysOfWeek = range(0, 6);
                    shuffle($daysOfWeek);
                    $offDays = array_slice($daysOfWeek, 0, 2);
                }

                if (in_array($date->dayOfWeek, $offDays)) {
                    continue;
                }

                $startTime = $date->copy()->setTime(7, 30, 0)->addMinutes(rand(0, 150));
                $endTime = $date->copy()->setTime(16, 30, 0)->addMinutes(rand(0, 210));

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

                // 休憩時間（30分〜90分）を「分」で生成
                $totalRestMinutes = rand(30, 90);
                // 勤務時間を「分」で計算
                $workMinutes = $startTime->diffInMinutes($endTime);
                // 休憩が開始できる時間範囲を「分」で計算
                $possibleRestStartRangeInMinutes = $workMinutes - $totalRestMinutes;

                // 勤務時間内に休憩が収まる場合のみ記録
                if ($possibleRestStartRangeInMinutes > 0) {
                    // 休憩開始までのオフセット（ずれ）を「分」でランダムに決定
                    $restStartOffsetInMinutes = rand(0, $possibleRestStartRangeInMinutes);

                    // 休憩開始・終了時刻を計算
                    $restStartTime = $startTime->copy()->addMinutes($restStartOffsetInMinutes);
                    $restEndTime = $restStartTime->copy()->addMinutes($totalRestMinutes);

                    // 休憩記録を作成
                    DB::table('rests')->insert([
                        'attendance_id' => $attendance->id,
                        'start_time' => $restStartTime,
                        'end_time' => $restEndTime,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Seeding of Attendance and Rest data has been completed!');
    }
}