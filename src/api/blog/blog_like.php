<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログいいね切り替えAPI
 * 必要なパラメータ:
 * - csrf_token
 * - blog_id
 *  
 * レスポンス:
 * - success: true/false
 * - message
 * - data:
 *   - is_liked: true/false
 */

try{
    //POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    //セッション管理
    $sessionHandler = new lib\Session();

    //サインイン確認
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    //パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $blogId    = $_POST['blog_id'] ?? '';

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($blogId === ''){
        lib\Util::responseError(400,'ブログIDを指定してください');
    }

    if(!ctype_digit((string)$blogId)){
        lib\Util::responseError(400,'ブログIDの形式が正しくありません');
    }

    $blogId = (int)$blogId;

    //ログイン中ユーザーID取得
    $currentUserId = $sessionHandler->getCurrentUserID();

//ブログ存在確認
$blog = models\Blog::query()
    ->where('id', $blogId)
    ->first([
        'id',
        'user_id',
        'visibility',
        'group_id'
    ]);

if(!$blog){
    lib\Util::responseError(404,'ブログが見つかりません');
}

$blogAuthorId = (int)$blog->user_id;

// 相手が自分をブロックしているか
$blockedByAuthor = models\BlockList::query()
    ->where('user_id', $blogAuthorId)
    ->where('blocked_user_id', $currentUserId)
    ->first(['id']);

// 自分が相手をブロックしているか
$blockedByMe = models\BlockList::query()
    ->where('user_id', $currentUserId)
    ->where('blocked_user_id', $blogAuthorId)
    ->first(['id']);

if($blockedByAuthor || $blockedByMe){
    lib\Util::responseError(403, 'ブロック関係にあるため、このブログにはいいねできません');
}//ブログ存在確認
$blog = models\Blog::query()
    ->where('id', $blogId)
    ->first([
        'id',
        'user_id',
        'visibility',
        'group_id'
    ]);

if(!$blog){
    lib\Util::responseError(404,'ブログが見つかりません');
}

$blogAuthorId = (int)$blog->user_id;

// 相手が自分をブロックしているか
$blockedByAuthor = models\BlockList::query()
    ->where('user_id', $blogAuthorId)
    ->where('blocked_user_id', $currentUserId)
    ->first(['id']);

// 自分が相手をブロックしているか
$blockedByMe = models\BlockList::query()
    ->where('user_id', $currentUserId)
    ->where('blocked_user_id', $blogAuthorId)
    ->first(['id']);

if($blockedByAuthor || $blockedByMe){
    lib\Util::responseError(403, 'ブロック関係にあるため、このブログにはいいねできません');
}


// 非公開ブログは投稿者本人だけいいね可能
if($blog->visibility === 'private'){

    if((int)$blog->author_id !== (int)$currentUserId){
        lib\Util::responseError(403,'非公開ブログにはいいねできません');
    }

}

// グループブログは所属メンバーだけいいね可能
if($blog->visibility === 'group'){

    if(empty($blog->group_id)){
        lib\Util::responseError(403,'このグループブログにはいいねできません');
    }

    $member = models\GroupMember::query()
        ->where('group_id', $blog->group_id)
        ->where('user_id', $currentUserId)
        ->first(['id']);

    if(!$member){
        lib\Util::responseError(403,'グループメンバーのみいいねできます');
    }

}
    //既にいいねしているか確認
    $liked = models\BlogLike::query()
        ->where('blog_id', $blogId)
        ->where('user_id', $currentUserId)
        ->first(['blog_id', 'user_id']);

    //すでにいいね済みなら削除
    if($liked){
        models\BlogLike::query()
            ->where('blog_id', $blogId)
            ->where('user_id', $currentUserId)
            ->delete();

        $data = [
            'is_liked' => false
        ];

        lib\Util::responseSuccess($data, 'いいねを削除しました');
    }

    //まだいいねしていなければ追加
    models\BlogLike::query()->create([
        'blog_id' => $blogId,
        'user_id' => $currentUserId
    ]);

    $data = [
        'is_liked' => true
    ];

    lib\Util::responseSuccess($data, 'いいねを追加しました');

}catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500,'サーバーエラーが発生しました');
}
?>