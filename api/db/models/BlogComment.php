<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * BlogComment Model
 * 
 * @property int $id
 * @property int $blog_id
 * @property int $user_id
 * @property string $content
 * @property \Illuminate\Support\Carbon $created_at
 */
class BlogComment extends Model
{
    protected $table = 'blog_comments';

    /**
     * updated_at が存在しないため、タイムスタンプ管理を調整
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'blog_id',
        'user_id',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * コメントが投稿されたブログ記事を取得
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    /**
     * コメントの投稿ユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}