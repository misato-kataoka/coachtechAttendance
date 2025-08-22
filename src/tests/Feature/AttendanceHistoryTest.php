<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Carbon;

class AttendanceHistoryTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
    // =============================================
    // ID: 9 勤怠一覧情報取得機能
    // =============================================
    /**
     * @test
     */
    public function 勤怠一覧には自分の勤怠情報のみが表示される(): void
    {
        // 準備: ログインユーザーの今月の勤怠
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-08-10',
            'start_time' => '2025-08-10 09:00:00',
        ]);
        // 準備: 他のユーザーの今月の勤怠
        $anotherUser = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $anotherUser->id,
            'work_date' => '2025-08-11',
            'start_time' => '2025-08-11 10:00:00',
        ]);

        Carbon::setTestNow('2025-08-22'); // 現在を8月と仮定

        $response = $this->actingAs($this->user)->get(route('attendance.list')); // ルート名は要確認

        $response->assertOk();
        $response->assertSee('08/10');
        $response->assertSee('09:00'); // 自分の勤怠は見れる
        $response->assertDontSee('10:00'); // 他人の勤怠は見れない
    }

    /**
     * @test
     */
    public function 前月ボタンを押すと前月の勤怠情報が表示される(): void
    {
        // 準備: 今月のデータ
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-08-10',
        ]);
        // 準備: 前月のデータ
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-07-15',
        ]);

        Carbon::setTestNow('2025-08-22');

        // 実行: クエリパラメータで前月を指定
        $response = $this->actingAs($this->user)->get(route('attendance.list', ['year' => '2025', 'month' => '07']));

        $response->assertOk();
        $response->assertSee('2025/07'); // 画面のどこかに「2025年07月」のような表示があるはず
        $response->assertSee('07/15'); // 前月の日付が見える
        $response->assertDontSee('08/10'); // 今月の日付は見えない
    }

    public function 翌月ボタンを押すと翌月の勤怠情報が表示される(): void
    {
        // 準備: 今月のデータ
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-08-10',
        ]);
        // 準備: 翌月のデータ
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-09-15',
        ]);

        Carbon::setTestNow('2025-08-22');

        // 実行: クエリパラメータで前月を指定
        $response = $this->actingAs($this->user)->get(route('attendance.list', ['year' => '2025', 'month' => '09']));

        $response->assertOk();
        $response->assertSee('2025/09'); // 画面のどこかに「2025年07月」のような表示があるはず
        $response->assertSee('09/15'); // 前月の日付が見える
        $response->assertDontSee('08/10'); // 今月の日付は見えない
    }

    /**
     * @test
     */
    public function 勤怠一覧の詳細ボタンから勤怠詳細画面に遷移できる(): void
    {
        $attendance = Attendance::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('attendance.show', $attendance->id));

        $response->assertOk();
        $response->assertSee('勤怠詳細'); // 詳細画面のタイトルなど
    }

    // =============================================
    // ID: 10 勤怠詳細情報取得機能
    // =============================================

    /**
     * @test
     */
    public function 勤怠詳細画面に正しい情報が表示される(): void
    {
        // 準備: 休憩記録も含めた勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-08-20',
            'start_time' => '2025-08-20 09:01:02',
            'end_time' => '2025-08-20 18:03:04',
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-08-20 12:05:06',
            'end_time' => '2025-08-20 13:07:08',
        ]);

        // 実行
        $response = $this->actingAs($this->user)->get(route('attendance.show', $attendance->id));

        // 検証
        $response->assertOk();
        $response->assertSee($this->user->name); // ログインユーザーの名前
        $response->assertSee('2025年8月20日'); // 勤務日
        $response->assertSee('09:01'); // 出勤時刻
        $response->assertSee('18:03'); // 退勤時刻
        $response->assertSee('12:05'); // 休憩開始時刻
        $response->assertSee('13:07'); // 休憩終了時刻
    }
}