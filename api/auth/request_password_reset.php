<?php
require_once __DIR__ . '/../../vendor/autoload.php';
/**
 * パスワード再発行リクエストAPI
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - mail_address: 登録されているメールアドレス
 * レスポンス:
 * - 成功: { "success": true, "message": "パスワード再発行のメールを送信しました" }
 * - エラー: { "success": false, "message": "エラーが発生しました" }
 */
try {
    //POST以外を拒否
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        lib\Util::responseError(405, '許可されていないリクエストです');
    }

    $sent_token   = $_POST['csrf_token'] ?? '';
    $mail_address = trim($_POST['mail_address'] ?? '');

    if (!filter_var($mail_address, FILTER_VALIDATE_EMAIL)) {
        lib\Util::responseError(400, '有効なメールアドレスを入力してください');
    }

    $csrfToken = new lib\CSRFToken();
    //バリデーション
    if (!$csrfToken->isValid($sent_token)) {
        lib\Util::responseError(400, '不正リクエストです');
    }

    //dbに接続
    $pdoHandler = lib\Util::connectDB();
    $sessionHandler = new lib\Session($pdoHandler);
    if ($sessionHandler->isLoggedIn()) {
        lib\Util::responseError(401, 'すでにログインしています');
    }

    // ユーザーが存在するか確認
    $user_id = $pdoHandler->exec(
        "SELECT id FROM users WHERE mail_address = ?",
        [$mail_address]
    )[0]['id'] ?? null;
    if (!$user_id) {
        lib\Util::responseSuccess('パスワード再発行のメールを送信しました');
    }

    // トークンを削除
    $pdoHandler->exec(
        "DELETE FROM reset_requests WHERE user_id = ?",
        [$user_id]
    );

    // トークンを生成(5分間有効)して保存
    $token = lib\Util::generateRandomString(5);
    $pdoHandler->exec(
        "INSERT INTO reset_requests(user_id, token, created_at, expires_at) VALUES(?, ?, NOW(), NOW() + INTERVAL 5 MINUTE)",
        [$user_id, $token]
    );

    // メール送信
    $isSuccess = lib\SendMail::send(
        $mail_address,
        'パスワード再発行のリクエスト',
        "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>パスワード再発行のリクエスト</title>
        </head>
        <body>
            <h1>パスワード再発行のリクエスト</h1>
            <p>パスワード再発行のリクエストを受け付けました。以下のトークンを5分以内に入力してください。</p>
            <h2>{$token}</h2>
            <p>このトークンは5分後に無効になります。</p>
        </body>
        </html>
        "
    );
    if (!$isSuccess) {
        lib\Util::responseError(500, 'メール送信に失敗しました');
    }
    lib\Util::responseSuccess('パスワード再発行のメールを送信しました');
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
