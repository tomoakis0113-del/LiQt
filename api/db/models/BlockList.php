<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * BlockList Model
 * 
 * @property int $id
 * @property int $user_id (ブロックした人)
 * @property int $blocked_user_id (ブロックされた人)
 * @property \Illuminate\Support\Carbon $created_at
 */
class BlockList extends Model
{
    /**
     * モデルに関連付けるテーブル名
     */
    protected $table = 'block_list';

    /**
     * updated_at が存在しないため、タイムスタンプ管理を調整
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'blocked_user_id',
    ];

    /**
     * キャスト設定
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * ブロックしたユーザー情報を取得
     */
    public function blocker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ブロックされたユーザー情報を取得
     */
    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}