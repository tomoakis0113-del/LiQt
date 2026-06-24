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

    $sessionHandler = new lib\Session();
    $csrfToken = new lib\CSRFToken();

    // 検証
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'POSTリクエストのみ許可されています');
    }
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
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
    $user = models\User::query()
        ->where('id', $user_id)
        ->first(['user_id', 'is_active']);
    $userToken = models\MailTemporary::query()
        ->where('user_id', $user_id)
        ->first(['token', 'created_at']);

    if(!$user){ 
        lib\Util::responseError(404,'ユーザーが見つかりません');
    }
    if($user['is_active']){
        lib\Util::responseError(400,'ユーザーは既に有効化されています');
    }
    $diff = (new DateTime('now', $timezone))->getTimestamp() - (new DateTime($userToken['created_at'], $timezone))->getTimestamp();
    if($diff > 300){ // 300秒 = 5分
        lib\Util::responseError(400,'トークンの有効期限が切れています。新しいトークンを発行してください。');
    }
    if($userToken['token'] !== $token){
        lib\Util::responseError(400,'トークンが一致しません');
    }

    // トークンが一致し、有効期限内であればユーザーを有効化し、トークンを削除
    models\MailTemporary::query()->where('user_id', $user_id)->delete();
    models\User::query()->where('id', $user_id)->update(['is_active' => 1]);

    lib\Util::responseSuccess('メール認証が完了しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>