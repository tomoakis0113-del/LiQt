<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * BlogLike Model
 * 
 * @property int $blog_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 */
class BlogLike extends Model
{
    protected $table = 'blog_likes';

    /**
     * 複合主キーの設定
     */
    protected $primaryKey = ['blog_id', 'user_id'];
    public $incrementing = false;

    /**
     * updated_at がないためタイムスタンプ管理を調整
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'blog_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * いいねされたブログ記事を取得
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    /**
     * いいねしたユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}