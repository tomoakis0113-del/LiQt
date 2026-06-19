<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * グループ退出API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - group_id: グループID
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "グループを退出しました" }
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

    // グループからユーザーを削除
    models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id)
        ->delete();

    // オーナが全員退出した場合、グループを削除
    $ownerId = 
    models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('role', '=', 'owner')
        ->first(['user_id'])['user_id'] ?? null;

    if (!$ownerId) {
        models\Group::query()
            ->where('id', '=', $group_id)
            ->delete();
    }

    lib\Util::responseSuccess('グループから退出しました');
} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
