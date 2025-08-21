<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as AttendanceRequest;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    /**
     * @param HttpRequest
     * @return \Illuminate\View\View
     */
    public function index(HttpRequest $httpRequest)
    {

        $statusFilter = $httpRequest->query('status', 'pending');

        $query = AttendanceRequest::withoutGlobalScopes()->with(['user', 'attendance'])->latest();

        if ($statusFilter === 'pending') {
            //「承認待ち」タブが選択された場合
            $query->where('status', 0); // ステータスが0のものを絞り込み
        } else {
            //「承認済み」タブが選択された場合
            $query->whereIn('status', [1, 2]); // ステータスが1(承認)または2(却下)のものを絞り込み
        }

        $requests = $query->paginate(15);

        return view('admin.requests.index', [
            'requests' => $requests,
            'statusFilter' => $statusFilter
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

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                // 1. 元の勤怠レコードを取得
                $attendance = $request->attendance;

                // 2. 勤怠本体（出退勤時刻）を更新
                $attendance->start_time = $request->corrected_start_time ?? $attendance->start_time;
                $attendance->end_time = $request->corrected_end_time ?? $attendance->end_time;
                $attendance->save();

                // 3. 休憩時間(Rests)を更新
                // 3-1. 元の休憩データをすべて削除
                $attendance->rests()->delete();

                // 3-2. 申請された休憩データをループで新しく作成
                foreach ($request->requestedRests as $requestedRest) {
                    // 申請された休憩データが存在する場合のみ作成
                    if ($requestedRest->start_time && $requestedRest->end_time) {
                        $attendance->rests()->create([
                            'start_time' => $requestedRest->start_time,
                            'end_time' => $requestedRest->end_time,
                        ]);
                    }
                }

                // 4. 申請(Request)自体のステータスを更新
                $request->status = 1; // 承認済み
                $request->approved_by = Auth::id();
                $request->approved_at = now();
                $request->save();

                // 5. すべてのDB操作が成功したので、変更を確定
                DB::commit();

                return redirect()->route('admin.requests.show', $request)->with('success', '申請を承認し、勤怠データを更新しました。');

            } elseif ($action === 'reject') {
                // === 却下処理 ===
                // 却下の場合は勤怠データは更新せず、申請ステータスのみ変更
                $request->status = 2; // 却下
                $request->approved_by = Auth::id();
                $request->approved_at = now();
                $request->save();

                DB::commit();

                return redirect()->route('admin.requests.show', $request)->with('success', '申請を却下しました。');
            } else {
                DB::rollBack();
                return redirect()->route('admin.requests.show', $request)->with('error', '無効な操作です。');
            }

        } catch (\Exception $e) {
            // 6. 途中でエラーが起きたら、全ての変更を元に戻す
            DB::rollBack();
            return redirect()->route('admin.requests.show', $request)->with('error', '処理中にエラーが発生しました。');
        }
    }
}