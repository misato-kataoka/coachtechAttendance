<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as AttendanceRequest;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    /**
     * @param HttpRequest
     * @return \Illuminate\View\View
     */
    public function index(HttpRequest $httpRequest)
    {
        // 表示するステータスを取得 (URLの ?status=... から)
        // 指定がなければデフォルトで 'pending' (承認待ち) を表示
        $statusFilter = $httpRequest->query('status', 'pending');

        // Requestモデルからグローバルスコープを一時的に解除してクエリを開始
        $query = AttendanceRequest::withoutGlobalScopes()->with(['user', 'attendance'])->latest();

        if ($statusFilter === 'pending') {
            //「承認待ち」タブが選択された場合
            $query->where('status', 0); // ステータスが0のものを絞り込み
        } else {
            //「承認済み」タブが選択された場合
            $query->whereIn('status', [1, 2]); // ステータスが1(承認)または2(却下)のものを絞り込み
        }

        // ページネーションを付けてデータを取得 (1ページあたり15件)
        $requests = $query->paginate(15);

        // ビューに応答を返す
        return view('admin.requests.index', [
            'requests' => $requests,
            'statusFilter' => $statusFilter // 現在のフィルタ状態をビューに渡す
        ]);
    }

    public function show(AttendanceRequest $request)
    {
        $request->load([
            'user',
            'attendance.rests',
            'requestedRests'
        ]);

        return view('admin.requests.show', compact('request'));
    }

    public function update(HttpRequest $httpRequest, AttendanceRequest $request)
    {
        // 既に処理済みの申請を再度処理しようとした場合は、エラーを返して操作を防ぐ
        if ($request->status !== 0) {
            return redirect()->route('admin.requests.show', $request)
                ->with('error', 'この申請は既に処理済みです。');
        }

        $action = $httpRequest->input('action');

        if ($action === 'approve') {
            $request->status = 1; // 1: 承認済み
            $message = '申請を承認しました。';
        } elseif ($action === 'reject') {
            $request->status = 2; // 2: 却下
            $message = '申請を却下しました。';
        } else {
            return redirect()->route('admin.requests.show', $request)
                ->with('error', '無効な操作です。');
        }

        $request->save();

        return redirect()->route('admin.requests.show', $request)
            ->with('success', $message);
    }
}