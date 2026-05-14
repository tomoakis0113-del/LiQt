<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ダッシュボード表示API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "サインインに成功しました" }
 * - エラー: { "success": false, "message": "エラ
 */

use models\GroupMember as GroupMemberModel;
use models\Chat as ChatModel;


try{

    //POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    //セッション管理
    $sessionHandler = new lib\Session();

    //サインイン管理
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    //パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';

    $csrfToken = new lib\CSRFToken();

    //CSRFチェック
    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //ログイン中ユーザーID取得
    $currentUserid = $sessionHandler->getCurrentUserID();

    //参加中グループ取得
    $groupMembers = GroupMemberModel::query()
        ->with('group')
        ->where('user_id', $currentUserid)
        ->get();
    
    $joinedGroups = [];
    foreach($groupMembers as $groupMember){
        $group = $groupMember->group;

        if(!$group){
            continue;
        }

        //最新のメッセージを取得
        $lateestChat = ChatModel::query()
            ->where('group_id', $group->id)
            ->orderBy('created_at', 'desc')
            ->first(['content']);

        $joinedGroups[] = [
            'group_id' => $group->id,
            'group_name' => $group->name,
            'group_icon' => $group->group_icon_url ??'',
            'latest_message' => $lateestChat ? $lateestChat->content : ''
            ];
    }

    //レスポンス作成
    $data = [
        'joined_groups' => $joinedGroups
    ];
    lib\Util::responseSuccess($data,'ダッシュボード情報を取得しました');
    }

    catch(\Throwable $e){
        error_log("エラーが発生しました: " . $e->getMessage());
        lib\Util::responseError(500, 'サーバーエラーが発生しました');    
    }
?>