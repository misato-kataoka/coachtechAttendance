@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/list.css') }}">
@endsection

@section('content')

<div class="attendance-container">
    <h2 class="attendance-title">
        勤怠一覧
    </h2>

    @php
        $currentDate = \Carbon\Carbon::createFromFormat('Y-m', $year . '-' . $month);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
    @endphp

    <div class="month-navigation">
        <a href="{{ route('attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="month-nav-link">
            &laquo; {{ $prevMonth->format('Y年m月') }}
        </a>
        <h3 class="current-month-display">
            {{ $currentDate->format('Y年m月') }}
        </h3>
        <a href="{{ route('attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="month-nav-link">
            {{ $nextMonth->format('Y年m月') }} &raquo;
        </a>
    </div>

    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th class="table-header">日付</th>
                    <th class="table-header">出勤</th>
                    <th class="table-header">退勤</th>
                    <th class="table-header">休憩</th>
                    <th class="table-header">合計</th>
                    <th class="table-header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                @php
                    $totalRestSeconds = $attendance->rests->sum(function ($rest) {
                        return \Carbon\Carbon::parse($rest->start_time)->diffInSeconds(\Carbon\Carbon::parse($rest->end_time));
                    });
                    $totalWorkSeconds = \Carbon\Carbon::parse($attendance->start_time)->diffInSeconds(\Carbon\Carbon::parse($attendance->end_time));
                    $actualWorkSeconds = $totalWorkSeconds - $totalRestSeconds;
                @endphp
                <tr>
                    <td class="table-cell">
                        <p class="cell-text">{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d (D)') }}</p>
                    </td>
                    <td class="table-cell">
                        <p class="cell-text">{{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}</p>
                    </td>
                    <td class="table-cell">
                        <p class="cell-text">{{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}</p>
                    </td>
                    <td class="table-cell">
                        <p class="cell-text">{{ gmdate('H:i', $totalRestSeconds) }}</p>
                    </td>
                    <td class="table-cell">
                        <p class="cell-text">{{ gmdate('H:i', $actualWorkSeconds) }}</p>
                    </td>
                    <td class="table-cell">
                        <button class="detail__button-submit" type="submit">詳細</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection