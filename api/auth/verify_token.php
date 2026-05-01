<?php
require_once __DIR__ .'/../../vendor/autoload.php';

/**
 * メール認証トークン検証API
 * 
 * 必要なパラメータ:
 * - token: メール認証トークン
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "メール認証が完了しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // パラメータの受け取り
    $csrf_token  = $_POST['csrf_token'] ?? null;
    $token       = $_POST['token'] ?? null;

    $pdoHandler = lib\Util::connectDB();
    $sessionHandler = new lib\Session($pdoHandler);
    $csrfToken = new lib\CSRFToken();

    // 検証
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'POSTリクエストのみ許可されています');
    }
    if(!$sessionHandler->isLoggedIn()){
        lib\Util::responseError(401,'ログインが必要です');
    }
    if(!$csrf_token || !$csrfToken->isValid($csrf_token)){
        lib\Util::responseError(400,'不正リクエストです');
    }
    if(!$token){
        lib\Util::responseError(400,'トークンを入力してください');
    }

    // 現在のユーザ情報を取得
    $timezone = new DateTimeZone('Asia/Tokyo');
    $user_id = $sessionHandler->getCurrentUserID();
    $user = $pdoHandler->exec(
        "SELECT u.user_id, u.is_active, mt.token, mt.created_at FROM users u INNER JOIN mail_temporary mt ON u.id = mt.user_id WHERE u.id = :id AND mt.token = :token",
        ['id' => $user_id, 'token' => $token]
        )[0] ?? null;

    if(!$user){ 
        lib\Util::responseError(404,'ユーザーが見つかりません');
    }
    if($user['is_active']){
        lib\Util::responseError(400,'ユーザーは既に有効化されています');
    }
    $diff = (new DateTime('now', $timezone))->getTimestamp() - (new DateTime($user['created_at'], $timezone))->getTimestamp();
    if($diff > 300){ // 300秒 = 5分
        lib\Util::responseError(400,'トークンの有効期限が切れています。新しいトークンを発行してください。');
    }
    if($user['token'] !== $token){
        lib\Util::responseError(400,'トークンが一致しません');
    }

    $pdoHandler->exec(
        "DELETE FROM mail_temporary WHERE user_id = :user_id",
        ['user_id' => $user_id]
    );
    $pdoHandler->exec(
        "UPDATE users SET is_active = 1 WHERE id = :id",
        ['id' => $user_id]
    );

    lib\Util::responseSuccess('メール認証が完了しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>