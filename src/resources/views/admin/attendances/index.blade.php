@extends('layouts.app')

@section('title', '日付別勤怠一覧')

@section('css')
{{-- ユーザー指定のCSSを読み込みます --}}
<link rel="stylesheet" href="{{ asset('css/admin/attendances/index.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="header-title">{{ $date->format('Y年n月j日') }}の勤怠</h1>

<div class="date-navigation-area">
    <!-- 前日へのリンク -->
    <a href="{{ route('admin.attendance.index', ['date' => $prevDay]) }}" class="date-nav-arrow">← 前日</a>

    <label for="date-picker" class="date-picker-container">
        <img src="{{ asset('storage/images/calendar.png') }}" alt="カレンダーから選択" class="calendar-icon">
        <span class="date-picker-display">{{ $date->format('Y / m / d') }}</span>
        <input type="date"
            id="date-picker"
            class="date-picker-input"
            value="{{ $date->toDateString() }}"
        >
    </label>

    <!-- 翌日へのリンク -->
    <a href="{{ route('admin.attendance.index', ['date' => $nextDay]) }}" class="date-nav-arrow">翌日 →</a>
</div>

    {{-- 勤怠一覧テーブル --}}
    <div class="attendance-table">
        <table>
            <thead>
                <tr>
                    <th class="table-header">名前</th>
                    <th class="table-header">勤務開始</th>
                    <th class="table-header">勤務終了</th>
                    <th class="table-header">休憩時間</th>
                    <th class="table-header">勤務時間</th>
                    <th class="table-header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                <tr>
                    <td class="table-cell">{{ $attendance->user->name ?? '退会したユーザー' }}</td>
                    <td class="table-cell">{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : 'N/A' }}</td>
                    <td class="table-cell">{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : 'N/A' }}</td>
                    <td class="table-cell">{{ $attendance->total_break_time_formatted }}</td>
                    <td class="table-cell">{{ $attendance->total_work_time_formatted }}</td> {{-- ← 勤務時間のデータを表示 --}}
                    <td class="table-cell">
                        {{-- 詳細ページへのリンクとして機能するように<a>タグに変更 --}}
                        <a href="{{-- route('admin.attendance.show', $attendance) --}}" class="detail__button-submit">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    {{-- データがない場合は6列を結合してメッセージを表示 --}}
                    <td colspan="6">この日の勤怠データはありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const datePicker = document.getElementById('date-picker');

    // 日付ピッカーの値が変更されたら、その日付のページに移動する
    // この機能だけあればOKです
    datePicker.addEventListener('change', function() {
        if (this.value) {
            const baseUrl = "{{ route('admin.attendance.index') }}";
            window.location.href = baseUrl + '?date=' + this.value;
        }
    });
});
</script>
@endsection
@endsection