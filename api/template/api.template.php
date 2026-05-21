<?php
require_once __DIR__ . '/../../vendor/autoload.php';

try{
    /* 以下サンプルコード */
    // csrftoken用ライブラリ
    $csrfToken = new lib\CSRFToken();

    $csrfToken->getToken(); // csrf_tokenを取得->フロントエンド側でフォームに埋め込む
    $csrfToken->isValid($_POST['csrf_token'] ?? ''); // フォーム送信時にトークンを検証

    // ユーザ操作系
    $session = new lib\Session();
    $session->trySignin('username', 'password', true); // ユーザ名、パスワードを用いてサインインを試行する。true:サインインを維持
    $session->isSignedIn(); // サインイン済みかどうか
    $session->signout(); // サインアウト

    // データベース操作はORMを使用
    $users = models\User::query()->where('is_active', 1)->get();
    $user = models\User::query()->where('id', 1)->first();
    $newUserId = models\User::query()->insertGetId([
        'user_id' => 'newuser',
        'mail_address' => 'test@example.com',
        'password' => password_hash('Password123', PASSWORD_DEFAULT),
        'is_active' => false,
    ]);

    // メール送信
    echo lib\SendMail::send('example@example.com', 'タイトル', 'html本文')?'メール送信成功':'メール送信失敗';
}
catch(\Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    exit(lib\Util::responseError(500, 'サーバーエラーが発生しました'));
}
?>