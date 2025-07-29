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

            $startTime = \Carbon\Carbon::parse($pendingRequest->corrected_start_time)->format('H:i');
            $endTime = \Carbon\Carbon::parse($pendingRequest->corrected_end_time)->format('H:i');
        @endphp

        <div class="readonly-wrapper">
            <table class="detail-table readonly">
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
                        <td>{{ $startTime }} <span class="time-separator">〜</span> {{ $endTime }}</td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>{{ $startTime }} <span class="time-separator">〜</span> {{ $endTime }}</td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>{{ $startTime }} <span class="time-separator">〜</span> {{ $endTime }}</td>
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
                                <input type="time" name="start_time" value="{{ old('start_time', $startTime) }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="end_time" value="{{ old('end_time', $endTime) }}">

                                <div class="form__error">
                                @error('attendance')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>休憩</th>
                            <td>
                                @if($rest1_id)
                                    <input type="hidden" name="rests[0][id]" value="{{ $rest1_id }}">
                                @endif
                                <input type="time" name="rests[0][start_time]" value="{{ old('rest1_start', $rest1_start) }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="rests[0][end_time]" value="{{ old('rests.0.end_time', $rest1_end) }}">

                                <div class="form__error">
                                    @error('rest')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>休憩2</th>
                            <td>
                                @if($rest2_id)
                                    <input type="hidden" name="rests[1][id]" value="{{ $rest2_id }}">
                                @endif
                                <input type="time" name="rests[1][start_time]" value="{{ old('rests.1.start_time', $rest2_start) }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="rests[1][end_time]" value="{{ old('rests.1.end_time', $rest2_end) }}">

                                <div class="form__error">
                                    @error('rest')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </td>
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