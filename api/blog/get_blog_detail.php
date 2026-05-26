<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログ編集API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - blog_id: 編集したいブログID
 *  
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ブログの編集に成功しました" }
 * {
 *"success": true,
 * "message": [],
 * "data": {
 *   "id": ブログID,
 *   "title": "ブログのタイトル",
 *   "content": "ブログの本文",
 *   "visibility": "public","private","group",
 *   "tags": "ブログのタグ",
 *   "created_at": "作成日時",
 *   "updated_at": "更新日時"
 * }
 *}
 * 
 * - 失敗: { "success": false, "message": "エラーメッセージ","サインインが必要です","不正なリクエストです","ブログIDが指定されていません" }
 * 
 */

try{

    //POST以外を拒否
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

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($blogId === ''){
        lib\Util::responseError(400,'ブログIDが指定されていません');
    }

    //ログイン中のユーザーIDを取得
    $currentUserId = $sessionHandler->getCurrentUserID();

    //ブログ習得
    $blog = models\Blog::query()
        ->where('id', $blogId)
        ->first();

    if($blog === null){
        lib\Util::responseError(400,'指定されたブログが見つかりません');
    }

    //投稿者本人か確認する
    if($blog->author_id !== $currentUserId){
        lib\Util::responseError(403,'権限がありません');
    }

    $blog = [
        'id' => $blog->id,
        'title' => $blog->title,
        'content' => $blog->content,
        'visibility' => $blog->visibility,
        'tags' => $blog->tags,
        'created_at' => $blog->created_at,
        'updated_at' => $blog->updated_at,
    ];

    lib\Util::responseSuccess('ブログを取得しました',  $blog);

}catch(Exception $e){
    lib\Util::responseError(500,'予期せぬエラーが発生しました');
}

?>