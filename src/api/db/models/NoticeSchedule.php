<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * NoticeSchedule
 * 
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property bool $is_checking
 */
class NoticeSchedule extends Model
{
    public $timestamps = false;

    /**
     * モデルに関連付けるテーブル名
     */
    protected $table = 'notice_schedules';

    protected $fillable = [
        'user_id',
        'content',
        'is_checking'
    ];
}