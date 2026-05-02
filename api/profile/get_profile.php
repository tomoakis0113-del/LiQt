<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * プロフィール取得API
 * 必要なパラメータ:
 * - user_id: 8文字以上のユーザーID
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * icon.url: アイコンURL
 * user_id: ユーザーID
 * display_name: 表示名
 * introduction: 自己紹介
 * tags: タグ
 * blogs: ブログ一覧
 * is_mine: 自分のプロフィールが表示されてるか（true/false）
 * 
 */
try {
    // POST以外を拒否
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        lib\Util::responseError(405, '許可されていないリクエストです');
    }

    $sentToken = $_POST['csrf_token'] ?? '';
    $targetUserId = trim($_POST['user_id'] ?? '');

    $csrfToken = new lib\CSRFToken();

    if (!$csrfToken->isValid($sentToken)) {
        lib\Util::responseError(400, '不正リクエストです');
    }

    $pdoHandler = lib\Util::connectDB();
    $sessionHandler = new lib\Session($pdoHandler);

    if (!$sessionHandler->isLoggedIn()) {
        lib\Util::responseError(401, 'ログインしてください');
    }

    $currentUserId = $sessionHandler->getCurrentUserID();

    // user_id未指定なら自分のプロフィール
    if ($targetUserId === '') {
        $userResult = $pdoHandler->exec(
            "SELECT user_id FROM users WHERE id = ?",
            [$currentUserId]
        );

        if (!$userResult) {
            lib\Util::responseError(404, 'ユーザーが見つかりません');
        }

        $targetUserId = $userResult[0]['user_id'];
    }
    // ユーザーIDは8文字のみ
    else if (!preg_match('/^[A-Za-z0-9]{8}$/', $targetUserId)) {
        lib\Util::responseError(400, 'ユーザーIDは英数字8文字で入力してください');
    }

    $profile = $pdoHandler->exec(
        "SELECT 
        users.id,
        users.user_id,
        profiles.display_name,
        profiles.introduction,
        profiles.icon_url,
        profiles.tags
    FROM users
    INNER JOIN profiles ON users.id = profiles.user_id
    WHERE users.user_id = ?",
        [$targetUserId]
    );
    if (!$profile) {
        lib\Util::responseError(404, 'プロフィールが見つかりません');
    }

    $targetInternalId = $profile[0]['id'];

    $blogs = $pdoHandler->exec(
        "SELECT id FROM blogs
         WHERE author_id = ?
         AND visibility = 'public'
         ORDER BY created_at DESC",
        [$targetInternalId]
    );

    $blogIds = [];
    if ($blogs) {
        foreach ($blogs as $blog) {
            $blogIds[] = $blog['id'];
        }
    }

    $data = [
        'icon_url'     => $profile[0]['icon_url'],
        'user_id'      => $profile[0]['user_id'],
        'display_name' => $profile[0]['display_name'],
        'introduction' => $profile[0]['introduction'],
        'tags'         => $profile[0]['tags'],
        'blogs'        => $blogIds,
        'is_mine'      => $currentUserId == $targetInternalId
    ];

    lib\Util::responseSuccess($data, 'プロフィールを取得しました');
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
