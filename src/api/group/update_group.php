<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * グループ情報の更新API
 * 必要なパラメータ
 * - group_id: 更新するグループのID
 * - group_name: グループ名（任意）
 * - group_icon: グループアイコン（任意）
 * - is_public: グループの公開設定（任意）
 * - add_user_ids: 追加するユーザーIDのカンマ区切り（任意）
 * - remove_user_ids: 削除するユーザーIDのカン区切り（任意）
 * - change_role_user_ids: ロール変更するユーザーIDのカンマ区切り（任意）
 * - new_roles: 変更後のロールのカンマ区切り（任意、change_role_user_idsと対応させて順番に指定）
 * - csrf_token: CSRFトークン
 * レスポンス
 * - 成功: { "success": true, "message": "グループ情報を更新しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try {
    // データ受け取り
    $csrf_token = $_POST['csrf_token'] ?? null;
    $group_id   = $_POST['group_id'] ?? null;

    $group_name = $_POST['group_name'] ?? null;
    $group_icon = $_FILES['group_icon'] ?? null;
    $is_public  = $_POST['is_public'] ?? null;
    $add_user_ids    = $_POST['add_user_ids'] ?? null;
    $remove_user_ids = $_POST['remove_user_ids'] ?? null;
    $change_role_user_ids   = $_POST['change_role_user_ids'] ?? null;
    $new_roles = $_POST['new_roles'] ?? null;
    
    $add_user_ids = $add_user_ids ? explode(',', $add_user_ids) : [];
    $remove_user_ids = $remove_user_ids ? explode(',', $remove_user_ids) : [];
    $change_role_user_ids = $change_role_user_ids ? explode(',', $change_role_user_ids) : [];
    $new_roles = $new_roles ? explode(',', $new_roles) : [];

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

    if (!$userRole || ($userRole !== 'owner' && $userRole !== 'manager')) {
        lib\Util::responseError(403, 'グループのメンバーではありません');
    }

    // AIチェック
    $ai = new lib\AI();
    if(!$ai->word_check($group_name)){
        lib\Util::responseError(400,'グループ名に不適切な内容が含まれています');
    }

    // グループ情報の更新
    $group = models\Group::find($group_id);
    $updated = false;
    if ($group_name !== null) {
        $group->name = $group_name;
        $updated = true;
    }
    if ($is_public !== null) {
        $group->is_public = (bool)$is_public;
        $updated = true;
    }
    // アイコンアップロード処理
    if ($group_icon && isset($group_icon['error']) && $group_icon['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/group_icons/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($group_icon['name'], PATHINFO_EXTENSION);
        $iconName = 'group_' . $group_id . '_' . time() . '.' . $ext;
        $iconPath = $uploadDir . $iconName;
        if (move_uploaded_file($group_icon['tmp_name'], $iconPath)) {
            $group->group_icon_url = '/uploads/group_icons/' . $iconName;
            $updated = true;
        }
    }
    if ($updated) {
        $group->save();
    }

    // メンバー追加
    if ($add_user_ids && is_array($add_user_ids)) {
        foreach ($add_user_ids as $add_id) {
            $exists = models\GroupMember::where('group_id', $group_id)->where('user_id', $add_id)->first();
            $add_id = models\User::where('user_id', $add_id)->first(['id'])['id'] ?? null;
            if (!$exists) {
                models\GroupMember::create([
                    'group_id' => $group_id,
                    'user_id' => $add_id,
                    'role' => 'member',
                ]);
            }
        }
    }

    // メンバー削除
    if ($remove_user_ids && is_array($remove_user_ids)) {
        if(in_array($user_id, $remove_user_ids)) {
            $ownerCount = models\GroupMember::where('group_id', $group_id)->where('role', 'owner')->count();
            if ($ownerCount <= 1) {
                lib\Util::responseError(400, '自分を削除するには、他にオーナーが必要です');
            }
        }

        foreach ($remove_user_ids as $remove_id) {
            models\GroupMember::where('group_id', $group_id)->where('user_id', $remove_id)->delete();
        }
    }

    // ロール変更
    if ($change_role_user_ids && $new_roles && is_array($change_role_user_ids) && is_array($new_roles)) {
        foreach ($change_role_user_ids as $idx => $uid) {
            $role = $new_roles[$idx] ?? null;
            if (!$role || !in_array($role, ['owner', 'manager', 'member'])) {
                continue;
            }

            // オーナーの場合
            if ($userRole === 'owner') {
                // 自分の権限を管理者に変更する場合のみ特別処理
                if ($uid == $user_id && $role === 'manager') {
                    // オーナーが1人以上残るかチェック
                    $ownerCount = models\GroupMember::where('group_id', $group_id)->where('role', 'owner')->count();
                    if ($ownerCount <= 1) {
                        // 自分しかオーナーがいない場合は変更不可
                        continue;
                    }
                    models\GroupMember::where('group_id', $group_id)->where('user_id', $uid)->update(['role' => $role]);
                } elseif ($uid != $user_id) {
                    // 他のメンバーのロールも変更可能
                    models\GroupMember::where('group_id', $group_id)->where('user_id', $uid)->update(['role' => $role]);
                }
                // オーナーが自分をオーナー以外に変更する場合のみ制限（上記）
            } elseif ($userRole === 'manager') {
                // 管理者は自分以外のメンバーのみ権限変更可能、かつオーナーの権限は変更不可
                if ($uid == $user_id) {
                    continue; // 自分は変更不可
                }
                // 変更対象がオーナーの場合は不可
                $targetRole = models\GroupMember::where('group_id', $group_id)->where('user_id', $uid)->first(['role'])['role'] ?? null;
                if ($targetRole === 'owner') {
                    continue;
                }
                models\GroupMember::where('group_id', $group_id)->where('user_id', $uid)->update(['role' => $role]);
            }
        }
    }

    lib\Util::responseSuccess('グループ情報を更新しました');

} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
