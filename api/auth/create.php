<?php
require_once __DIR__.'/../../vendor/autoload.php';

try{
    // データ受け取り
    $user_id      = $_POST['user_id'] ?? null;
    $display_name = $_POST['display_name'] ?? null;
    $mail_address = $_POST['mail_address'] ?? null;
    $password     = $_POST['password'] ?? null;
    $csrf_token   = $_POST['csrf_token'] ?? null;

    // csrfトークンの検証
    $csrfToken = new lib\CSRFToken();
    if(!$csrf_token || !$csrfToken->isValid($csrf_token)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // バリデーション
    if(!$user_id || !$display_name || !$mail_address || !$password){
        lib\Util::responseError(400,'全てのフィールドを入力してください');
    }
    if(!filter_var($mail_address, FILTER_VALIDATE_EMAIL)){
        lib\Util::responseError(400,'メールアドレスの形式が正しくありません');
    }

    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,20}$/';
    if(!preg_match($pattern, $password)){
        lib\Util::responseError(400,'パスワードは6-20文字で、英大文字・小文字・数字をそれぞれ1種類以上含む必要があります');
    }

    // DB接続
    $pdoHandler = lib\Util::connectDB();
    $pdoHandler->exec(
        'INSERT INTO users(user_id,mail_address,password)
        VALUES (:user_id, :mail_address, :password)
        ',
        [
            'user_id'       => $user_id,
            'mail_address'  => $mail_address,
            'password'      => password_hash($password, PASSWORD_DEFAULT)
        ]
    );
    $user_id_number = $pdoHandler->getLastInsertId();
    $pdoHandler->exec(
        'INSERT INTO profiles(user_id,display_name)
        VALUES (:user_id, :display_name)
        ',
        [
            'user_id'       => $user_id_number,
            'display_name'  => $display_name
        ]
    );

    lib\Util::responseSuccess('ユーザーの作成に成功しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}