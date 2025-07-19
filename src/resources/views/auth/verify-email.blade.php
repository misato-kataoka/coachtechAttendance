@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/auth/verify.css')  }}">
@endsection

@section('content')
    <div class="card">
        <h2>ご登録ありがとうございます！</h2>
        <p class="message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="https://mailtrap.io/inboxes" target="_blank" rel="noopener noreferrer" class="verification-button-link">
            <button type="button" class="verification-button">認証はこちらから</button>
        </a>

        @if (session('message'))
            <div class="session-message">
                {{ session('message') }}
            </div>
        @endif

        <p>メールが届かない場合は、以下のボタンから再送信できます。</p>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="link-style-button">認証メールを再送する</button>
        </form>
    </div>
@endsection