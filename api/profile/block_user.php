<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブロックAPI
 * 必要なパラメータ:
 * - csrf_token
 * - user_id: ブロックしたいユーザーID
 */
try{
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    $sentToken = $_POST['csrf_token'] ?? '';
    $targetUserId = trim($_POST['user_id'] ?? '');

    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    if(!preg_match('/^[A-Za-z0-9]{8}$/', $targetUserId)){
        lib\Util::responseError(400,'ユーザーIDは英数字8文字で入力してください');
    }

    $pdoHandler = lib\Util::connectDB();
    $sessionHandler = new lib\Session($pdoHandler);

    if(!$sessionHandler->isLoggedIn()){
        lib\Util::responseError(401,'ログインしてください');
    }

    $currentUserId = $sessionHandler->getCurrentUserID();

    $targetUser = $pdoHandler->exec(
        "SELECT id FROM users WHERE user_id = ?",
        [$targetUserId]
    );

    if(!$targetUser){
        lib\Util::responseError(404,'ブロック対象のユーザーが見つかりません');
    }

    $blockedUserId = $targetUser[0]['id'];

    if($currentUserId == $blockedUserId){
        lib\Util::responseError(400,'自分自身はブロックできません');
    }

    $exists = $pdoHandler->exec(
        "SELECT id FROM block_list WHERE user_id = ? AND blocked_user_id = ?",
        [$currentUserId, $blockedUserId]
    );

    if($exists){
        lib\Util::responseSuccess([], 'すでにブロック済みです');
    }

    $pdoHandler->exec(
        "INSERT INTO block_list (user_id, blocked_user_id) VALUES (?, ?)",
        [$currentUserId, $blockedUserId]
    );

    lib\Util::responseSuccess([], 'ユーザーをブロックしました');
}
catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>