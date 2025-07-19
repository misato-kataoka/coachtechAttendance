<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
    ];

    /**
     * ユーザー情報とのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩時間(Rest)モデルとのリレーション
     */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    // ▼▼▼ ここから下の計算ロジックを全面的に修正しました ▼▼▼

    /**
     * 【内部計算用】合計休憩時間を「秒」で計算する
     *
     * @return int
     */
    public function getTotalRestSecondsAttribute(): int
    {
        return $this->rests->reduce(function ($carry, $rest) {
            if ($rest->start_time && $rest->end_time) {
                $start = Carbon::parse($rest->start_time);
                $end = Carbon::parse($rest->end_time);
                $carry += $end->diffInSeconds($start);
            }
            return $carry;
        }, 0);
    }

    /**
     * 【ビュー表示用】合計休憩時間を "HH:MM:SS" 形式で取得する
     * (ビューの total_break_time_formatted から呼び出される)
     *
     * @return string
     */
    public function getTotalBreakTimeFormattedAttribute(): string
    {
        // total_rest_seconds (上の関数) の結果を使ってフォーマットする
        return gmdate('H:i:s', $this->total_rest_seconds);
    }

    /**
     * 【内部計算用】実労働時間を「秒」で計算する
     *
     * @return int
     */
    public function getActualWorkSecondsAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0; // 始業または終業がない場合は0
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // 総勤務時間（秒）
        $totalWorkSeconds = $end->diffInSeconds($start);

        // 総勤務時間（秒）から 合計休憩時間（秒）を引く
        $actualWorkSeconds = $totalWorkSeconds - $this->total_rest_seconds;

        return max(0, $actualWorkSeconds); // マイナス表示防止
    }

    /**
     * 【ビュー表示用】実労働時間を "HH:MM:SS" 形式で取得する
     * (ビューの total_work_time_formatted から呼び出される)
     *
     * @return string
     */
    public function getTotalWorkTimeFormattedAttribute(): string
    {
        // actual_work_seconds (上の関数) の結果を使ってフォーマットする
        return gmdate('H:i:s', $this->actual_work_seconds);
    }
    
    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function pendingRequest()
    {
        return $this->hasOne(Request::class)->where('status', \App\Models\Request::STATUS_PENDING);
    }
}