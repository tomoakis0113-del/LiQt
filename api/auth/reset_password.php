<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * パスワードリセットAPI
 * 必要なパラメータ:
 * - mail_address: 登録されているメールアドレス
 * - token: パスワードリセットトークン
 * - new_password: 新しいパスワード
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "パスワードがリセットされました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        lib\Util::responseError(405, '許可されていないリクエストです');
    }

    $sent_token   = $_POST['csrf_token'] ?? '';
    $mail_address = trim($_POST['mail_address'] ?? '');
    $token        = trim($_POST['token'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    $csrfToken = new lib\CSRFToken();

    // バリデーション
    if (!$csrfToken->isValid($sent_token)) {
        lib\Util::responseError(400, '不正リクエストです');
    }

    if (!filter_var($mail_address, FILTER_VALIDATE_EMAIL)) {
        lib\Util::responseError(400, '有効なメールアドレスを入力してください');
    }
    if ($token === '' || $new_password === '') {
        lib\Util::responseError(400, 'すべてのフィールドを入力してください');
    }
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,20}$/';
    if (!preg_match($pattern, $new_password)) {
        lib\Util::responseError(400, 'パスワードは6-20文字で、英大文字・小文字・数字をそれぞれ1種類以上含む必要があります');
    }

    // DB接続
    $pdoHandler = lib\Util::connectDB();

    // メールアドレスからユーザーIDを取得
    $user_id = $pdoHandler->exec(
        'SELECT id FROM users WHERE mail_address = :mail_address',
        [':mail_address' => $mail_address]
    )[0]['id'] ?? null;
    if ($user_id === null) {
        lib\Util::responseError(404, '無効なトークンです');
    }

    // トークンの検証 - トークンが存在し、有効期限内であることを確認
    $result = $pdoHandler->exec(
        'SELECT id FROM reset_requests WHERE user_id = :user_id AND token = :token AND NOW() < expires_at',
        [':user_id' => $user_id, ':token' => $token]
    );
    if (count($result) === 0) {
        lib\Util::responseError(400, '無効なトークンです');
    }

    // パスワードを更新し、トークンを削除
    $pdoHandler->exec(
        'UPDATE users SET password = :password WHERE mail_address = :mail_address',
        [':password' => password_hash($new_password, PASSWORD_DEFAULT), ':mail_address' => $mail_address]
    );
    $pdoHandler->exec(
        'DELETE FROM reset_requests WHERE user_id = :user_id',
        [':user_id' => $user_id]
    );

    lib\Util::responseSuccess('パスワードがリセットされました');
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
