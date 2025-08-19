@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection

@section('content')
<div class="detail-container">
    <h1 class="detail-header">勤怠詳細</h1>

    @php
        $userName = $displayData['userName'];
        $workDate = \Carbon\Carbon::parse($displayData['workDate'])->format('Y年n月j日');
        $startTime = $displayData['startTime'] ? \Carbon\Carbon::parse($displayData['startTime'])->format('H:i') : '---';
        $endTime = $displayData['endTime'] ? \Carbon\Carbon::parse($displayData['endTime'])->format('H:i') : '---';

        // 休憩時間の取得
        $rest1 = $displayData['rests']->get(0);
        $rest2 = $displayData['rests']->get(1);
        $rest1_start = isset($rest1) ? \Carbon\Carbon::parse($rest1->start_time)->format('H:i') : '---';
        $rest1_end   = isset($rest1) ? \Carbon\Carbon::parse($rest1->end_time)->format('H:i') : '---';
        $rest2_start = isset($rest2) ? \Carbon\Carbon::parse($rest2->start_time)->format('H:i') : '---';
        $rest2_end   = isset($rest2) ? \Carbon\Carbon::parse($rest2->end_time)->format('H:i') : '---';
    @endphp

    <table class="detail-table">
        <tbody>
            <tr>
                <th>名前</th>
                <td>{{ $userName }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $workDate }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                {{-- 承認待ちの場合、修正箇所が分かるようにCSSで印をつけるとなお良い --}}
                <td>{{ $startTime }} <span class="time-separator">〜</span> {{ $endTime }}</td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>{{ $rest1_start }} <span class="time-separator">〜</span> {{ $rest1_end }}</td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>{{ $rest2_start }} <span class="time-separator">〜</span> {{ $rest2_end }}</td>
            </tr>
            <tr>
                <th>備考</th>
                <td>{{ $displayData['remarks'] }}</td>
            </tr>
        </tbody>
    </table>

    @if (isset($attendance->pendingRequest))
        <div class="form-actions">
            <p class="pending-message">承認待ちのため修正できません。</p>
        </div>
    @else
        <form action="{{ route('request.store') }}" method="POST" class="detail-form">
            @csrf
            <button type="submit" class="submit-button">修正</button>
        </form>
    @endif
</div>
@endsection