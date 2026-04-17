<?php
require_once __DIR__ . '/vendor/autoload.php';

/* 以下サンプルコード */
// csrftoken用ライブラリ
$csrfToken = new lib\CSRFToken();

$csrfToken->getToken(); // csrf_tokenを取得->フロントエンド側でフォームに埋め込む
$csrfToken->isValid($_POST['csrf_token'] ?? ''); // フォーム送信時にトークンを検証

// データベース操作用ライブラリ
$pdoHandler = lib\Util::connectDB(); // データベース接続
$pdoHandler->exec('SELECT name FROM users WHERE id = ?',['otonari']);

// ユーザ操作系
$pdoHandler = lib\Util::connectDB();
$session = new lib\Session($pdoHandler);
$session->tryLogin('username', 'password', true); // ユーザ名、パスワードを用いてログインを試行する。true:ログインを維持
$session->isLoggedIn(); // ログイン済みかどうか
$session->logout(); // ログアウト

// メール送信
lib\SendMail::send('example@example.com', 'タイトル', 'html本文');