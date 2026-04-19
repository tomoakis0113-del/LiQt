<?php
require_once __DIR__ . '/../../vendor/autoload.php';

try{
    /* 以下サンプルコード */
    // csrftoken用ライブラリ
    $csrfToken = new lib\CSRFToken();

    $csrfToken->getToken(); // csrf_tokenを取得->フロントエンド側でフォームに埋め込む
    $csrfToken->isValid($_POST['csrf_token'] ?? ''); // フォーム送信時にトークンを検証

    // データベース操作用ライブラリ
    $pdoHandler = lib\Util::connectDB(); // データベース接続
    $pdoHandler->exec('SELECT name FROM user WHERE id = ?',['otonari']);

    // ユーザ操作系
    $pdoHandler = lib\Util::connectDB();
    $session = new lib\Session($pdoHandler);
    $session->tryLogin('username', 'password', true); // ユーザ名、パスワードを用いてログインを試行する。true:ログインを維持
    $session->isLoggedIn(); // ログイン済みかどうか
    $session->logout(); // ログアウト

    // メール送信
    echo lib\SendMail::send('example@example.com', 'タイトル', 'html本文')?'メール送信成功':'メール送信失敗';
}
catch(\Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    exit(lib\Util::responseError(500, 'サーバーエラーが発生しました'));
}
?>