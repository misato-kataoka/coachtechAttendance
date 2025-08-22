<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 管理者は正常にログインできる(): void
    {
        // 準備：管理者ユーザーを作成
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 実行：管理者用ログインページにPOST
        // ルート名はあなたの実装に合わせてください (例: 'admin.login')
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        // 検証
        $response->assertRedirect('/admin/attendance');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * @test
     */
    public function 管理者ログイン時にメールアドレスは必須である(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function ログイン時にパスワードの入力は必須である(): void
    {
        $response = $this->post(route('/admin/login'), [
            'email' => 'login-test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function 誤ったパスワードではログインできない(): void
    {
        // 準備
        $user = User::factory()->create([
            'email' => 'login-test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 実行
        $response = $this->post(route('/admin/login'), [
            'email' => 'login-test@example.com',
            'password' => 'wrong-password', // 違うパスワード
        ]);

        // 検証
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
