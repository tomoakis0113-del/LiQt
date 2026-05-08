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

    $sessionHandler = new lib\Session();
    if (!$sessionHandler->isSignedIn()) {
        lib\Util::responseError(401, 'サインインしてください');
    }

    $currentUserId = $sessionHandler->getCurrentUserID();

    // user_id未指定なら自分のプロフィール
    if ($targetUserId === '') {
        $userId = models\User::query()
            ->where('id', $currentUserId)
            ->first(['user_id'])['user_id'] ?? null;
        if (!$userId) {
            lib\Util::responseError(404, 'ユーザーが見つかりません');
        }

        $targetUserId = $userId;
    }
    // ユーザーIDは8文字のみ
    else if (!preg_match('/^[A-Za-z0-9]{8}$/', $targetUserId)) {
        lib\Util::responseError(400, 'ユーザーIDは英数字8文字で入力してください');
    }
    
    $profile = models\Profile::query()
        ->where('user_id', $targetUserId)
        ->first(['user_id', 'display_name', 'introduction', 'icon_url', 'tags']);
    if (!$profile) {
        lib\Util::responseError(404, 'プロフィールが見つかりません');
    }

    $targetInternalId = $profile->id;
    $blogs = models\Blog::query()
        ->where('author_id', $targetInternalId)
        ->where('visibility', 'public')
        ->orderBy('created_at', 'desc')
        ->get(['id']);

    $blogIds = [];
    if ($blogs) {
        foreach ($blogs as $blog) {
            $blogIds[] = $blog['id'];
        }
    }

    $data = [
        'icon_url'     => $profile->icon_url,
        'user_id'      => $profile->user_id,
        'display_name' => $profile->display_name,
        'introduction' => $profile->introduction,
        'tags'         => $profile->tags,
        'blogs'        => $blogIds,
        'is_mine'      => $currentUserId == $targetInternalId
    ];

    lib\Util::responseSuccess($data, 'プロフィールを取得しました');
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
