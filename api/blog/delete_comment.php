<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログコメント削除API
 * 必要なパラメータ:
 * - csrf_token
 * - comment_id
 * 
 * レスポンス:
 * - 成功: success: true/false
 * {
 *  "success": true,
 *  "message": [],
 *  "data": "コメントを削除しました"
 *}
 * 
 * - 失敗: { "success": false, "message": "不正なリクエストです" ,"サインインが必要です"}
 * 
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
    $commentId = $_POST['comment_id'] ?? '';

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($commentId === ''){
        lib\Util::responseError(400,'コメントIDを指定してください');
    }

    if(!ctype_digit((string)$commentId)){
        lib\Util::responseError(400,'コメントIDの形式が正しくありません');
    }

    $commentId = (int)$commentId;

    //ログイン中ユーザーID取得
    $currentUserId = $sessionHandler->getCurrentUserID();

    //コメント存在確認
    $comment = models\BlogComment::query()
        ->where('id', $commentId)
        ->first(['id', 'user_id']);

    if(!$comment){
        lib\Util::responseError(404,'コメントが見つかりません');
    }

    //自分のコメントか確認
    if($comment->user_id != $currentUserId){
        lib\Util::responseError(403,'このコメントは削除できません');
    }

    //コメント削除
    models\BlogComment::query()
        ->where('id', $commentId)
        ->delete();

    lib\Util::responseSuccess([], 'コメントを削除しました');

}catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500,'サーバーエラーが発生しました');
}
?>