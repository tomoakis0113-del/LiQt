<?php

use function Illuminate\Support\now;

require_once __DIR__ .'/../../vendor/autoload.php';

/**
 * メール認証トークン送信API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "確認トークンをメールに送信しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // パラメータの受け取り
    $csrf_token = $_POST['csrf_token'] ?? null;
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

    // 現在のユーザ情報を取得
    $user_id = $sessionHandler->getCurrentUserID();
    $user = models\User::query()
        ->where('id', $user_id)
        ->first(['user_id', 'mail_address', 'is_active']);

    // ユーザの存在と状態を確認
    if(!$user){ 
        lib\Util::responseError(404,'ユーザーが見つかりません');
    }
    if($user['is_active']){
        lib\Util::responseError(400,'ユーザーは既に有効化されています');
    }

    // 既存のトークンを確認
    $existingToken = models\MailTemporary::query()
        ->where('user_id', $user_id)
        ->first(['created_at'])['created_at'] ?? null;

    $timezone = new DateTimeZone('Asia/Tokyo');

    // 既存のトークンが1分以内に発行されているか確認
    if($existingToken){
        $createdAt = new DateTime($existingToken, $timezone);
        $interval = (new DateTime('now', $timezone))->getTimestamp() - $createdAt->getTimestamp();

        if($interval < 60){ // 60秒クールダウン
            lib\Util::responseError(429,'トークンは1分に1回のみ発行できます。');
        }

        models\MailTemporary::query()->where('user_id', $user_id)->delete();
    }

    // トークン生成と保存
    $token = lib\Util::generateRandomString(5);
    $now = new DateTime('now', $timezone);
    models\MailTemporary::query()->insert([
        'user_id' => $user_id,
        'token' => $token,
        'created_at' => $now->format('Y-m-d H:i:s'),
    ]);

    // メール送信
    $isSuccess = lib\SendMail::send(
        $user['mail_address'],
        '【LiQt】メールアドレス確認のトークン',
        "
        <html>
        <head>
            <title>メールアドレス確認のトークン</title>
        </head>
        <body>
            <p>以下のトークンをLiQtのメールアドレス確認画面に入力してください。</p>
            <h2>{$token}</h2>
            <p>このトークンは5分間有効です。</p>
        </body>
        </html>
        "
    );

    if(!$isSuccess){
        lib\Util::responseError(500,'メールの送信に失敗しました');
    }

    lib\Util::responseSuccess('確認トークンをメールに送信しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>