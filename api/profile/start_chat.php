<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * チャット開始API
 * 必要なパラメータ:
 * - csrf_token
 * - user_id: 相手のユーザーID
 */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        lib\Util::responseError(405, '許可されていないリクエストです');
    }

    $sentToken = $_POST['csrf_token'] ?? '';
    $targetUserId = trim($_POST['user_id'] ?? '');

    $csrfToken = new lib\CSRFToken();

    if (!$csrfToken->isValid($sentToken)) {
        lib\Util::responseError(400, '不正リクエストです');
    }

    if (!preg_match('/^[A-Za-z0-9]{8}$/', $targetUserId)) {
        lib\Util::responseError(400, 'ユーザーIDは英数字8文字で入力してください');
    }

    $pdoHandler = lib\Util::connectDB();
    $sessionHandler = new lib\Session($pdoHandler);

    if (!$sessionHandler->isLoggedIn()) {
        lib\Util::responseError(401, 'ログインしてください');
    }

    $currentUserId = $sessionHandler->getCurrentUserID();

    // 相手ユーザーの内部IDを取得 自分がブロックされていない場合のみ進む
    $targetUser = $pdoHandler->exec(
        "SELECT id FROM users WHERE user_id = :target_user_id AND NOT EXISTS (SELECT 1 FROM block_list WHERE user_id = users.id AND blocked_user_id = :from_user_id)",
        [":target_user_id" => $targetUserId, ":from_user_id" => $currentUserId]
    );

    $targetInternalId = $targetUser[0]['id'] ?? null;
    if (!$targetInternalId) {
        lib\Util::responseError(404, '相手ユーザーが見つかりません');
    }
    if ($currentUserId == $targetInternalId) {
        lib\Util::responseError(400, '自分とはチャットできません');
    }

    $minId = min($currentUserId, $targetInternalId);
    $maxId = max($currentUserId, $targetInternalId);
    $groupName = 'dm_' . $minId . '_' . $maxId;

    $existingGroup = $pdoHandler->exec(
        "SELECT id FROM `groups` WHERE name = ? AND is_public = false",
        [$groupName]
    );

    if ($existingGroup && count($existingGroup) > 0) {
        $groupId = $existingGroup[0]['id'];
    } else {
        $pdoHandler->exec(
            "INSERT INTO `groups` (name, is_public) VALUES (?, false)",
            [$groupName]
        );

        $groupId = $pdoHandler->getLastInsertId();

        $pdoHandler->exec(
            "INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'owner')",
            [$groupId, $currentUserId]
        );

        $pdoHandler->exec(
            "INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')",
            [$groupId, $targetInternalId]
        );
    }

    $data = [
        'chat_link' => 'chat.php?group_id=' . $groupId
    ];

    lib\Util::responseSuccess($data, 'チャットを開始しました');
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
