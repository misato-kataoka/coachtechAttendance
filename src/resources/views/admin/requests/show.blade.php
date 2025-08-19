@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection
@section('content')

<div class="detail-container">
    <h1 class="detail-header">勤怠詳細</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php

        $attendance = $request->attendance;
        $originalRests = $attendance->rests;
        $requestedRests = $request->requestedRests;

        $displayStartTimeValue = $request->corrected_start_time ?? $attendance->start_time;
        $displayEndTimeValue = $request->corrected_end_time ?? $attendance->end_time;

        // 休憩1: 申請された休憩1があればそれを、なければ元の休憩1を使う
        $displayRest1_start_value = optional($requestedRests->get(0))->start_time ?? optional($originalRests->get(0))->start_time;
        $displayRest1_end_value   = optional($requestedRests->get(0))->end_time   ?? optional($originalRests->get(0))->end_time;
        // 休憩2: 申請された休憩2があればそれを、なければ元の休憩2を使う
        $displayRest2_start_value = optional($requestedRests->get(1))->start_time ?? optional($originalRests->get(1))->start_time;
        $displayRest2_end_value   = optional($requestedRests->get(1))->end_time   ?? optional($originalRests->get(1))->end_time;
    @endphp

    <div class="readonly-wrapper">
        <table class="detail-table">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td>{{ $request->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}</td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        {{ $displayStartTimeValue ? \Carbon\Carbon::parse($displayStartTimeValue)->format('H:i') : '-' }}
                        <span class="time-separator">〜</span>
                        {{ $displayEndTimeValue ? \Carbon\Carbon::parse($displayEndTimeValue)->format('H:i') : '-' }}
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        {{ $displayRest1_start_value ? \Carbon\Carbon::parse($displayRest1_start_value)->format('H:i') : '-' }}
                        <span class="time-separator">〜</span>
                        {{ $displayRest1_end_value ? \Carbon\Carbon::parse($displayRest1_end_value)->format('H:i') : '-' }}
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        {{ $displayRest2_start_value ? \Carbon\Carbon::parse($displayRest2_start_value)->format('H:i') : '-' }}
                        <span class="time-separator">〜</span>
                        {{ $displayRest2_end_value ? \Carbon\Carbon::parse($displayRest2_end_value)->format('H:i') : '-' }}
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>{!! nl2br(e($request->remarks)) !!}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 承認アクション --}}
    <div class="action-wrapper">
        @if($request->status === 0) {{-- 承認待ち --}}
            <form action="{{ route('admin.requests.update', $request) }}" method="POST" class="action-form">
                @csrf
                @method('PATCH')
                <button type="submit" name="action" value="approve" class="submit-button button-approve">承認</button>
            </form>

        @elseif($request->status === 1) {{-- 承認済み --}}
            <button type="button" class="submit-button button-approved" disabled>承認済み</button>
        @endif
    </div>
</div>
@endsection