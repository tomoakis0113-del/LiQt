<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * GroupMember Model (Intermediate table)
 * 
 * @property int $group_id
 * @property int $user_id
 * @property string $role (owner|manager|member)
 * @property \Illuminate\Support\Carbon $joined_at
 */
class GroupMember extends Model
{
    protected $table = 'group_members';

    /**
     * 複合主キーのため、主キーによる自動検索やインクリメントを無効化
     */
    protected $primaryKey = ['group_id', 'user_id'];
    public $incrementing = false;

    /**
     * joined_at のみを扱うため、標準のタイムスタンプ管理を調整
     */
    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * 所属するグループを取得
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * 所属するユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}