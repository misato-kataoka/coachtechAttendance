<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================================
    // ID: 1 認証機能（一般ユーザー）- 会員登録
    // ==========================================================

    /**
     * @test
     * 正常なデータが入力された場合、ユーザーが登録され、ログイン状態になる
     */
    public function ユーザーは正常に会員登録できる(): void
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 実行：会員登録のエンドポイントにPOSTリクエスト
        $response = $this->post(route('register'), $userData);

        // 検証
        $response->assertRedirect(route('verification.notice')); // 登録後メール認証へリダイレクトされる
        $this->assertAuthenticated(); // ユーザーが認証（ログイン）状態であることを確認
        $this->assertDatabaseHas('users', [ // usersテーブルにデータが保存されたことを確認
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * @test
     * 名前が未入力の場合、バリデーションエラーとなる
     */
    public function 名前の入力は必須である(): void
    {
        $userData = [
            'name' => '', // 名前を空にする
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('name'); // 'name'フィールドでバリデーションエラーがあることを確認
    }

    /**
     * @test
     * メールアドレスが未入力の場合、バリデーションエラーとなる
     */
    public function メールアドレスの入力は必須である(): void
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => '', // メールアドレスを空にする
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('email');// 'email'フィールドでバリデーションエラーがあることを確認
    }

    /**
     * @test
     * パスワードが8文字未満の場合、バリデーションエラーとなる
     */
    public function パスワードは8文字以上である必要がある(): void
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass', // 8文字未満
            'password_confirmation' => 'pass',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     * 確認用パスワードが一致しない場合、バリデーションエラーとなる
     */
    public function パスワードと確認用パスワードは一致する必要がある(): void
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password', // 一致しないパスワード
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('password');
    }

    // ==========================================================
    // ID: 2 ログイン認証機能（一般ユーザー）
    // ==========================================================

    /**
     * @test
     * 登録済みのユーザーは正常にログインできる
     */
    public function 登録済みユーザーはログインできる(): void
    {
        // 準備：テスト用のユーザーを先に作成しておく
        $user = User::factory()->create([
            'email' => 'login-test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 実行：ログイン処理
        $response = $this->post(route('login'), [
            'email' => 'login-test@example.com',
            'password' => 'password123',
        ]);

        // 検証
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user); // 指定したユーザーとして認証されていることを確認
    }

    /**
     * @test
     * メールアドレスが未入力の場合、ログイン時にバリデーションエラーとなる
     */
    public function ログイン時にメールアドレスの入力は必須である(): void
    {
        $response = $this->post(route('login'), [
            'email' => '', // メールアドレスを空にする
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest(); // ログインされていないことを確認
    }

    /**
     * @test
     * パスワードが未入力の場合、ログイン時にバリデーションエラーとなる
     */
    public function ログイン時にパスワードの入力は必須である(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'login-test@example.com',
            'password' => '', // パスワードを空にする
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * @test
     * 登録内容と一致しない場合（パスワードが違う）、ログインできない
     */
    public function 誤ったパスワードではログインできない(): void
    {
        // 準備
        $user = User::factory()->create([
            'email' => 'login-test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 実行
        $response = $this->post(route('login'), [
            'email' => 'login-test@example.com',
            'password' => 'wrong-password', // 違うパスワード
        ]);

        // 検証
        $response->assertSessionHasErrors('email'); // 一般的に、Laravelはemailキーにエラーを返します
        $this->assertGuest();
    }
}
