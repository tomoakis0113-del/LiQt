<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * 
 * 
 * 
 * 
 * 
 * 
 */


try{
    //POST以外を拒否する
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    //セッション管理
    $sessionHandler = new lib\Session();

    //サインイン管理
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    //パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $blogId = $_POST['blog_id'] ?? '';
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $visibility = $_POST['visibility'] ?? '';
    $tags = $_POST['tags'] ?? '';

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //編集データを受け取る
    $blogData = [
        'title' => $title,
        'content' => $content,
        'visibility' => $visibility,
        'tags' => $tags
    ];

    //入力チェック
    if($blogId === ''){
        lib\Util::responseError(400,'ブログIDが指定されていません');
    }

    if(!ctype_digit((string)$blogId)){
        lib\Util::responseError(400,'ブログIDの形式が正しくありません');
    }

    if(trim($title) === ''){
        lib\Util::responseError(400,'タイトルを入力してください');
    }

    if(trim($content) === ''){
        lib\Util::responseError(400,'本文を入力してください');
    }

    if(!in_array($visibility, ['public', 'private', 'group'], true)){
        lib\Util::responseError(400,'公開設定が正しくありません');
    }

    if(mb_strlen($title) > 255){
        lib\Util::responseError(400,'タイトルは255文字以内で入力してください');
    }
    //編集対象のブログをDBから探す
    $blog = models\Blog::query()
        ->where('id', $blogId)
        ->first();    
    //投稿者本人か確認する
    if($blog->author_id !== $sessionHandler->getCurrentUserID()){
        lib\Util::responseError(403,'編集権限がありません');
    }

$currentUserId = $sessionHandler->getCurrentUserID();

$member = models\GroupMember::query()
    ->where('user_id', $currentUserId)
    ->where('group_id', $blog->group_id)
    ->first(['group_id']);

    //blogsテーブルを更新する
    $blog->title = $title;
    $blog->content = $content;
    $blog->visibility = $visibility;
    $blog->tags = $tags;
    $blog->save();

    $blog = [
        'id' => $blog->id,
        'title' => $blog->title,
        'content' => $blog->content,
        'visibility' => $blog->visibility,
        'tags' => $blog->tags,
        'created_at' => $blog->created_at,
        'updated_at' => $blog->updated_at,
    ];

    lib\Util::responseSuccess('ブログを更新しました',  $blog);

}catch(Exception $e){
    lib\Util::responseError(500,$e->getMessage());
}

 




?>