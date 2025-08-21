@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection

@section('content')
<div class="detail-container">
    <h1 class="detail-header">勤怠詳細</h1>

    @php
        $userName = $attendance->user->name ?? 'ユーザー不明';
        $workDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日');
    @endphp

    @if (isset($attendance->pendingRequest))
        @php
            $pendingRequest = $attendance->pendingRequest;
            $pendingRequest->load('requestedRests');

            $displayStartTimeValue = $pendingRequest->corrected_start_time ?? $attendance->start_time;
            $displayEndTimeValue = $pendingRequest->corrected_end_time ?? $attendance->end_time;

            $displayStartTime = \Carbon\Carbon::parse($displayStartTimeValue)->format('H:i');
            $displayEndTime = $displayEndTimeValue ? \Carbon\Carbon::parse($displayEndTimeValue)->format('H:i') : '---';

            $originalRests = $attendance->rests;
            $requestedRests = $pendingRequest->requestedRests;

            $rest1_start_value = optional($requestedRests->get(0))->start_time ?? optional($originalRests->get(0))->start_time;
            $rest1_end_value   = optional($requestedRests->get(0))->end_time   ?? optional($originalRests->get(0))->end_time;

            $rest2_start_value = optional($requestedRests->get(1))->start_time ?? optional($originalRests->get(1))->start_time;
            $rest2_end_value   = optional($requestedRests->get(1))->end_time   ?? optional($originalRests->get(1))->end_time;

            $displayRest1_start = $rest1_start_value ? \Carbon\Carbon::parse($rest1_start_value)->format('H:i') : '---';
            $displayRest1_end   = $rest1_end_value   ? \Carbon\Carbon::parse($rest1_end_value)->format('H:i')   : '---';
            $displayRest2_start = $rest2_start_value ? \Carbon\Carbon::parse($rest2_start_value)->format('H:i') : '---';
            $displayRest2_end   = $rest2_end_value   ? \Carbon\Carbon::parse($rest2_end_value)->format('H:i')   : '---';
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
                        <td>{{ $displayStartTime }} <span class="time-separator">〜</span> {{ $displayEndTime }}</td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>{{ $displayRest1_start }} <span class="time-separator">〜</span> {{ $displayRest1_end }}</td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>{{ $displayRest2_start }} <span class="time-separator">〜</span> {{ $displayRest2_end }}</td>
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
                                <input type="time" name="start_time" value="{{ old('start_time', $startTime) }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="end_time" value="{{ old('end_time', $endTime) }}">

                                <div class="form__error">
                                    @error('start_time')
                                        <p>{{ $message }}</p>
                                    @enderror

                                    @error('end_time')
                                        <p>{{ $message }}</p>
                                    @enderror
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>休憩</th>
                            <td>
                                <input type="time" name="rests[0][start_time]" value="{{ old('rests.0.start_time', $rest1_start_formatted) }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="rests[0][end_time]" value="{{ old('rests.0.end_time', $rest1_end_formatted) }}">

                                <div class="form__error">
                                    @error('rests.0.start_time')
                                        <p>{{ $message }}</p>
                                    @enderror

                                    @error('rests.0.end_time')
                                        <p>{{ $message }}</p>
                                    @enderror
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>休憩2</th>
                            <td>
                                <input type="time" name="rests[1][start_time]" value="{{ old('rests.1.start_time', $rest2_start_formatted) }}">
                                <span class="time-separator">〜</span>
                                <input type="time" name="rests[1][end_time]" value="{{ old('rests.1.end_time', $rest2_end_formatted) }}">

                                <div class="form__error">
                                    @error('rests.1.start_time')
                                        <p>{{ $message }}</p>
                                    @enderror

                                    @error('rests.1.end_time')
                                        <p>{{ $message }}</p>
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