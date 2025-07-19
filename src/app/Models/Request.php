<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\User;

class Request extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;   // 申請中
    const STATUS_APPROVED = 1;  // 承認
    const STATUS_REJECTED = 2;  // 却下

    protected $fillable = [
        'user_id',
        'attendance_id',
        'reason',
        'corrected_start_time',
        'corrected_end_time',
        'status',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongTo(User::class, 'approved_by');
    }

    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return '申請中';
            case self::STATUS_APPROVED:
                return '承認';
            case self::STATUS_REJECTED:
                return '却下';
            default:
                return '不明';
        }
    }
}
