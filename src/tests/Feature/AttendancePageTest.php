<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendancePageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * @test
     * ID: 4 日時取得機能
     */
    /*public function 勤怠ページに現在の日時が表示される(): void
    {
        // 準備：時間を固定する
        $now = Carbon::parse('2025-08-21 10:30:00');
        Carbon::setTestNow($now);

        $user = User::factory()->create();

        // 実行 & 検証
        $response = $this->actingAs($user)
                         ->get(route('dashboard')); // 勤怠ページのルート名

        $response->assertOk();
        $response->assertSee($now->format('Y-m-d')); // 日付が表示されているか
        $response->assertSee($now->format('H:i:s')); // 時刻が表示されているか
    }*/

    /**
     * @test
     * ID: 5 ステータス確認機能 - 勤務外
     */
    public function 勤務開始前のユーザーには勤務外と表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('勤務外');
        $response->assertDontSee('勤務中');
    }

    /**
     * @test
     * ID: 5 ステータス確認機能 - 出勤中
     */
    public function 勤務中のユーザーには勤務中と表示される(): void
    {
        $user = User::factory()->create();
        // 準備：今日の出勤記録を作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => null,
        ]);

        $response = $this->actingAs($user)
                        ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('勤務中');
    }

    /**
     * @test
     * ID: 5 ステータス確認機能 - 休憩中
     */
    public function 休憩中のユーザーには休憩中と表示される(): void
    {
        $user = User::factory()->create();
        // 準備：出勤記録と、開始された休憩記録を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => null,
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::today()->setHour(12),
            'end_time' => null, // end_timeがnullなら休憩中
        ]);

        $response = $this->actingAs($user)
                        ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('休憩中');
    }

    /**
     * @test
     * ID: 5 ステータス確認機能 - 退勤済
     */
    public function 退勤済のユーザーには退勤済と表示される(): void
    {
        $user = User::factory()->create();
        // 準備：完了した今日の出勤記録を作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => Carbon::today()->setHour(18), // end_timeがあれば退勤済
        ]);

        $response = $this->actingAs($user)
                        ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('退勤済');
    }

    // ==========================================================
    // ID: 6 出勤機能
    // ==========================================================

    /**
     * @test
     */
    public function 出勤ボタンを押すと勤務中になり出勤時刻が記録される(): void
    {
        $user = User::factory()->create();

        // 実行: 出勤ボタンに相当するルートにPOSTリクエスト
        $response = $this->actingAs($user)->post(route('attendance.start'));

        // 検証
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'end_time' => null, // end_timeはまだない
        ]);

        // 画面の表示を確認
        $this->actingAs($user)->get(route('dashboard'))
            ->assertSee('勤務中');
    }

    /**
     * @test
     */
    public function 一度出勤したユーザーには出勤ボタンが表示されない(): void
    {
        $user = User::factory()->create();
        // 準備: 既に出勤済みの状態にする
        Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => null,
        ]);

        // 実行 & 検証
        $this->actingAs($user)->get(route('dashboard'))
            ->assertDontSee('出勤');
    }

    // ==========================================================
    // ID: 7 休憩機能
    // ==========================================================
    /**
     * @test
     */
    public function 勤務中に休憩ボタンを押すと休憩中になる(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([ // 準備: 出勤済みの状態
            'user_id' => $user->id,
            'start_time' => now()->subHour(),
            'end_time' => null
        ]);

        // 実行
        $response = $this->actingAs($user)->post(route('rest.start'));

        // 検証
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
            'end_time' => null,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertSee('休憩中');
    }

    /**
     * @test
     */
    public function 休憩中に休憩戻ボタンを押すと勤務中に戻る(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'start_time' => Carbon::today()->setHour(9),
            'end_time' => null
        ]);
        $rest = Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::today()->setHour(12),
            'end_time' => null
        ]);

        // 実行
        $response = $this->actingAs($user)->post(route('rest.end'));

        // 検証
        $response->assertRedirect(route('dashboard'));
        // 休憩記録が終了したことを確認
        $this->assertDatabaseMissing('rests', [
            'id' => $rest->id,
            'end_time' => null,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertSee('勤務中');
    }

    // ==========================================================
    // ID: 8 退勤機能
    // ==========================================================

    /**
     * @test
     */
    public function 勤務中に退勤ボタンを押すと退勤済になる(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(), // work_dateを今日に固定
            'start_time' => Carbon::today()->setHour(9), // start_timeも今日に
            'end_time' => null,
        ]);

        // 実行
        $response = $this->actingAs($user)->post(route('attendance.end'));

        // 検証
        $response->assertRedirect(route('dashboard'));
        // 出勤記録にend_timeが記録されたことを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'end_time' => $attendance->fresh()->end_time, // end_timeがnullでないことを確認
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertSee('退勤済');
    }
}