<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログ削除API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - blog_id: 削除したいブログID
 * 
 * レスポンス:
 * 
 * 
 */


try{
    // POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    // セッション管理
    $sessionHandler = new lib\Session();

    // サインイン管理
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    // パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $blogId = $_POST['blog_id'] ?? '';

    // CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($blogId === ''){
        lib\Util::responseError(400,'ブログIDが指定されていません');
    }

    if(!is_numeric($blogId)){
        lib\Util::responseError(400,'不正なブログIDです');
    }

    $blog = models\Blog::find($blogId);

    //ログイン中ユーザーID取得
    $currentUserid = $sessionHandler->getCurrentUserID();

    //ブログ習得
    $blog = models\Blog::query()
        ->where('id', $blogId)
        ->first(['id', 'author_id']);

    if(!$blog){
        lib\Util::responseError(404,'ブログが見つかりません');
    }

    //投稿者本人か確認
    if($blog->author_id !== $currentUserid){
        lib\Util::responseError(403,'このブログは削除できません');
    }

    //ブログ削除
    models\Blog::query()
        ->where('id', $blogId)
        ->delete();

    lib\Util::responseSuccess([], 'ブログを削除しました');
}catch(Exception $e){
    lib\Util::responseError(500,'予期せぬエラーが発生しました');
}

?>