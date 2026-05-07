<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Group Model
 * 
 * @property int $id
 * @property string $name
 * @property string|null $group_icon_url
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon $created_at
 */
class Group extends Model
{
    /**
     * モデルに関連付けるテーブル名
     */
    protected $table = 'groups';

    /**
     * updated_at カラムがないため、タイムスタンプ管理を調整
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    /**
     * 一括割り当て可能な属性
     */
    protected $fillable = [
        'name',
        'group_icon_url',
        'is_public',
    ];

    /**
     * 属性に対するキャスト
     * is_public を bool 型として扱えるようにします
     */
    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * このグループに所属するメッセージを取得（例）
     */
    public function chats()
    {
        return $this->hasMany(Chat::class, 'group_id');
    }

    /**
     * このグループに参加しているユーザーを取得（中間テーブルがある場合）
     */
    public function members()
    {
        // group_members のような中間テーブルを想定する場合
        // return $this->belongsToMany(User::class, 'group_members');
    }
}