<?php
ob_start();

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json; charset=UTF-8');

/**
 * 運営管理トップ表示API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 *
 * レスポンス:
 * - 成功:
 * {
 *   "success": true,
 *   "message": "運営管理情報を取得しました",
 *   "data": {
 *     "user_count": 4,
 *     "blog_count": 10,
 *     "group_count": 3,
 *     "block_count": 2
 *   }
 * }
 *
 * - エラー:
 * {
 *   "success": false,
 *   "message": "エラーメッセージ"
 * }
 */

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

    $currentUserName = $currentUser['user_id'] ?? $currentUser->user_id ?? null;

    // 運営管理者として許可する user_id
    // users テーブルは変更しないので、ここで管理者を決める
    $managerUserIds = [
        'tomoaki2',
        'otonari',
        ''
    ];

    // 運営管理者チェック
    if(!in_array($currentUserName, $managerUserIds, true)){
        if(ob_get_length()){
            ob_clean();
        }

        lib\Util::responseError(403, '運営管理者権限が必要です');
    }

    // 件数取得
    $userCount = models\User::query()
        ->get(['id'])
        ->count();

    $blogCount = models\Blog::query()
        ->get(['id'])
        ->count();

    $groupCount = models\Group::query()
        ->get(['id'])
        ->count();

    $blockCount = models\BlockList::query()
        ->get(['id'])
        ->count();

    // レスポンス作成
    $data = [
        'user_count'  => $userCount,
        'blog_count'  => $blogCount,
        'group_count' => $groupCount,
        'block_count' => $blockCount
    ];

    if(ob_get_length()){
        ob_clean();
    }

    lib\Util::responseSuccess('運営管理情報を取得しました', $data);

}catch(\Throwable $e){

    error_log("エラーが発生しました: " . $e->getMessage());
    error_log("ファイル: " . $e->getFile());
    error_log("行番号: " . $e->getLine());

    if(ob_get_length()){
        ob_clean();
    }

    lib\Util::responseError(500, 'サーバーエラーが発生しました');

}