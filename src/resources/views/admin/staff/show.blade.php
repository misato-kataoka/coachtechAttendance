@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/list.css') }}">
@endsection

@section('content')

<div class="attendance-container">
    <h2 class="attendance-title">
        {{ $staff->name }}さんの勤怠
    </h2>

    <div class="month-navigation">
        <a href="{{ route('admin.staff.show', ['staff' => $staff->id, 'month' => $prevMonth->format('Y-m')]) }}">
            ←前月
        </a>
        <h3 class="current-month-display">
            <img src="{{ asset('storage/images/calendar.png') }}" alt="calendar" class="calendar-icon">
            <span>{{ $currentDate->format('Y/m') }}</span>
        </h3>
        <a href="{{ route('admin.staff.show', ['staff' => $staff->id, 'month' => $nextMonth->format('Y-m')]) }}">
            翌月→
        </a>
    </div>

    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr class="table-header-row">
                    <th class="table-header">日付</th>
                    <th class="table-header">出勤</th>
                    <th class="table-header">退勤</th>
                    <th class="table-header">休憩</th>
                    <th class="table-header">合計</th>
                    <th class="table-header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($calendarDays as $day)
                    <tr class="table-body-row">
                        <td class="table-cell">{{ $day['date']->format('m/d') }}({{ $day['date']->isoFormat('ddd') }})</td>

                        @if ($day['attendance'])
                            {{-- 記録がある場合 --}}
                            <td class="table-cell">{{ \Carbon\Carbon::parse($day['attendance']->start_time)->format('H:i') }}</td>
                            <td class="table-cell">{{ $day['attendance']->end_time ? \Carbon\Carbon::parse($day['attendance']->end_time)->format('H:i') : '-' }}</td>
                            <td class="table-cell">{{ $day['totalRest'] }}</td>
                            <td class="table-cell">{{ $day['actualWork'] }}</td>
                            <td class="table-cell">
                                <button class="detail__button-submit" type="button">詳細</button>
                            </td>
                        @else
                            {{-- 記録がない場合 --}}
                            <td class="table-cell">-</td>
                            <td class="table-cell">-</td>
                            <td class="table-cell">-</td>
                            <td class="table-cell">-</td>
                            <td class="table-cell">
                                <button class="detail__button-submit" type="button" disabled>詳細</button>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="csv-export-wrapper" style="text-align: right; margin-top: 20px;">
    <form action="{{ route('admin.staff.exportCsv', ['staff' => $staff->id]) }}" method="GET">
        <input type="hidden" name="month" value="{{ $currentDate->format('Y-m') }}">
        <button type="submit" class="csv-export-button" style="padding: 10px 20px; font-size: 16px;">CSV出力</button>
    </form>
</div>
@endsection