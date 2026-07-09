<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ユーザ検索API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - query: 検索キーワード（ユーザIDに部分一致するものを検索）
 * * レスポンス:
 * - 成功: { 
 * "success": true, 
 * "message": "ユーザの取得に成功しました", 
 * "data":[
 * {
 * "icon_url": アイコンURL,
 * "user_id": ユーザID,
 * "display_name": 表示名,
 * "introduction": 自己紹介,
 * "tags": タグ
 * },
 * ...
 * ]
 * }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try {
    // データ受け取り
    $csrf_token = $_POST['csrf_token'] ?? null;
    $query = $_POST['query'] ?? null;

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

    // 条件に一致するユーザーを検索
    $searchQuery = models\User::query()
        ->join('profiles', 'users.id', '=', 'profiles.user_id')
        ->where('users.user_id', 'LIKE', "%{$query}%")
        ->where('profiles.tags', 'LIKE', "%{$query}%", 'OR')
        ->where('profiles.display_name', 'LIKE', "%{$query}%", 'OR');
        
    // 💡 icon_url を取得カラムにしっかりと追加
    $users = $searchQuery->get(
        [
            'users.user_id as user_id', 
            'profiles.display_name as display_name', 
            'profiles.introduction as introduction', 
            'profiles.tags as tags',
            'profiles.icon_url as icon_url' 
        ]
    );
    lib\Util::responseSuccess('ユーザの取得に成功しました', $users);
} catch (Exception $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}