<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Blog Model
 * 
 * @property int $id
 * @property int $author_id
 * @property int|null $group_id
 * @property string $title
 * @property string $content (Markdown)
 * @property string|null $tags (Comma separated)
 * @property string $visibility (public|private|group)
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Blog extends Model
{
    protected $table = 'blogs';

    /**
     * created_at, updated_at の両方が存在するため
     * 標準のタイムスタンプ管理を有効にします
     */
    public $timestamps = true;

    protected $fillable = [
        'author_id',
        'group_id',
        'title',
        'content',
        'tags',
        'visibility',
    ];

    /**
     * キャスト設定
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 投稿者を取得
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * 紐づいているグループを取得（存在する場合）
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * この記事へのいいね一覧を取得
     */
    public function likes()
    {
        return $this->hasMany(BlogLike::class, 'blog_id');
    }

    /**
     * この記事へのコメント一覧を取得
     */
    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'blog_id');
    }
}