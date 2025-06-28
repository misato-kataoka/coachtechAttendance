<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Request extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;   // 申請中
    const STATUS_APPROVED = 1;  // 承認
    const STATUS_REJECTED = 2;  // 却下

    protected static $status_texts = [
        self::STATUS_PENDING => '申請中',
        self::STATUS_APPROVED => '承認',
        self::STATUS_REJECTED => '却下',
    ];

    protected $fillable = [
        'user_id',
        'request_date',
        'reason',
        'status',
    ];

    public function getStatusTextAttribute()
    {
        return self::$status_texts[$this->status] ?? '不明';
    }

    public function user()
    {
        return $this->belongTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongTo(User::class, 'approved_by');
    }
}
