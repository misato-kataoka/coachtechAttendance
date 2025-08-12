@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection
@section('content')

<div class="detail-container">
    <h1 class="detail-header">勤怠詳細</h1>

    @if (isset($attendance->pendingRequest))
    @php
        $pendingRequest = $attendance->pendingRequest;
        $userName = $attendance->user->name ?? 'ユーザー不明';
        $workDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日');

        $requestedStartTime = \Carbon\Carbon::parse($pendingRequest->corrected_start_time)->format('H:i');
        $requestedEndTime = \Carbon\Carbon::parse($pendingRequest->corrected_end_time)->format('H:i');

        $requestedRests = $pendingRequest->requestedRests;
        $requestedRest1 = $requestedRests->get(0);
        $requestedRest2 = $requestedRests->get(1);

        $requestedRest1_start = isset($requestedRest1) ? \Carbon\Carbon::parse($requestedRest1->start_time)->format('H:i') : '---';
        $requestedRest1_end = isset($requestedRest1) ? \Carbon\Carbon::parse($requestedRest1->end_time)->format('H:i') : '---';
        $requestedRest2_start = isset($requestedRest2) ? \Carbon\Carbon::parse($requestedRest2->start_time)->format('H:i') : '---';
        $requestedRest2_end = isset($requestedRest2) ? \Carbon\Carbon::parse($requestedRest2->end_time)->format('H:i') : '---';
    @endphp

        <div class="readonly-wrapper">
            <table class="detail-table readonly>
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
                        <td>{{ $requestedStartTime }} <span class="time-separator">〜</span> {{ $requestedEndTime }}</td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>{{ $requestedRest1_start }} <span class="time-separator">〜</span> {{ $requestedRest1_end }}</td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>{{ $requestedRest2_start }} <span class="time-separator">〜</span> {{ $requestedRest2_end }}</td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>{{ $pendingRequest->remarks }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="form-actions">
                <p class="pending-message">承認待ちのため修正できません。</p>
            </div>
        </div>

        @else
        @php
        $userName = $attendance->user->name ?? 'ユーザー不明';
        $workDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日');
        $startTime = \Carbon\Carbon::parse($attendance->start_time)->format('H:i');
        $endTime = $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '';

        $rest1 = $attendance->rests->get(0);
        $rest2 = $attendance->rests->get(1);
        $rest1_start_formatted = isset($rest1) ? \Carbon\Carbon::parse($rest1->start_time)->format('H:i') : '';
        $rest1_end_formatted   = isset($rest1) ? \Carbon\Carbon::parse($rest1->end_time)->format('H:i') : '';
        $rest2_start_formatted = isset($rest2) ? \Carbon\Carbon::parse($rest2->start_time)->format('H:i') : '';
        $rest2_end_formatted   = isset($rest2) ? \Carbon\Carbon::parse($rest2->end_time)->format('H:i') : '';
    @endphp

            <form action="{{ route('request.store') }}" method="POST" class="detail-form">
                @csrf

                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

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
                                <input type="time" name="rests[0][start_time]" value="{{ $rest1_start_formatted }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="rests[0][end_time]" value="{{ $rest1_end_formatted }}">
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
                                <input type="time" name="rests[1][start_time]" value="{{ $rest2_start_formatted }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="rests[1][end_time]" value="{{ $rest2_end_formatted }}">
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
                                <textarea name="remarks" class="remarks-textarea">{{ old('remarks') }}</textarea>

                                <div class="form__error">
                                    @error('remarks')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-actions">
                    <button type="submit" class="submit-button">修正</button>
                </div>
            </form>
        @endif

</div>
@endsection