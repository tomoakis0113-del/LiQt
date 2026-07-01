<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * AutoReply
 * 
 * @property int $id
 * @property int $group_id
 * @property int $chat_id
 * @property bool $is_checking
 */
class ReplySchedule extends Model
{
    public $timestamps = false;

    /**
     * モデルに関連付けるテーブル名
     */
    protected $table = 'reply_schedules';

    protected $fillable = [
        "group_id","chat_id","is_checking"
    ];
}