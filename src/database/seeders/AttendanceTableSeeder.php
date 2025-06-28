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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Attendance::truncate();
        DB::table('rests')->truncate();

        $user = User::find(1);
        if (!$user) {
            $this->command->info('User with ID:1 not found. Seeder will be skipped.');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        $period = CarbonPeriod::create('2025-03-01', '2025-06-30');

        foreach ($period as $date) {
            if ($date->isSaturday() || $date->isSunday()) {
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

            // 休憩時間（30分〜90分）を秒で生成
            $totalRestSeconds = rand(30, 90) * 60;
            // 勤務時間（秒）から休憩時間を引いて、休憩が開始できる時間範囲を計算
            $workSeconds = $startTime->diffInSeconds($endTime);
            $possibleRestStartRange = $workSeconds - $totalRestSeconds;

            // 勤務時間内に休憩が収まる場合のみ記録
            if ($possibleRestStartRange > 0) {

                $restStartOffset = rand(0, $possibleRestStartRange);
                // 休憩開始・終了時刻を計算
                $restStartTime = $startTime->copy()->addSeconds($restStartOffset);
                $restEndTime = $restStartTime->copy()->addSeconds($totalRestSeconds);

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

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Attendance and Rest data for user_id:1 have been created for 4 months.');
    }
}