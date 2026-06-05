<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * SessionLog Model
 * 
 * @property int $id
 * @property int $user_id
 * @property string $user_agent
 * @property string $ip
 * @property \Illuminate\Support\Carbon $timestamp
 */
class SessionLog extends Model
{
    protected $table = 'session_log';

    /**
     * 標準の created_at / updated_at は使用せず
     * 独自の timestamp カラムのみを使用するため false
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_agent',
        'ip',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * ログに記録されたユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}