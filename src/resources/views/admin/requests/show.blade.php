@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection
@section('content')

<div class="detail-container">
    <h1 class="detail-header">勤怠詳細</h1>

    @if (session('success'))
        <div class="alert alert-success" style="background-color: #c6f6d5; color: #22543d; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" style="background-color: #fed7d7; color: #822727; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem;">{{ session('error') }}</div>
    @endif

    <div class="readonly-wrapper">
        <table class="detail-table readonly">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td>{{ $request->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}</td>
                </tr>
                <tr class="highlight-row">
                <th>出勤・退勤</th>
                <td>
                    {{ \Carbon\Carbon::parse($request->corrected_start_time)->format('H:i') }} 〜
                    {{ $request->corrected_end_time ? \Carbon\Carbon::parse($request->corrected_end_time)->format('H:i') : '変更なし' }}
                </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                    @if(isset($request->attendance->rests[0]))
                        {{ \Carbon\Carbon::parse($request->attendance->rests[0]->start_time)->format('H:i') }} 〜
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
                            {{ \Carbon\Carbon::parse($request->attendance->rests[1]->start_time)->format('H:i') }} 〜 
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

    {{-- 管理者アクション（承認・却下） --}}
    <div class="action-wrapper">
        @if($request->status === 0) {{-- 承認待ちの場合のみボタン表示 --}}
            <form action="{{ route('admin.requests.update', $request) }}" method="POST" class="action-form">
                @csrf
                @method('PATCH')
                <button type="submit" name="action" value="approve" class="submit-button" style="background-color: #48bb78;">承認</button>
            </form>
        @elseif($request->status === 1) {{-- 承認済みの場合 --}}
            <p><span class="status-badge status-approved">この申請は承認済みです</span></p>
        @elseif($request->status === 2) {{-- 却下済みの場合 --}}
            <p><span class="status-badge status-rejected">この申請は却下済みです</span></p>
        @endif
    </div>
</div>
@endsection