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

    // パラメータの受け取りとバリデーション
    $sentToken = $_POST['csrf_token'] ?? '';
    $targetUserId = trim($_POST['user_id'] ?? '');

    $csrfToken = new lib\CSRFToken();

    if (!$csrfToken->isValid($sentToken)) {
        lib\Util::responseError(400, '不正リクエストです');
    }

    if (!preg_match('/^[A-Za-z0-9]{8}$/', $targetUserId)) {
        lib\Util::responseError(400, 'ユーザーIDは英数字8文字で入力してください');
    }

    // サインイン状態の確認
    $sessionHandler = new lib\Session();
    if (!$sessionHandler->isSignedIn()) {
        lib\Util::responseError(401, 'サインインしてください');
    }

    $currentUserId = $sessionHandler->getCurrentUserID();

    // 相手ユーザーの内部IDを取得 自分がブロックされていない場合のみ進む
    $targetUser = models\User::query()
        ->where('user_id', $targetUserId)
        ->whereDoesntHave(models\BlockList::class, function ($query) use ($currentUserId, $targetUserId) {
            $query->where('blocked_user_id', $currentUserId)
                ->where('user_id', $targetUserId);
        })
        ->first(['id']);

    // 相手の内部IDを取得
    $targetInternalId = $targetUser ? $targetUser->id : null;
    if (!$targetInternalId) {
        lib\Util::responseError(404, '相手ユーザーが見つかりません');
    }
    if ($currentUserId == $targetInternalId) {
        lib\Util::responseError(400, '自分とはチャットできません');
    }

    $minId = min($currentUserId, $targetInternalId);
    $maxId = max($currentUserId, $targetInternalId);
    $groupName = 'dm_' . $minId . '_' . $maxId;

    $existingGroup = models\Group::query()
        ->where('name', $groupName)
        ->first(['id'])['id'] ?? null;

    if ($existingGroup) {
        $groupId = $existingGroup->id;
    } else {
        // グループが存在しない場合は新規作成
        $groupId = models\Group::query()->insert([
            'name' => $groupName,
            'is_public' => 0,
        ])->id;

        // グループメンバーに自分と相手を追加
        models\GroupMember::query()->insert([
            'group_id' => $groupId,
            'user_id'  => $currentUserId,
            'role'     => 'owner',
        ]);
        models\GroupMember::query()->insert([
            'group_id' => $groupId,
            'user_id'  => $targetInternalId,
            'role'     => 'member',
        ]);
    }

    $data = [
        'chat_link' => 'chat.php?group_id=' . $groupId
    ];

    lib\Util::responseSuccess($data, 'チャットを開始しました');
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
