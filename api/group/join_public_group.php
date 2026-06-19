<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * グループ参加API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - group_id: グループID
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "グループに参加しました" }
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
    $groupId = models\Group::where('id', $group_id)->where('is_public', '=', true)->first(['id'])['id'] ?? null;
    if (!$groupId) {
        lib\Util::responseError(400, '指定されたグループは存在しません');
    }
    // すでにグループのメンバーかどうかチェック
    $existingMembership = models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id)
        ->first(['user_id'])['user_id'] ?? null;
    if ($existingMembership) {
        lib\Util::responseSuccess('すでにグループのメンバーです');
    }

    // グループにユーザーを追加
    models\GroupMember::insert([
        'group_id' => $group_id,
        'user_id' => $user_id,
        'role' => 'member',
        'joined_at' => date('Y-m-d H:i:s')
    ]);

    lib\Util::responseSuccess('グループに参加しました');
} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
