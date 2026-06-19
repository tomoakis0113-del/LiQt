<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブロックAPI
 * 必要なパラメータ:
 * - csrf_token
 * - user_id: 確認対象のユーザーID
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ブロック状態を確認しました", "is_blocked": true/false}
 * - エラー: { "success": false, "message": "不正なリクエストです" ,"サインインが必要です"}
 * -"data" : "すでにブロック済みです" 
 */
try{
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    $sentToken = $_POST['csrf_token'] ?? '';
    $targetUserId = trim($_POST['user_id'] ?? '');

    $csrfToken = new lib\CSRFToken();

    // csrfトークンの検証
    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // ログイン中のユーザーIDを取得
    $sessionHandler = new lib\Session();
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインしてください');
    }

    $currentUserId = $sessionHandler->getCurrentUserID();
    $targetUserId = models\User::query()
        ->where('user_id', $targetUserId)
        ->first(['id'])['id'] ?? null;

    if(!$targetUserId){
        lib\Util::responseError(404,'ブロック対象のユーザーが見つかりません');
    }

    $exists = models\BlockList::query()
        ->where('user_id', $currentUserId)
        ->where('blocked_user_id', $targetUserId)
        ->first(['id'])['id'] ?? null;

    // ブロック状態を返す
    lib\Util::responseSuccess('ブロック状態を確認しました', ['is_blocked' => (bool)$exists]);
}
catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>