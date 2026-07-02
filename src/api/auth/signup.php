<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * ユーザー作成API
 * 必要なパラメータ:
 * - user_id: ユーザーID（ユニークな文字列）
 * - display_name: 表示名
 * - mail_address: メールアドレス
 * - password: パスワード
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ユーザーの作成に成功しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
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
    if(!preg_match('/^[a-zA-Z0-9]{5,20}$/', $user_id)){
        lib\Util::responseError(400,'ユーザーIDは半角英数字で 5～20 文字で入力してください');
    }
    if(!filter_var($mail_address, FILTER_VALIDATE_EMAIL)){
        lib\Util::responseError(400,'メールアドレスの形式が正しくありません');
    }

    // AIチェック
    $ai = new lib\AI();
    if($ai->word_check($display_name . $user_id . $mail_address)){
        lib\Util::responseError(400,'入力内容に不適切なものが含まれています');
    }

    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,20}$/';
    if(!preg_match($pattern, $password)){
        lib\Util::responseError(400,'パスワードは6-20文字で、英大文字・小文字・数字をそれぞれ1種類以上含む必要があります');
    }

    // ユーザーIDとメールアドレスの重複チェック
    if(models\User::query()->where('user_id', $user_id)->exists()){
        lib\Util::responseError(400,'ユーザーIDは既に使用されています');
    }
    if(models\User::query()->where('mail_address', $mail_address)->exists()){
        lib\Util::responseError(400,'メールアドレスは既に使用されています');
    }

    // ユーザー作成
    $session = models\User::create([
        'user_id' => $user_id,
        'mail_address' => $mail_address,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'is_active' => false,
    ]);
    models\Profile::create([
        'user_id' => $session->id,
        'display_name' => $display_name,
    ]);

    // 初回ログイン
    $sessionHandler = new lib\Session();
    $sessionHandler->trySignin($mail_address, $password);

    lib\Util::responseSuccess('ユーザーの作成に成功しました。');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}