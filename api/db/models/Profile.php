<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Profile Model
 * 
 * @property int $user_id (Primary Key)
 * @property string $display_name
 * @property string|null $introduction
 * @property string|null $icon_url
 * @property string|null $tags
 */
class Profile extends Model
{
    protected $table = 'profiles';

    /**
     * 主キーの設定
     * デフォルトの 'id' ではなく 'user_id' を使用
     */
    protected $primaryKey = 'user_id';

    /**
     * 主キーが数値（int）でない場合やオートインクリメントでない場合は
     * $incrementing を false に設定します
     */
    public $incrementing = false;

    /**
     * マイグレーションに timestamps() がないため、自動管理を無効化
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'display_name',
        'introduction',
        'icon_url',
        'tags',
    ];

    /**
     * このプロフィールに紐づくユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}