<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * グループ作成API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - group_id: グループID
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ロールの取得に成功しました", "data": { "role": "ロール名" } }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // データ受け取り
    $csrf_token = $_POST['csrf_token'] ?? null;
    $group_id = $_POST['group_id'] ?? null;

    // csrfトークンの検証
    $csrfToken  = new lib\CSRFToken();
    if(!$csrf_token || !$csrfToken->isValid($csrf_token)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // ログイン済みかチェック
    $sessionHandler = new lib\Session();
    $user_id = $sessionHandler->getCurrentUserID();
    
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }
    if(!$group_id){
        lib\Util::responseError(400,'グループIDが必要です');
    }

    // グループが存在するかどうかチェック
    $groupId = models\Group::where('id', $group_id)->first(['id'])['id'] ?? null;
    if(!$groupId){
        lib\Util::responseError(400,'指定されたグループは存在しません');
    }

    $userRole =  models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id','=',$user_id)
        ->first(['role'])['role'] ?? null;

    if(!$userRole){
        lib\Util::responseError(403,'グループのメンバーではありません');
    }

    lib\Util::responseSuccess('ユーザーのロールを取得しました', ['role' => $userRole]);
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}