<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * ResetRequest Model
 * 
 * @property int $user_id (Primary Key)
 * @property string $token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $expires_at
 */
class ResetRequest extends Model
{
    protected $table = 'reset_requests';

    /**
     * 主キーの設定
     */
    protected $primaryKey = 'user_id';

    /**
     * オートインクリメントではないため false
     */
    public $incrementing = false;

    /**
     * updated_at が存在しないため無効化
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    /**
     * 日付型のキャスト
     */
    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * トークンが有効期限内かどうかを判定する
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * 紐づくユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}