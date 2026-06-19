<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Chat Model
 * 
 * @property int $id
 * @property int $group_id
 * @property int $sender_id
 * @property string $content
 * @property string|null $image_url
 * @property \Illuminate\Support\Carbon $created_at
 */
class Chat extends Model
{
    protected $table = 'chats';

    /**
     * updated_at カラムがないため、タイムスタンプ管理を調整
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'group_id',
        'sender_id',
        'content',
        'image_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * メッセージの送信者を取得
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * メッセージが投稿されたグループを取得
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}