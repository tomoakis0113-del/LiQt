<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * User Model
 * 
 * @property int $id (Primary Key)
 * @property string $user_id
 * @property string $mail_address
 * @property string $password
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Model
{
    /**
     * モデルに関連付けるテーブル名
     * Phinxの $this->table('users') に対応
     */
    protected $table = 'users';

    /**
     * 一括割り当て可能な属性
     * DBに保存したいカラムをここに指定します
     */
    protected $fillable = [
        'user_id',
        'mail_address',
        'password',
        'is_active',
    ];

    /**
     * 属性に対するキャスト（型の強制変換）
     * boolean型や日付型をPHP側で適切に扱うために指定します
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Eloquent による created_at / updated_at の自動管理を有効にするか
     * マイグレーションに両方のカラムがあるため true に設定します
     */
    public $timestamps = true;

    /**
     * セキュリティ上の理由から、配列やJSONに変換した際に隠したい属性
     */
    protected $hidden = [
        'password',
    ];
}