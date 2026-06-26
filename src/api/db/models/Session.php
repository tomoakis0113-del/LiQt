<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Session Model
 * 
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Illuminate\Support\Carbon $expire
 * @property string $user_agent
 */
class Session extends Model
{
    protected $table = 'session';

    /**
     * created_at などの自動管理が不要な場合は false
     * もし created_at だけ欲しい場合は change() で追加して調整してください
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'expire',
        'user_agent',
    ];

    protected $casts = [
        'expire' => 'datetime',
    ];

    /**
     * このセッションを所有するユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * セッションが有効期限内かどうかを判定
     */
    public function isValid(): bool
    {
        return $this->expire->isFuture();
    }
}