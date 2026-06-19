<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * グループ削除API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - group_id: グループID
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "グループを削除しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try {
    // データ受け取り
    $csrf_token = $_POST['csrf_token'] ?? null;
    $group_id = $_POST['group_id'] ?? null;

    // csrfトークンの検証
    $csrfToken  = new lib\CSRFToken();
    if (!$csrf_token || !$csrfToken->isValid($csrf_token)) {
        lib\Util::responseError(400, '不正リクエストです');
    }

    // ログイン済みかチェック
    $sessionHandler = new lib\Session();
    $user_id = $sessionHandler->getCurrentUserID();

    if (!$sessionHandler->isSignedIn()) {
        lib\Util::responseError(401, 'サインインが必要です');
    }
    if (!$group_id) {
        lib\Util::responseError(400, 'グループIDが必要です');
    }

    // グループが存在するかどうかチェック
    $groupId = models\Group::where('id', $group_id)->first(['id'])['id'] ?? null;
    if (!$groupId) {
        lib\Util::responseError(400, '指定されたグループは存在しません');
    }

    // ユーザーのロールを取得
    $userRole =  models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id)
        ->first(['role'])['role'] ?? null;

    if (!$userRole) {
        lib\Util::responseError(403, 'グループのメンバーではありません');
    }

    // グループ情報を取得
    $groupInfo = models\Group::query()
        ->where('id', '=', $group_id)
        ->first(['name', 'group_icon_url', 'is_public']);

    if (!$groupInfo) {
        lib\Util::responseError(400, 'グループ情報の取得に失敗しました');
    }

    // グループメンバーの情報を取得
    $role = models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id)
        ->first(['role'])['role'] ?? null;
    if (!$role) {
        lib\Util::responseError(400, 'グループメンバーの情報の取得に失敗しました');
    }
    if($role !== 'owner'){
        lib\Util::responseError(403, 'グループの管理者以上の権限が必要です');
    }

    // グループ削除
    models\Group::where('id', $group_id)->delete();
    models\GroupMember::where('group_id', $group_id)->delete();

    lib\Util::responseSuccess('グループを削除しました');
} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
