<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * サインインAPI
 * 必要なパラメータ:
 * - mail_address: メールアドレス
 * - password: パスワード
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ログインに成功しました" }
 * - エラー: { "success": false, "message": "エラ
 */
try{
    //POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    //パラメータを受け取り
    $mailaddress = trim($_POST['mail_address'] ?? '');
    $password    = $_POST['password'] ?? '';
    $sentToken   = $_POST['csrf_token'] ?? '';

    $csrfToken = new lib\CSRFToken();

    //バリデーション
    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    if($mailaddress === '' || $password === ''){
        lib\Util::responseError(400,'メールアドレスとパスワードを入力してください');
    }

    if(!filter_var($mailaddress, FILTER_VALIDATE_EMAIL)){
        lib\Util::responseError(400,'メールアドレスの形式が正しくありません');
    }

    if(strlen($password) < 6 || strlen($password) > 20){
        lib\Util::responseError(400,'パスワードは6文字以上20文字以内です');
    }

    //dbに接続
    $sessionHandler = new lib\Session();
    $isSuccess = $sessionHandler->tryLogin($mailaddress, $password);

    if($isSuccess){
        session_regenerate_id(true);
        lib\Util::responseSuccess('ログインに成功しました');
    }else{
        lib\Util::responseError(401,'メールアドレスまたはパスワードが違います');
    }
}
catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>