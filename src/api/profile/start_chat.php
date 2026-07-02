<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * チャット開始API
 * 必要なパラメータ:
 * - csrf_token
 * - user_id: 相手のユーザーID　5文字以上20文字以下
 * 
 * -成功: {"user_id": "相手のユーザーID"} 
 * {
 *    "success": true,
 *    "message": "チャットを開始しました",
 *    "data": {
 *      "chat_link" : "chat.php?group_id=グループID" 
 *    }
 *}
 * 
 * - エラー: { "success": false, "message": "ユーザーIDは英数字5-20文字で入力してください" ,"自分とはチャットできません","サインインが必要です"}
 * 
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

    if (!preg_match('/^[A-Za-z0-9]{5,20}$/', $targetUserId)) {
        lib\Util::responseError(400, 'ユーザーIDは英数字5-20文字で入力してください');
    }

    // サインイン状態の確認
    $sessionHandler = new lib\Session();
    if (!$sessionHandler->isSignedIn()) {
        lib\Util::responseError(401, 'サインインしてください');
    }
    $currentInternalUserId = $sessionHandler->getCurrentUserID();
    $currentUserId = models\User::query()
        ->where('id', $currentInternalUserId)
        ->first(['user_id'])['user_id'] ?? null;

    // 相手ユーザーの内部IDを取得 自分がブロックされていない場合のみ進む
    $targetUser = models\User::query()
        ->where('user_id', $targetUserId)
        ->first(['id']);

    if(!$targetUser){
        lib\Util::responseError(404, '相手ユーザーが見つかりません');
    }

    $targetInternalId = $targetUser->id;

    // 相手の内部IDを取得
    $targetInternalId = $targetUser ? $targetUser->id : null;
    if (!$targetInternalId) {
        lib\Util::responseError(404, '相手ユーザーが見つかりません');
    }
    if ($currentInternalUserId == $targetInternalId) {
        lib\Util::responseError(400, '自分とはチャットできません');
    }

    // 相手が自分をブロックしている場合はチャット開始不可
    $blocked = models\BlockList::query()
        ->where('user_id', $targetInternalId)
        ->where('blocked_user_id', $currentInternalUserId)
        ->first(['id']);

    if($blocked){
        lib\Util::responseError(403, 'ブロックされているため、このユーザーとはチャットできません');
    }

    $minId = min($currentUserId, $targetUserId);
    $maxId = max($currentUserId, $targetUserId);
    $groupName = 'dm_' . $minId . '_' . $maxId;

    $existingGroup = models\Group::query()
        ->where('name', $groupName)
        ->first(['id']);

    if($existingGroup){
        $groupId = $existingGroup->id;
    }

    if ($existingGroup) {
        $groupId = $existingGroup->id;
    } else {
        // グループが存在しない場合は新規作成
        $groupId = models\Group::query()->insertGetId([
            'name' => $groupName,
            'is_public' => 0,
        ]);

        // グループメンバーに自分と相手を追加
        models\GroupMember::query()->insert([
            'group_id' => $groupId,
            'user_id'  => $currentInternalUserId,
            'role'     => 'owner',
        ]);
        models\GroupMember::query()->insert([
            'group_id' => $groupId,
            'user_id'  => $targetInternalId,
            'role'     => 'owner',
        ]);
    }

    $data = [
        'chat_link' => '/group/chat.php?group_id=' . $groupId
    ];

    lib\Util::responseSuccess('チャットを開始しました',$data);
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
