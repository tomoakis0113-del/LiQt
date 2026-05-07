<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * グループ作成API
 * 必要なパラメータ:
 * - group_name: グループ名
 * - group_icon: グループアイコンURL（任意）
 * - is_public: グループの公開設定（true/false）
 * - invite_user_ids: 招待するユーザIDの配列（任意）
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "グループの作成に成功しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // データ受け取り
    $group_name     = $_POST['group_name'] ?? null;
    $group_icon     = $_POST['group_icon'] ?? null;
    $is_public      = $_POST['is_public'] ?? null;
    $invite_user_ids= $_POST['invite_user_ids'] ?? null; // invite_user_ids[]
    $csrf_token     = $_POST['csrf_token'] ?? null;    

    // csrfトークンの検証
    $csrfToken = new lib\CSRFToken();
    if(!$csrf_token || !$csrfToken->isValid($csrf_token)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // バリデーション
    if(!$group_name){
        lib\Util::responseError(400,'グループ名を入力してください');
    }

    $sessionHandler = new lib\Session();
    $user_id = $sessionHandler->getCurrentUserID();
    
    if(!$sessionHandler->isLoggedIn()){
        lib\Util::responseError(401,'ログインが必要です');
    }

    // すでに同じグループ名が存在するか確認
    $existingGroup = models\Group::query()
        ->where('name', $group_name)
        ->first(['id']);
    if($existingGroup){
        lib\Util::responseError(400,'同じグループ名が既に存在しています');
    }

    // グループ作成
    $group_id = models\Group::query()->insert([
        'name' => $group_name,
        'group_icon_url' => $group_icon,
        'is_public' => $is_public ? 1 : 0,
    ])->id;
    models\GroupMember::query()->insert([
        'group_id' => $group_id,
        'user_id'  => $user_id,
        'role'     => 'owner',
    ]);

    // 招待ユーザがいる場合はグループに追加
    if(is_array($invite_user_ids)){
        foreach($invite_user_ids as $invite_user_id){
            // 招待ユーザIDからユーザのIDを取得
            $id = models\User::query()->where('user_id', $invite_user_id)->first(['id'])['id'] ?? null;
            if($id === null){
                continue; // ユーザが存在しない場合はスキップ
            }
            models\GroupMember::query()->insert([
                'group_id' => $group_id,
                'user_id'  => $id,
                'role'     => 'member',
            ]);
        }
    }

    lib\Util::responseSuccess('グループの作成に成功しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}