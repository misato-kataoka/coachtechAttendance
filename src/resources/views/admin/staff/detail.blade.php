@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection

@section('content')
<div class="detail-container">
    <h1 class="detail-header">勤怠詳細</h1>

    @if (session('success'))
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 1rem; border: 1px solid #c3e6cb; border-radius: .25rem; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: .25rem; margin-bottom: 1rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- ここに、バリデーションエラーが一つでもあれば、その一覧を表示します --}}
    @if ($errors->any())
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: .25rem; margin-bottom: 1rem;">
            <strong>入力内容にエラーがあります。</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $userName = $attendance->user->name ?? 'ユーザー不明';
        $workDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日');
    @endphp

    @if (isset($attendance->pendingRequest))

        @php
            $pendingRequest = $attendance->pendingRequest;
            $requestedStartTime = \Carbon\Carbon::parse($pendingRequest->corrected_start_time)->format('H:i');
            $requestedEndTime = \Carbon\Carbon::parse($pendingRequest->corrected_end_time)->format('H:i');

            // 申請された休憩時間も同様に取得
            $requestedRest1Start = isset($pendingRequest->pendingRests[0]) ? \Carbon\Carbon::parse($pendingRequest->pendingRests[0]->corrected_start_time)->format('H:i') : '-';
            $requestedRest1End = isset($pendingRequest->pendingRests[0]) ? \Carbon\Carbon::parse($pendingRequest->pendingRests[0]->corrected_end_time)->format('H:i') : '-';
            $requestedRest2Start = isset($pendingRequest->pendingRests[1]) ? \Carbon\Carbon::parse($pendingRequest->pendingRests[1]->corrected_start_time)->format('H:i') : '-';
            $requestedRest2End = isset($pendingRequest->pendingRests[1]) ? \Carbon\Carbon::parse($pendingRequest->pendingRests[1]->corrected_end_time)->format('H:i') : '-';
        @endphp

        <div class="readonly-wrapper">
            <p class="pending-message">以下の内容で修正申請中です。承認されるまでお待ちください。</p>
            <table class="detail-table readonly">
                <tbody>
                    <tr><th>名前</th><td>{{ $userName }}</td></tr>
                    <tr><th>日付</th><td>{{ $workDate }}</td></tr>
                    <tr><th>出勤・退勤</th><td>{{ $requestedStartTime }} <span class="time-separator">〜</span> {{ $requestedEndTime }}</td></tr>
                    <tr><th>休憩</th><td>{{ $requestedRest1Start }} <span class="time-separator">〜</span> {{ $requestedRest1End }}</td></tr>
                    <tr><th>休憩2</th><td>{{ $requestedRest2Start }} <span class="time-separator">〜</span> {{ $requestedRest2End }}</td></tr>
                    <tr><th>備考</th><td>{{ $pendingRequest->remarks }}</td></tr>
                </tbody>
            </table>
        </div>

    @else

    @php
        $rests = $attendance->rests->sortBy('start_time');
        $rest1 = $rests->get(0);
        $rest2 = $rests->get(1);

        $rest1_id = $rest1->id ?? null;
        $initial_rest1_start = $rest1 && $rest1->start_time ? \Carbon\Carbon::parse($rest1->start_time)->format('H:i') : '';
        $initial_rest1_end = $rest1 && $rest1->end_time ? \Carbon\Carbon::parse($rest1->end_time)->format('H:i') : '';

        $rest2_id = $rest2->id ?? null;
        $initial_rest2_start = $rest2 && $rest2->start_time ? \Carbon\Carbon::parse($rest2->start_time)->format('H:i') : '';
        $initial_rest2_end = $rest2 && $rest2->end_time ? \Carbon\Carbon::parse($rest2->end_time)->format('H:i') : '';
    @endphp

    <form action="{{ route('admin.attendance.update', ['attendance' => $attendance->id]) }}" method="POST" class="detail-form">
        @csrf
        @method('PUT')

        <table class="detail-table">
            <tbody>
                <tr><th>名前</th><td>{{ $userName }}</td></tr>
                <tr><th>日付</th><td>{{ $workDate }}</td></tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time" name="start_time" value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="end_time" value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                        <div class="form__error">
                            @error('start_time')<p>{{ $message }}</p>@enderror
                            @error('end_time')<p>{{ $message }}</p>@enderror
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        @if($rest1_id) <input type="hidden" name="rests[0][id]" value="{{ $rest1_id }}"> @endif
                        <input type="time" name="rests[0][start_time]" value="{{ old('rests.0.start_time', $initial_rest1_start) }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="rests[0][end_time]" value="{{ old('rests.0.end_time', $initial_rest1_end) }}">
                        <div class="form__error">
                            @error('rests.0.start_time')<p>{{ $message }}</p>@enderror
                            @error('rests.0.end_time')<p>{{ $message }}</p>@enderror
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        @if($rest2_id) <input type="hidden" name="rests[1][id]" value="{{ $rest2_id }}"> @endif
                        <input type="time" name="rests[1][start_time]" value="{{ old('rests.1.start_time', $initial_rest2_start) }}">
                        <span class="time-separator">〜</span>
                        <input type="time" name="rests[1][end_time]" value="{{ old('rests.1.end_time', $initial_rest2_end) }}">
                        <div class="form__error">
                            @error('rests.1.start_time')<p>{{ $message }}</p>@enderror
                            @error('rests.1.end_time')<p>{{ $message }}</p>@enderror
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks" class="remarks-textarea" rows="3">{{ old('remarks', optional($attendance)->remarks) }}</textarea>
                        <div class="form__error">
                            @error('remarks')<p>{{ $message }}</p>@enderror
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