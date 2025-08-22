<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Rest;
use App\Models\Request;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     */
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    /**
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
     *
     * @return string
     */
    public function getTotalBreakTimeFormattedAttribute(): string
    {
        $seconds = $this->total_rest_seconds;
        if ($seconds <= 0) {
            return '00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
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

        $totalWorkSeconds = $end->diffInSeconds($start);

        $actualWorkSeconds = $totalWorkSeconds - $this->total_rest_seconds;

        return max(0, $actualWorkSeconds);
    }

    /**
     *
     * @return string
     */
    public function getTotalWorkTimeFormattedAttribute(): string
    {
        $seconds = $this->actual_work_seconds;

        if ($seconds <= 0) {
            return '00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
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