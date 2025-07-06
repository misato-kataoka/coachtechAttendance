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

        @php
            // ログイン中のユーザーのメールアドレスを取得
            $email = Auth::user()->email;
            // '@'で分割してドメイン部分を取得
            $domain = explode('@', $email)[1];

            // 主要なメールドメインと、そのURLを対応付ける配列
            $mailServices = [
                'gmail.com' => 'https://mail.google.com/',
                'yahoo.co.jp' => 'https://mail.yahoo.co.jp/',
                'yahoo.com' => 'https://mail.yahoo.com/',
                'outlook.com' => 'https://outlook.live.com/',
                'outlook.jp' => 'https://outlook.live.com/',
                'hotmail.com' => 'https://outlook.live.com/',
                'live.jp' => 'https://outlook.live.com/',
                'icloud.com' => 'https://www.icloud.com/mail',
            ];

            // ユーザーのドメインが配列に存在する場合、そのURLを取得
            $link = isset($mailServices[$domain]) ? $mailServices[$domain] : null;
        @endphp

        {{-- もしリンク先が特定できた場合のみ、ボタンを表示する --}}
        @if ($link)
            <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="verification-button-link">
                <button type="button" class="verification-button">認証はこちらから</button>
            </a>
        @endif

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