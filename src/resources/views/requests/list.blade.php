@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/requests/list.css') }}">
@endsection

@section('content')
<div class="request-container">
    <h2 class="request-title">
        申請一覧
    </h2>

    <nav class="filter-nav mb-3">
        <a href="{{ route('requests.list', ['status' => 'pending']) }}"
            class="filter-nav__link {{ $statusFilter === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('requests.list', ['status' => 'processed']) }}"
            class="filter-nav__link {{ $statusFilter !== 'pending' ? 'active' : '' }}">
            承認済み
        </a>
    </nav>

    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $request)
                <tr>
                    <td>{{ $request->status_text }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ $request->attendance->work_date }}</td>
                    <td>{{ $request->remarks }}</td>
                    <td>{{ $request->created_at->format('Y-m-d H:i')}}</td>
                    <td class="table-cell">
                        <a href="{{ route('request.show', ['request_id' => $request->id]) }}" class="detail__button-submit">詳細</a>
                    </td>
                </tr>
        @empty
            <tr>
                <td colspan="6">申請履歴はまだありません</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection