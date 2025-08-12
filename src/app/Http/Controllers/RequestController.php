<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as AttendanceRequest;
use App\Http\Requests\RequestRequest;
use App\Models\Attendance;
use App\Models\RequestedRest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $requests = $user->requests()
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('requests.list', [
            'requests' => $requests
        ]);
    }

    public function show($id) // この$idは「申請(request)のID」
    {
        // ----------------------------------------------------
        // ステップ1：IDを使ってまず「申請」の情報を取得する
        // ----------------------------------------------------
        // 申請に紐づく勤怠記録(attendance)と、さらにその勤怠記録に紐づくユーザーと確定済み休憩(rests)も一緒に取得
        $request = AttendanceRequest::with([
            'attendance.user',
            'attendance.rests',
            'requestedRests' // この申請に紐づく「申請された休憩」
        ])->findOrFail($id);

        // ----------------------------------------------------
        // ステップ2：取得した申請情報から、大元となる「勤怠」の情報を取得
        // ----------------------------------------------------
        $attendance = $request->attendance;

        // もし何らかの理由で勤怠情報が取得できなかった場合のエラーハンドリング
        if (!$attendance) {
            // 例えば、リクエストリストにリダイレクトしてエラーメッセージを表示する
            return redirect()->route('request.list')->with('error', '関連する勤怠記録が見つかりませんでした。');
        }

        // 表示用の変数を準備
        $displayData = [];

        // ----------------------------------------------------
        // ステップ3：マージロジックを実行する（ここは以前のロジックとほぼ同じ）
        // ----------------------------------------------------

        // ユーザー名と日付は、取得した勤怠記録から取得
        $displayData['userName'] = $attendance->user->name ?? 'ユーザー不明';
        $displayData['workDate'] = $attendance->work_date;

        // ★重要★: このページは必ず「申請が存在する」ページなので、if文での分岐は不要
        // 出勤時間： 申請された時間があればそれを、なければ元の時間を使う
        $displayData['startTime'] = !empty($request->corrected_start_time) ? $request->corrected_start_time : $attendance->start_time;
        // 退勤時間： 申請された時間があればそれを、なければ元の時間を使う
        $displayData['endTime']   = !empty($request->corrected_end_time) ? $request->corrected_end_time : $attendance->end_time;

        // 休憩時間： 申請された休憩があればそれを、なければ元の休憩を使う
        $displayData['rests'] = $request->requestedRests->isNotEmpty()
            ? $request->requestedRests
            : $attendance->rests;

        // 備考： 申請された備考を優先
        $displayData['remarks'] = $request->remarks;

        // ----------------------------------------------------
        // ステップ4：ビューにデータを渡す
        // ----------------------------------------------------
        return view('attendances.show', [
            // $attendanceは、元の勤怠記録のインスタンスを渡す
            'attendance' => $attendance,
            // $displayDataは、画面表示用にマージされたデータの配列
            'displayData' => $displayData,
        ]);
    }

    // ...

    public function store(RequestRequest $request)
    {
        $validated = $request->validated();

        \DB::beginTransaction();
    try {
        // まず、親となる「申請」レコードを作成し、そのインスタンスを受け取る
        $newRequest = AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $validated['attendance_id'],
            'status' => AttendanceRequest::STATUS_PENDING,
            'remarks' => $validated['remarks'],
            'corrected_start_time' => $validated['start_time'],
            'corrected_end_time' => $validated['end_time'],
        ]);

        // フォームから送信された休憩時間のデータがあれば、ループ処理で保存する
        if (!empty($validated['rests'])) {
            foreach ($validated['rests'] as $restData) {
                // 休憩の開始・終了時間が両方入力されている場合のみ保存する
                if (!empty($restData['start_time']) && !empty($restData['end_time'])) {
                    // RequestedRestモデルを使って、新しい休憩記録を作成
                    RequestedRest::create([
                        // ★最重要★: 先ほど作成した申請のID($newRequest->id)を、ここで紐付ける
                        'request_id' => $newRequest->id,
                        'start_time' => $restData['start_time'],
                        'end_time' => $restData['end_time'],
                    ]);
                }
            }
        }

        // すべて成功したら、変更をデータベースに確定
        \DB::commit();

    } catch (\Exception $e) {
        // 何かエラーが起きたら、すべての変更を元に戻す
        \DB::rollBack();
        
        // エラーがあったことをユーザーに通知
        return redirect()->back()->with('error', '申請処理中にエラーが発生しました。')->withInput();
    }

    return redirect()->route('request.list')->with('success', '修正内容を申請しました');
    }
}
