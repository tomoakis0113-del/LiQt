<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * グループ削除API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - keyword: 検索キーワード（グループ名に部分一致するものを検索）
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "公開グループの取得に成功しました", "data": [ { "group_id": グループID, "group_name": グループ名, "group_icon": グループアイコンURL, "last_message_time": 最終メッセージの日時, "latest_message": 最終メッセージの内容 }, ... ] }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try {
    // データ受け取り
    $csrf_token = $_POST['csrf_token'] ?? null;
    $keyword = $_POST['keyword'] ?? null;

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

    // 所属しているグループまたはパブリックなグループを検索
    $query = models\Group::query()
        ->leftJoin('group_members', 'groups.id', '=', 'group_members.group_id')
        ->where(function($q) use ($user_id) {
            $q->where('group_members.user_id', '=', $user_id)
              ->orWhere('groups.is_public', '=', true);
        })
        ->distinct();

    if ($keyword) {
        $query->where('groups.name', 'LIKE', "%{$keyword}%");
    }
    $groups = $query->get(['groups.id as group_id', 'groups.name as group_name', 'groups.group_icon_url as group_icon']);
    lib\Util::responseSuccess('グループの取得に成功しました', $groups);
} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
