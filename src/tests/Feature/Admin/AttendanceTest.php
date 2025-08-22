<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user1;
    private User $user2;

    /**
     * 各テスト実行前のセットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();
        // 管理者ユーザーと一般ユーザーを2名作成
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
    }

    // --- 12. 勤怠一覧情報取得機能（管理者） ---

    /**
     * @test
     * 12-1, 12-2: 管理者はその日の全ユーザーの勤怠情報と現在日付を確認できる
     */
    public function test_管理者は勤怠一覧で当日の全勤怠と日付を確認できる()
    {
        // 準備: 今日の日付で2人分の勤怠データを作成
        Attendance::factory()->create(['user_id' => $this->user1->id, 'work_date' => today()]);
        Attendance::factory()->create(['user_id' => $this->user2->id, 'work_date' => today()]);
        // 比較用: 昨日のデータも作成しておく
        Attendance::factory()->create(['user_id' => $this->user1->id, 'work_date' => today()->subDay()]);

        // 管理者として勤怠一覧ページにアクセス
        $response = $this->actingAs($this->admin)->get(route('admin.attendances.index'));

        $response->assertOk(); // ページが正常に表示される
        $response->assertSee(today()->format('Y-m-d')); // 今日の日付が表示される
        $response->assertSee($this->user1->name); // ユーザー1の名前が表示される
        $response->assertSee($this->user2->name); // ユーザー2の名前が表示される
    }

    /**
     * @test
     * 12-3: 管理者は「前日」ボタンで前日の勤怠情報を表示できる
     */
    public function test_管理者は前日の勤怠情報を表示できる()
    {
        // 昨日の日付で勤怠データを作成
        $yesterdayAttendance = Attendance::factory()->create(['user_id' => $this->user1->id, 'work_date' => today()->subDay()]);

        // 昨日の日付を指定して勤怠一覧ページにアクセス
        $yesterday = today()->subDay()->format('Y-m-d');
        $response = $this->actingAs($this->admin)->get(route('admin.attendances.index', ['date' => $yesterday]));

        $response->assertOk();
        $response->assertSee($yesterday); // 昨日の日付が表示される
        $response->assertSee($this->user1->name); // 昨日の勤怠データを持つユーザーが表示される
    }

    /**
     * @test
     * 12-4: 管理者は「翌日」ボタンで翌日の勤怠情報を表示できる
     */
    public function test_管理者は翌日の勤怠情報を表示できる()
    {
        // 明日の日付で勤怠データを作成
        $tomorrowAttendance = Attendance::factory()->create(['user_id' => $this->user1->id, 'work_date' => today()->addDay()]);

        // 明日の日付を指定して勤怠一覧ページにアクセス
        $tomorrow = today()->addDay()->format('Y-m-d');
        $response = $this->actingAs($this->admin)->get(route('admin.attendances.index', ['date' => $tomorrow]));

        $response->assertOk();
        $response->assertSee($tomorrow); // 明日の日付が表示される
        $response->assertSee($this->user1->name); // 明日の勤怠データを持つユーザーが表示される
    }


    // --- 13. 勤怠詳細情報取得・修正機能（管理者） ---

    /**
     * @test
     * 13-1: 管理者は選択した勤怠の詳細画面を正しく表示できる
     */
    public function test_管理者は勤怠詳細画面を正しく表示できる()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user1->id,
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.attendances.show', $attendance->id));

        $response->assertOk();
        $response->assertSee($this->user1->name);
        $response->assertSee('09:00');
    }

    /**
     * @test
     * 13-2: 管理者が更新時に出勤時間を退勤時間より後にするとエラー
     */
    public function test_管理者更新時に出勤時間が退勤時間より後だとバリデーションエラー()
    {
        $attendance = Attendance::factory()->create(['user_id' => $this->user1->id]);
        $invalidData = [
            'start_time' => '19:00',
            'end_time'   => '18:00',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.attendances.update', $attendance->id), $invalidData);

        $response->assertInvalid([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * @test
     * 13-3, 13-4: 管理者が更新時に休憩時間を勤務時間外にするとエラー
     */
    public function test_管理者更新時に休憩時間が勤務時間外だとバリデーションエラー()
    {
        $attendance = Attendance::factory()->create(['user_id' => $this->user1->id]);
        $invalidData1 = [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'rests'      => [['start_time' => '19:00', 'end_time' => '19:30']],
        ];

        $invalidData2 = [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'rests'      => [['start_time' => '17:00', 'end_time' => '18:30']],
        ];

        $response1 = $this->actingAs($this->admin)->put(route('admin.attendances.update', $attendance->id), $invalidData1);
        $response1->assertInvalid([
            'rests.0.start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);

        $response2 = $this->actingAs($this->admin)->put(route('admin.attendances.update', $attendance->id), $invalidData2);
        $response2->assertInvalid([
            'rests.0.end_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }
}