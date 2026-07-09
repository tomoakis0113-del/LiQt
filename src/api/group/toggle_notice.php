<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * グループの通知ブロックapi
 * toggle_notice.php
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - group_id: グループID
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "通知ブロックの更新に成功しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // データ受け取り
    $csrf_token     = $_POST['csrf_token'] ?? null;
    $group_id       = $_POST['group_id'] ?? null;
    
    // csrfトークンの検証
    $csrfToken = new lib\CSRFToken();
    if(!$csrf_token || !$csrfToken->isValid($csrf_token)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // バリデーション
    if(!$group_id){
        lib\Util::responseError(400,'グループIDを入力してください');
    }

    $sessionHandler = new lib\Session();
    $user_id = $sessionHandler->getCurrentUserID();
    
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    // グループに属しているかどうか検証
    $isMember = models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id)
        ->exists();

    if(!$isMember){
        lib\Util::responseError(403,'グループに属していません');
    }

    $Blocked = models\NoticeBlock::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id);

    if($Blocked->exists()){
        $Blocked->delete();
    } else {
        models\NoticeBlock::create([
            "user_id" => $user_id,
            "group_id" => $group_id
        ]);
    }

    lib\Util::responseSuccess('通知ブロックの更新に成功しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}