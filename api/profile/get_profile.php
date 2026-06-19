<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * プロフィール取得API
 * 必要なパラメータ:
 * - user_id: 5文字以上20文字以上のユーザーID
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: {"user_id": "他の人のユーザーID"}
 * {
 *  "success": true,
 *  "message": "プロフィールを取得しました", 
 *  "data": {
 *       "icon_url": "アイコンURL",
 *       "user_id": "ユーザーID",
 *       "display_name": "表示名",
 *       "introduction": "自己紹介文",
 *       "tags": 
 *       "blogs": [ブログID],
 *       "is_mine": false
 *   }
 * }
 * 
 * -成功: {"user_id": "自分のユーザーID","空白"}
 *{
 *   "success": true,
 *   "message": "プロフィールを取得しました",　
 *    "data": {
 *       "icon_url": "アイコンURL",
 *       "user_id": "自分のユーザーID",
 *       "display_name": "自分の表示名",
 *       "introduction": "自己紹介文",
 *       "tags": "",
 *       "blogs": [[ブログID, ブログタイトル], ...],
 *       "is_mine": true,
 *       "is_blocked": false
 *   }
 * }
 * 
 * - エラー: { "success": false, "message": "エラーメッセージ" : "ユーザーIDは英数字5-20文字で入力してください"}
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
    else if (!preg_match('/^[A-Za-z0-9]{5,20}$/', $targetUserId)) {
        lib\Util::responseError(400, 'ユーザーIDは英数字5-20文字で入力してください');
    }
    
    $targetUser = models\User::query()
        ->where('user_id', $targetUserId)
        ->first(['id', 'user_id']);

    if(!$targetUser){
        lib\Util::responseError(404, 'ユーザーが見つかりません');
    }

    $targetInternalId = $targetUser->id;

    $profile = models\Profile::query()
        ->where('user_id', $targetInternalId)
        ->first(['display_name', 'introduction', 'icon_url', 'tags']);

    if(!$profile){
        lib\Util::responseError(404, 'プロフィールが見つかりません');
    }

    $blogs = models\Blog::query()
        ->where('author_id', $targetInternalId)
        ->where('visibility', 'public')
        ->orderBy('created_at', 'desc')
        ->get(['id', 'title']);

    $blogIds = [];
    if ($blogs) {
        foreach ($blogs as $blog) {
            $blogIds[] = [$blog['id'], $blog['title']];
        }
    }

    $is_blocked = false;
    if($currentUserId !== $targetInternalId){
        $exists = models\BlockList::query()
            ->where('user_id', $currentUserId)
            ->where('blocked_user_id', $targetInternalId)
            ->first(['id'])['id'] ?? null;
        $is_blocked = (bool)$exists;
    }

    $data = [
        'icon_url'     => $profile->icon_url,
        'user_id'      => $targetUser->user_id,
        'display_name' => $profile->display_name,
        'introduction' => $profile->introduction,
        'tags'         => $profile->tags,
        'blogs'        => $blogIds,
        'is_mine'      => $currentUserId == $targetInternalId,
        'is_blocked'   => $is_blocked
    ];

    lib\Util::responseSuccess('プロフィールを取得しました',$data);
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
