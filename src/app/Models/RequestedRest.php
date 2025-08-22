<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedRest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public const ACTION_ADD = 0;
    public const ACTION_UPDATE = 1;
    public const ACTION_DELETE = 2;

    protected $fillable = [
        'request_id',
        'start_time',
        'end_time',
        'action',
        'original_rest_id',
    ];
}