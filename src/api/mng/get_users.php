<?php
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json; charset=UTF-8');

/**
 * 運営管理 ユーザー一覧取得API
 * 必要なパラメータ:
 * - csrf_token
 * - user_search
 * - status_filter
 */

function getColumnValue($row, $key, $default = null)
{
    if (is_object($row)) {
        return $row->{$key} ?? $default;
    }

    if (is_array($row)) {
        return $row[$key] ?? $default;
    }

    return $default;
}

function countByModel($modelClass, $column, $value)
{
    if (!class_exists($modelClass)) {
        return 0;
    }

    return $modelClass::query()
        ->where($column, '=', $value)
        ->get(['id'])
        ->count();
}

try {

    // POST以外を拒否
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        if (ob_get_length()) {
            ob_clean();
        }

        lib\Util::responseError(405, '許可されていないリクエストです');
    }

    // セッション管理
    $sessionHandler = new lib\Session();

    // サインイン確認
    if (!$sessionHandler->isSignedIn()) {
        if (ob_get_length()) {
            ob_clean();
        }

        lib\Util::responseError(401, 'サインインが必要です');
    }

    // パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $userSearch = trim($_POST['user_search'] ?? '');
    $statusFilter = $_POST['status_filter'] ?? 'すべて';

    // CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if (!$csrfToken->isValid($sentToken)) {
        if (ob_get_length()) {
            ob_clean();
        }

        lib\Util::responseError(400, '不正なリクエストです');
    }

    // ログイン中ユーザーID取得
    $currentUserId = $sessionHandler->getCurrentUserID();

    // ログイン中ユーザーの user_id を取得
    $currentUser = models\User::query()
        ->where('id', '=', $currentUserId)
        ->first(['id', 'user_id']);

    if (!$currentUser) {
        if (ob_get_length()) {
            ob_clean();
        }

        lib\Util::responseError(401, 'ユーザー情報を取得できません');
    }

    $currentUserName = getColumnValue($currentUser, 'user_id', '');

    // 運営管理者として許可する user_id
    $managerUserIds = [
        'tomoaki2',
        'otonari'
    ];

    // 運営管理者チェック
    if (!in_array($currentUserName, $managerUserIds, true)) {
        if (ob_get_length()) {
            ob_clean();
        }

        lib\Util::responseError(403, '運営管理者権限が必要です');
    }

    // ユーザー一覧取得
    $users = models\User::query()
        ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
        ->get([
            'users.id as id',
            'users.user_id as user_id',
            'users.mail_address as mail_address',
            'users.is_active as is_active',
            'profiles.display_name as display_name',
            'profiles.icon_url as icon_url'
        ]);

    $resultUsers = [];

    foreach ($users as $user) {

        $id = getColumnValue($user, 'id');
        $userId = getColumnValue($user, 'user_id', '');
        $displayName = getColumnValue($user, 'display_name', '');
        $mailAddress = getColumnValue($user, 'mail_address', '');
        $iconUrl = getColumnValue($user, 'icon_url', '');
        $isActive = (int)getColumnValue($user, 'is_active', 0);

        if (!$id) {
            continue;
        }

        // 運営管理者かどうか
        $isAdmin = in_array($userId, $managerUserIds, true);

        // 検索
        if ($userSearch !== '') {
            $hitUserId = stripos($userId, $userSearch) !== false;
            $hitDisplayName = stripos($displayName, $userSearch) !== false;
            $hitMailAddress = stripos($mailAddress, $userSearch) !== false;

            if (!$hitUserId && !$hitDisplayName && !$hitMailAddress) {
                continue;
            }
        }

        // 状態フィルター
        if ($statusFilter === '有効ユーザー' && $isActive !== 1) {
            continue;
        }

        if ($statusFilter === '停止中ユーザー' && $isActive !== 0) {
            continue;
        }

        if ($statusFilter === '運営管理者' && !$isAdmin) {
            continue;
        }

        // 投稿ブログ数
        $blogCount = countByModel('models\Blog', 'author_id', $id);

        // 所属グループ数
        $groupCount = models\GroupMember::query()
            ->where('user_id', '=', $id)
            ->get(['group_id'])
            ->count(); 
       // ブロック数
        $blockCount = countByModel('models\BlockList', 'user_id', $id);

        $resultUsers[] = [
            'id' => $id,
            'user_id' => $userId,
            'display_name' => $displayName,
            'mail_address' => $mailAddress,
            'icon_url' => $iconUrl,
            'is_active' => $isActive,
            'is_admin' => $isAdmin,
            'blog_count' => $blogCount,
            'group_count' => $groupCount,
            'block_count' => $blockCount
        ];
    }

    $data = [
        'users' => $resultUsers
    ];

    if (ob_get_length()) {
        ob_clean();
    }

    lib\Util::responseSuccess('ユーザー一覧を取得しました', $data);

} catch (\Throwable $e) {

    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);

    exit;
}