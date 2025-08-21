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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AttendanceRequest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $requestPatterns = [
            ['remarks' => '遅刻の為', 'type' => 'start_time', 'adjustment_hours' => 1],
            ['remarks' => '電車遅延の為', 'type' => 'start_time', 'adjustment_hours' => 2],
            ['remarks' => '体調不良の為', 'type' => 'end_time', 'adjustment_hours' => -2],
            ['remarks' => '私事都合にて', 'type' => 'end_time', 'adjustment_hours' => -3],
        ];

        $users = User::whereBetween('id', [2, 7])->get();

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) {
                $attendance = Attendance::where('user_id', $user->id)
                                ->whereNotNull('start_time')
                                ->whereNotNull('end_time')
                                ->whereDoesntHave('requests')
                                ->inRandomOrder()
                                ->first();

                if (!$attendance) {
                    continue;
                }

                $pattern = collect($requestPatterns)->random();

                $correctedStartTime = null;
                $correctedEndTime = null;

                if ($pattern['type'] === 'start_time') {
                    $originalStartTime = Carbon::parse($attendance->start_time);
                    $correctedStartTime = $originalStartTime->addHours($pattern['adjustment_hours'])->format('H:i:s');
                } else {
                    $originalEndTime = Carbon::parse($attendance->end_time);
                    $correctedEndTime = $originalEndTime->addHours($pattern['adjustment_hours'])->format('H:i:s');
                }

                AttendanceRequest::create([
                    'user_id'              => $user->id,
                    'attendance_id'        => $attendance->id,
                    'corrected_start_time' => $correctedStartTime,
                    'corrected_end_time'   => $correctedEndTime,
                    'remarks'              => $pattern['remarks'],
                    'status'               => 0,
                ]);
            }
        }
    }
}