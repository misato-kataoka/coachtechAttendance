@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection

@section('content')
@php
    $userName = $attendance->user->name ?? 'ユーザー不明';
    $workDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日');

    $startTime = \Carbon\Carbon::parse($attendance->start_time)->format('H:i');
    $endTime = $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '';

    // 休憩データを取得
    $rest1 = $attendance->rests->get(0);
    $rest2 = $attendance->rests->get(1);

    // 休憩1
    $rest1_id = $rest1->id ?? null;
    $rest1_start = $rest1 ? \Carbon\Carbon::parse($rest1->start_time)->format('H:i') : '';
    $rest1_end = $rest1 && $rest1->end_time ? \Carbon\Carbon::parse($rest1->end_time)->format('H:i') : '';

    // 休憩2
    $rest2_id = $rest2->id ?? null;
    $rest2_start = $rest2 ? \Carbon\Carbon::parse($rest2->start_time)->format('H:i') : '';
    $rest2_end = $rest2 && $rest2->end_time ? \Carbon\Carbon::parse($rest2->end_time)->format('H:i') : '';
@endphp

<div class="detail-container">
    <h1 class="detail-header">勤怠詳細</h1>

    <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST" class="detail-form">
        @csrf
        @method('PUT')

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
                    <td>
                        <input type="time" name="start_time" value="{{ $startTime }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="end_time" value="{{ $endTime }}">
                    </td>
                    <div class="form__error">
                        @error('attendance')
                            {{ $message }}
                        @enderror
                    </div>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        @if($rest1_id)
                            <input type="hidden" name="rests[0][id]" value="{{ $rest1_id }}">
                        @endif
                        <input type="time" name="rests[0][start_time]" value="{{ $rest1_start }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="rests[0][end_time]" value="{{ $rest1_end }}">
                    </td>
                    <div class="form__error">
                        @error('rest')
                            {{ $message }}
                        @enderror
                    </div>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        @if($rest2_id)
                            <input type="hidden" name="rests[1][id]" value="{{ $rest2_id }}">
                        @endif
                        <input type="time" name="rests[1][start_time]" value="{{ $rest2_start }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="rests[1][end_time]" value="{{ $rest2_end }}">
                    </td>
                    <div class="form__error">
                        @error('rest')
                            {{ $message }}
                        @enderror
                    </div>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks" class="remarks-textarea"></textarea>
                    </td>
                    <div class="form__error">
                        @error('remarks')
                            {{ $message }}
                        @enderror
                    </div>
                </tr>
            </tbody>
        </table>
    </form>

    <div class="form-actions">
            <button type="submit" class="submit-button">修正</button>
        </div>
</div>
@endsection