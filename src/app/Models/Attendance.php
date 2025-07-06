<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
    ];

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalRestTimeAttribute()
    {
        $totalSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->start_time && $rest->end_time) {
            $start = Carbon::parse($rest->start_time);
            $end = Carbon::parse($rest->end_time);
            $totalSeconds += $start->diffInSeconds($end);
            }
        }
        return CarbonInterval::seconds($totalSeconds)->cascade()->format('%H:%I:%S');
    }

    public function getTotalWorkTimeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return '00:00:00';
        }

        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        $totalWorkSeconds = $startTime->diffInSeconds($endTime);

        $totalRestSeconds = CarbonInterval::createFormFormat('H:i:s', $this->total_rest_time)->totalSeconds;

        $actualWorkSeconds = $totalWorkSeconds - $totalRestSeconds;

        return CarbonInterval::seconds($actualWorkSeconds > 0 ? $actualWorkSeconds : 0)->cascade()->format('%H:%I:%S');
    }
}
