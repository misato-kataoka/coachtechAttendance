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
                    @php
                        // 申請された出勤時間があればそれを使い、なければ元の勤怠の出勤時間を使う
                        $displayStartTime = $request->corrected_start_time ?? $request->attendance->start_time;

                        // 申請された退勤時間があればそれを使い、なければ元の勤怠の退勤時間を使う
                        $displayEndTime = $request->corrected_end_time ?? $request->attendance->end_time;
                    @endphp
                    {{ \Carbon\Carbon::parse($displayStartTime)->format('H:i') }}

                    <span class="time-separator">〜</span>

                    @if($displayEndTime)
                        {{ \Carbon\Carbon::parse($displayEndTime)->format('H:i') }}
                    @else
                        {{-- 未退勤の場合に表示するテキスト（例: - や 未退勤 など） --}}
                        -
                    @endif
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        @if(isset($request->attendance->rests[0]))
                            {{ \Carbon\Carbon::parse($request->attendance->rests[0]->start_time)->format('H:i') }}
                            <span class="time-separator">〜</span>
                            {{ \Carbon\Carbon::parse($request->attendance->rests[0]->end_time)->format('H:i') }}
                        @else
                            休憩記録なし
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        @if(isset($request->attendance->rests[1]))
                            {{ \Carbon\Carbon::parse($request->attendance->rests[1]->start_time)->format('H:i') }}
                            <span class="time-separator">〜</span>
                            {{ \Carbon\Carbon::parse($request->attendance->rests[1]->end_time)->format('H:i') }}
                        @else
                            休憩記録なし
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>{!! nl2br(e($request->remarks)) !!}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 承認・却下アクション --}}
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