<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * グループ削除API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try {
    // データ受け取り
    $csrf_token = $_POST['csrf_token'] ?? null;

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

    // 公開グループのリストを取得
    $publicGroups = models\Group::query()
        ->where('is_public', '=', true)
        ->leftJoin('chats', 'groups.id', '=', 'chats.group_id')
        ->orderBy('chats.created_at', 'desc')
        ->get(['groups.id as group_id', 'groups.name as group_name', 'groups.group_icon_url as group_icon', 'chats.created_at as last_message_time', 'chats.content as latest_message']);

    lib\Util::responseSuccess('公開グループの取得に成功しました', $publicGroups);
} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
