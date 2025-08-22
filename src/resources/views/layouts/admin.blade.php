<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header-utilities">
                <a class="header__logo" href="/">
                    <img src="{{ asset('images/logo.png') }}" alt="COACHTECH Logo" />
                </a>
                <nav>
                <ul class="header-nav">
                    @if (Auth::check())
                    <li>
                        <a class="header-nav__link" href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
                    </li>
                    <li>
                        <a class="header-nav__link" href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
                    </li>
                    <li>
                        <a class="header-nav__link" href="{{ route('admin.requests.index') }}">申請一覧</a>
                    </li>
                    <li>
                        <form class="form" action="{{ route('admin.logout') }}" method="post">
                            @csrf
                            <button type="submit" class="header-nav__link" style="background: none; border: none; padding: 0;">ログアウト</button>
                        </form>
                    </li>
                    @endif
                </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
    @yield('content')
    </main>

    @yield('js')
</body>

</html>