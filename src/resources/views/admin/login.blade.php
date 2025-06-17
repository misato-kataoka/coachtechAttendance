@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin/login.css') }}">
@endsection

@section('content')
    <div class="container">
        <h1>管理者ログイン</h1>
            <form class="form" action="{{ route('login') }}" method="post">
            @csrf
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="text" id="email" name="email" value="{{ old('email') }}" />
                </div>
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input type="password" id="password" name="password" />
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>

                <button type="submit" class="submit-btn">管理者ログインする</button>
            </form>
            <p class="register-link">
                <a href="{{ route('register') }}">会員登録はこちら</a>
            </p>
    </div>
@endsection