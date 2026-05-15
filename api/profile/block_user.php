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

    // csrfトークンの検証
    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // ユーザーIDのバリデーション
    if(!preg_match('/^[A-Za-z0-9]{8}$/', $targetUserId)){
        lib\Util::responseError(400,'ユーザーIDは英数字8文字で入力してください');
    }

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
    if($currentUserId == $targetUserId){
        lib\Util::responseError(400,'自分自身はブロックできません');
    }

    $exists = models\BlockList::query()
        ->where('user_id', $currentUserId)
        ->where('blocked_user_id', $targetUserId)
        ->first(['id'])['id'] ?? null;
    if($exists){
        lib\Util::responseSuccess([], 'すでにブロック済みです');
    }

    // ブロックリストに追加
    models\BlockList::query()->insert([
        'user_id' => $currentUserId,
        'blocked_user_id' => $targetUserId
    ]);

    lib\Util::responseSuccess([], 'ユーザーをブロックしました');
}
catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>