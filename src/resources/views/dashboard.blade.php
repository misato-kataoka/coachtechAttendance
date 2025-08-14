@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<div class="attendance-container">

<div class="attendance-status">
    <p>
        @if ($status == '勤務外')
            勤務外
        @elseif ($status == '勤務中')
            勤務中
        @elseif ($status == '休憩中')
            休憩中
        @elseif ($status == '退勤済')
            退勤済
        @endif
    </p>
</div>

<div class="attendance-clock">
    <p id="current-date"></p>
    <h1 id="current-time"></h1>
</div>

<div class="attendance-buttons">

    @if ($status === '勤務外')
        <form class="attendance-form" action="{{ route('attendance.start') }}" method="POST">
            @csrf
            <button class="attendance-button" type="submit">出勤</button>
        </form>
    @endif

    @if ($status === '勤務中')
        <form class="attendance-form" action="{{ route('attendance.end') }}" method="POST">
            @csrf
            <button class="attendance-button" type="submit">退勤</button>
        </form>
        <form class="attendance-form" action="{{ route('rest.start') }}" method="POST">
            @csrf
            <button class="attendance-button button-break" type="submit">休憩入</button>
        </form>
    @endif

    @if ($status === '休憩中')
        <form class="attendance-form" action="{{ route('rest.end') }}" method="POST">
            @csrf
            <button class="attendance-button button-break" type="submit">休憩戻</button>
        </form>
    @endif

    @if ($status === '退勤済')
        <div class="thank-you-message">
            <p>お疲れ様でした。</p>
        </div>
    @endif
</div>

</div>
@endsection

@section('scripts')
<script>
    function updateTime() {
        const now = new Date();

        const week = ["日", "月", "火", "水", "木", "金", "土"];
        const dayOfWeek = week[now.getDay()];

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const dateString = `${year}年${month}月${day}日(${dayOfWeek})`;
        const timeString = `${hours}:${minutes}`;

        document.getElementById('current-date').textContent = dateString;
        document.getElementById('current-time').textContent = timeString;
    }

    updateTime();
    setInterval(updateTime, 60000);
</script>
@endsection