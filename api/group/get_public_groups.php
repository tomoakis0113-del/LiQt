<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * 公開グループ取得API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: [
 *      "success": true,
 *      "message": "公開グループの取得に成功しました",
 *      "data":[
 *        {
 *              "group_id": グループID,
 *              "group_name": グループ名,
 *              "group_icon": グループアイコンURL,
 *              "last_message_time": 最終メッセージの日時,
 *              "latest_message": 最終メッセージの内容
 *          },
 *    ]
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
    ->where('is_public', true)
    ->leftJoin('chats', 'groups.id', '=', 'chats.group_id')
    ->orderBy('chats.created_at', 'desc') 
    ->get([
        'groups.id as group_id',
        'groups.name as group_name',
        'groups.group_icon_url as group_icon',
        'chats.created_at as last_message_time',
        'chats.content as latest_message'
    ])
    ->unique('group_id');

    lib\Util::responseSuccess('公開グループの取得に成功しました', [...$publicGroups]);
} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
