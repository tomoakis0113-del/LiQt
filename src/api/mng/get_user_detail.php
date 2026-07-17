<?php
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json; charset=UTF-8');

/**
 * 運営管理 ユーザー詳細取得API
 * 必要なパラメータ:
 * - csrf_token
 * - user_id
 */

function getMngDetailValue($data, $key, $default = null)
{
    if(is_array($data)){
        return $data[$key] ?? $default;
    }

    if(is_object($data)){
        return $data->{$key} ?? $default;
    }

    return $default;
}

try{

    // POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        if(ob_get_length()){
            ob_clean();
        }

        lib\Util::responseError(405, '許可されていないリクエストです');
    }

    // セッション管理
    $sessionHandler = new lib\Session();

    // サインイン確認
    if(!$sessionHandler->isSignedIn()){
        if(ob_get_length()){
            ob_clean();
        }

        lib\Util::responseError(401, 'サインインが必要です');
    }

    // パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $targetUserId = $_POST['user_id'] ?? '';

    if($targetUserId === ''){
        if(ob_get_length()){
            ob_clean();
        }

        lib\Util::responseError(400, 'ユーザーIDが指定されていません');
    }

    // CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        if(ob_get_length()){
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

    if(!$currentUser){
        if(ob_get_length()){
            ob_clean();
        }

        lib\Util::responseError(401, 'ユーザー情報を取得できません');
    }

    $currentUserName = getMngDetailValue($currentUser, 'user_id', '');

    // 運営管理者として許可する user_id
    $managerUserIds = [
        'tomoaki2',
        'otonari'
    ];

    // 運営管理者チェック
    if(!in_array($currentUserName, $managerUserIds, true)){
        if(ob_get_length()){
            ob_clean();
        }

        lib\Util::responseError(403, '運営管理者権限が必要です');
    }

    // 対象ユーザー取得
    $targetUser = models\User::query()
        ->join('profiles', 'users.id', '=', 'profiles.user_id')
        ->where('users.user_id', '=', $targetUserId)
        ->first([
            'users.id as id',
            'users.user_id as user_id',
            'users.mail_address as mail_address',
            'users.is_active as is_active',
            'profiles.display_name as display_name',
            'profiles.icon_url as icon_url',
            'profiles.introduction as introduction',
            'profiles.tags as tags'
        ]);

    if(!$targetUser){
        if(ob_get_length()){
            ob_clean();
        }

        lib\Util::responseError(404, 'ユーザーが見つかりません');
    }

    $targetInternalId = getMngDetailValue($targetUser, 'id');
    $targetScreenUserId = getMngDetailValue($targetUser, 'user_id', '');

    $isAdmin = in_array($targetScreenUserId, $managerUserIds, true);

    // 投稿ブログ一覧
    $blogs = models\Blog::query()
        ->where('author_id', '=', $targetInternalId)
        ->get([
            'id',
            'title',
            'visibility',
            'created_at'
        ]);

    $resultBlogs = [];

    foreach($blogs as $blog){
        $resultBlogs[] = [
            'blog_id' => getMngDetailValue($blog, 'id'),
            'title' => getMngDetailValue($blog, 'title', ''),
            'visibility' => getMngDetailValue($blog, 'visibility', ''),
            'created_at' => getMngDetailValue($blog, 'created_at', '')
        ];
    }

    // 所属グループ一覧
    // group_members には id がないので group_id を取得する
    $groupMembers = models\GroupMember::query()
        ->where('user_id', '=', $targetInternalId)
        ->get([
            'group_id',
            'role'
        ]);

    $resultGroups = [];

    foreach($groupMembers as $member){

        $groupId = getMngDetailValue($member, 'group_id');
        $role = getMngDetailValue($member, 'role', '');

        if(!$groupId){
            continue;
        }

        $group = models\Group::query()
            ->where('id', '=', $groupId)
            ->first([
                'id',
                'name'
            ]);

        if(!$group){
            continue;
        }

        $resultGroups[] = [
            'group_id' => getMngDetailValue($group, 'id'),
            'group_name' => getMngDetailValue($group, 'name', ''),
            'role' => $role
        ];
    }

    // ブロック一覧
    $blockRows = models\BlockList::query()
        ->where('user_id', '=', $targetInternalId)
        ->get([
            'blocked_user_id'
        ]);

    $resultBlocks = [];

    foreach($blockRows as $block){

        $blockedInternalId = getMngDetailValue($block, 'blocked_user_id');

        if(!$blockedInternalId){
            continue;
        }

        $blockedUser = models\User::query()
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('users.id', '=', $blockedInternalId)
            ->first([
                'users.user_id as user_id',
                'profiles.display_name as display_name'
            ]);

        if(!$blockedUser){
            continue;
        }

        $resultBlocks[] = [
            'blocked_user_id' => getMngDetailValue($blockedUser, 'user_id', ''),
            'blocked_display_name' => getMngDetailValue($blockedUser, 'display_name', '')
        ];
    }

    // レスポンス作成
    $data = [
        'id' => $targetInternalId,
        'user_id' => $targetScreenUserId,
        'display_name' => getMngDetailValue($targetUser, 'display_name', ''),
        'mail_address' => getMngDetailValue($targetUser, 'mail_address', ''),
        'icon_url' => getMngDetailValue($targetUser, 'icon_url', ''),
        'introduction' => getMngDetailValue($targetUser, 'introduction', ''),
        'tags' => getMngDetailValue($targetUser, 'tags', ''),
        'is_active' => (int)getMngDetailValue($targetUser, 'is_active', 0),
        'is_admin' => $isAdmin,
        'blogs' => $resultBlogs,
        'groups' => $resultGroups,
        'blocks' => $resultBlocks
    ];

    if(ob_get_length()){
        ob_clean();
    }

    lib\Util::responseSuccess('ユーザー詳細を取得しました', $data);

}catch(\Throwable $e){

    error_log("エラーが発生しました: " . $e->getMessage());
    error_log("ファイル: " . $e->getFile());
    error_log("行番号: " . $e->getLine());

    if(ob_get_length()){
        ob_clean();
    }

    lib\Util::responseError(500, 'サーバーエラーが発生しました');

}