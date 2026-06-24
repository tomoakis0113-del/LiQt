<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * グループ作成API
 * 必要なパラメータ:
 * - group_id: グループID
 * - message_content: メッセージ内容
 * - image_upload: 画像ファイル（任意）
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "グループの作成に成功しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // データ受け取り
    $message_content = $_POST['message_content'] ?? null;
    $image_upload    = $_FILES['image_upload'] ?? null;
    $group_id        = $_POST['group_id'] ?? null;
    $csrf_token      = $_POST['csrf_token'] ?? null;
    $csrfToken       = new lib\CSRFToken();

    if(!$message_content){
        lib\Util::responseError(400,'メッセージ内容を入力してください');
    }

    // ログイン済みかチェック
    $sessionHandler = new lib\Session();
    $user_id = $sessionHandler->getCurrentUserID();
    
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }
    if(!$csrf_token || !$csrfToken->isValid($csrf_token)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // グループに属しているかどうか検証
    $isMember = models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id)
        ->exists();
    if(!$isMember){
        lib\Util::responseError(403,'グループに属していません');
    }

    // 画像アップロード
    $image_path = null;
    if($image_upload && $image_upload['error'] === UPLOAD_ERR_OK){
        $uploadDir = __DIR__.'/../../uploads/messages/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = basename($image_upload['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($image_upload['tmp_name'], $targetFilePath)) {
            $image_path = 'uploads/messages/' . $fileName; // データベースに保存するパス
        } else {
            lib\Util::responseError(500, '画像のアップロードに失敗しました');
        }
    } else {
        $image_path = null;
    }

    // メッセージ保存
    models\Chat::create([
        'group_id' => $group_id,
        'sender_id' => $user_id,
        'content' => $message_content,
        'image_url' => $image_path
    ]);
    lib\Util::responseSuccess('メッセージの送信に成功しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}