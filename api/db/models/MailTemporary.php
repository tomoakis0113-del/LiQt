<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * MailTemporary Model
 * 
 * @property int $user_id (Primary Key)
 * @property string $token
 * @property \Illuminate\Support\Carbon $created_at
 */
class MailTemporary extends Model
{
    /**
     * モデルに関連付けるテーブル名
     */
    protected $table = 'mail_temporary';

    /**
     * 主キーの設定
     * デフォルトの 'id' ではなく 'user_id' を使用
     */
    protected $primaryKey = 'user_id';

    /**
     * 主キーは自動増分（オートインクリメント）ではないため false
     */
    public $incrementing = false;

    /**
     * updated_at が存在しないため、標準のタイムスタンプ管理を調整
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
    ];

    /**
     * 属性に対するキャスト
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * この一時データに紐づくユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}