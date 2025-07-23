@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@section('css')
    {{-- 必要であれば、このページ専用のCSSを読み込む --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/admin/staff_list.css') }}"> --}}
    <style>
        /* 簡単なスタイルをここに直接記述します */
        .staff-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .staff-table th, .staff-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .staff-table th {
            background-color: #f2f2f2;
        }
        .pagination {
            margin-top: 20px;
        }
    </style>
@endsection

@section('content')
<div class="container">
    <h1 class="header-title">スタッフ一覧</h1>

    <div class="staff-list">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staffMembers as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        <a href="{{ route('admin.staff.show', ['staff' => $staff->id]) }}">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">登録されているスタッフはいません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $staffMembers->links() }}
    </div>
</div>
@endsection