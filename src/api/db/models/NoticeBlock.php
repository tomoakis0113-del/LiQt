<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * NoticeBlock
 * 
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 */
class NoticeBlock extends Model
{
    public $timestamps = false;

    /**
     * モデルに関連付けるテーブル名
     */
    protected $table = 'notice_blocks';

    protected $fillable = [
        'user_id',
        'group_id'
    ];
}