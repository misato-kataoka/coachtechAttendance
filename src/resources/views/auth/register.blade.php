@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="container">
        <h1>会員登録</h1>
            <form action="{{ route('register') }}" method="POST">
            @csrf
                <div class="form-group">
                    <label for="name">名前</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" />
                </div>
                <div class="form__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" />
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

                <div class="form-group">
                    <label for="password_confirmation">パスワード確認</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" />
                </div>
                <div class="form__error">
                    @error('password_confirmation')
                    {{ $message }}
                    @enderror
                </div>

                <button type="submit" class="submit-btn">登録する</button>
            </form>

            <p class="login-link">
                <a href="/login">ログインはこちら</a>
            </p>
        </div>
@endsection