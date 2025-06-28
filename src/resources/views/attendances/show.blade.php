@extends('layouts.app')

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>
    <div class="card">
        <div class="card-header">
            {{ Carbon\Carbon::parse($attendance->date)->format('Y年m月d日') }}の勤怠記録
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>出勤:</strong> {{ $attendance->start_time ? Carbon\Carbon::parse($attendance->start_time)->format('H:i:s') : '未記録' }}</li>
            <li class="list-group-item"><strong>退勤:</strong> {{ $attendance->end_time ? Carbon\Carbon::parse($attendance->end_time)->format('H:i:s') : '未記録' }}</li>
            <li class="list-group-item"><strong>合計休憩時間:</strong> {{ $attendance->total_break_time }}</li>
            <li class="list-group-item"><strong>合計勤務時間:</strong> {{ $attendance->total_work_time }}</li>
        </ul>
        {{-- ここに休憩の内訳などを表示することも可能 --}}
        @if($attendance->breaks->isNotEmpty())
        <div class="card-body">
            <h5 class="card-title">休憩の内訳</h5>
            <ul>
            @foreach($attendance->breaks as $break)
                <li>{{ $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i:s') : '' }} - {{ $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i:s') : '' }}</li>
            @endforeach
            </ul>
        </div>
        @endif
    </div>
    <a href="{{ route('attendance.list') }}" class="btn btn-primary mt-3">一覧に戻る</a>
</div>
@endsection