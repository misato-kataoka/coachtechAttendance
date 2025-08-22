<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Request;

class RequestTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-08-22',
            'start_time' => '2025-08-22 09:00:00',
            'end_time' => '2025-08-22 18:00:00',
        ]);
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合はバリデーションエラー(): void
    {
        $response = $this->put(route('attendance.update', $this->attendance->id), [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'remarks' => '備考',
        ]);

        $response->assertSessionHasErrors('start_time'); 
        $response->assertRedirect();
    }

    public function test_休憩開始時間が退勤時間より後の場合はバリデーションエラー()
    {
        $this->setUp();

        $invalidData = [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'remarks'    => '不正な休憩時間テスト',
            'rests'      => [
                ['start_time' => '19:00', 'end_time' => '19:30']
            ],
            'attendance_id' => $this->attendance->id,
        ];

        $response = $this->actingAs($this->user)
                        ->put(route('attendance.update', $this->attendance->id), $invalidData);

        $response->assertInvalid([
            'rests.0.start_time' => '休憩時間が不適切な値です'
        ]);
    }
    /**
     * @test
     */
    public function test_休憩終了時間が退勤時間より後の場合はバリデーションエラー()
    {
        $this->setUp();

        $invalidData = [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'remarks'    => '不正な休憩時間テスト',
            'rests'      => [
                ['start_time' => '17:00', 'end_time' => '18:30']
            ],
            'attendance_id' => $this->attendance->id,
        ];

        $response = $this->actingAs($this->user)
                        ->put(route('attendance.update', $this->attendance->id), $invalidData);

        $response->assertInvalid([
            'rests.0.end_time' => '休憩時間は勤務時間内に設定してください。'
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合はバリデーションエラー(): void
    {
        $response = $this->put(route('attendance.update', $this->attendance->id), [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'remarks' => '', // 備考を空にする
        ]);

        $response->assertSessionHasErrors(['remarks']);
        $response->assertRedirect();
    }

    /** @test */
    public function 勤怠修正申請が正常に作成され管理者が確認できる(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->setUp();

        $requestData = [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'remarks' => '電車遅延のため。',
            'attendance_id' => $this->attendance->id,
            'rests' => [],
        ];

        $response = $this->actingAs($this->user)
                        ->put(route('attendance.update', $this->attendance->id), $requestData);

        $this->assertDatabaseHas('requests', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 0,
            'remarks' => '電車遅延のため。',
        ]);
    }

    /** @test */
    public function ユーザーは申請一覧で自分の承認待ち申請を確認できる(): void
    {
        Request::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 0,
            'remarks' => '承認待ちの申請です',
        ]);

        $otherUser = User::factory()->create();
        $otherAttendance = Attendance::factory()->create(['user_id' => $otherUser->id, 'work_date' => '2025-08-23']);
        Request::factory()->create([
            'user_id' => $otherUser->id,
            'attendance_id' => $otherAttendance->id,
            'remarks' => '他人の申請',
        ]);

        $response = $this->actingAs($this->user)->get(route('requests.list'));

        $response->assertOk();
        $response->assertSee('承認待ちの申請です');
        $response->assertDontSee('他人の申請');
    }
}