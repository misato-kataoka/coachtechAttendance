<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * 管理者ログインフォームを表示する
     */
    public function showLoginForm()
    {
        return view('auth.admin.login'); // ご提示いただいたビューファイルを返す
    }

    /**
     * 管理者ログイン処理を実行する
     */
    public function login(LoginRequest $request)
    {
        // 1. バリデーション
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. 認証を試行
        if (Auth::attempt($credentials)) {
            // 3. 認証成功後、ユーザーが管理者かチェック
            if (Auth::user()->is_admin) {
                // セッションを再生成（セキュリティ対策）
                $request->session()->regenerate();

                // 管理者勤怠一覧ページへリダイレクト
                return redirect()->intended(route('admin.attendance.index'));
            }

            // 4. 管理者でなければログアウトさせ、エラーを返す
            Auth::logout();
        }

        // 5. 認証失敗時
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }


    /**
     * 管理者をログアウトさせる
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後は管理者ログインページへリダイレクト
        return redirect()->route('admin.login');
    }
}