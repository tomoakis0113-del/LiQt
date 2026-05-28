<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログいいね削除API
 * 必要なパラメータ:
 * - csrf_token
 * - blog_id
 * 
 * レスポンス:
 * - success: true/false
 * - message
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
        ->first(['id']);

    if(!$blog){
        lib\Util::responseError(404,'ブログが見つかりません');
    }

    //いいね済みか確認
    $liked = models\BlogLike::query()
        ->where('blog_id', $blogId)
        ->where('user_id', $currentUserId)
        ->first(['blog_id', 'user_id']);

    if(!$liked){
        lib\Util::responseError(400,'まだいいねしていません');
    }

    //いいね削除
    models\BlogLike::query()
        ->where('blog_id', $blogId)
        ->where('user_id', $currentUserId)
        ->delete();

    lib\Util::responseSuccess([], 'いいねを削除しました');

}catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500,'サーバーエラーが発生しました');
}
?>