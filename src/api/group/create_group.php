<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * グループ作成API
 * 必要なパラメータ:
 * - group_name: グループ名
 * - group_icon: グループアイコン（任意）
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
    $group_icon     = $_FILES['group_icon'] ?? null;
    $is_public      = filter_var($_POST['is_public'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $invite_user_ids= $_POST['invite_user_ids'] ?? null; // invite_user_ids[]
    $csrf_token     = $_POST['csrf_token'] ?? null;   
    
    error_log("Received data: group_name={$group_name}, is_public={$is_public}, invite_user_ids=" . json_encode($invite_user_ids));

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
    
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    // すでに同じグループ名が存在するか確認
    $existingGroup = models\Group::where('name', $group_name)->first(['id']);
    if($existingGroup){
        lib\Util::responseError(400,'同じグループ名が既に存在しています');
    }

    $group_icon_path = null;
    if($group_icon && $group_icon['error'] === UPLOAD_ERR_OK){
        $uploadDir = __DIR__ . '/../../uploads/group_icons/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($group_icon['name'], PATHINFO_EXTENSION);
        $iconName = 'group_' . time() . '.' . $ext;
        $iconPath = $uploadDir . $iconName;
        if (move_uploaded_file($group_icon['tmp_name'], $iconPath)) {
            $group_icon_path = '/uploads/group_icons/' . $iconName;
            $updated = true;
        }
    } else {
        $group_icon_path = null;
    }

    // グループ作成
    $group_id = models\Group::insertGetId([
        'name' => $group_name,
        'group_icon_url' => $group_icon_path,
        'is_public' => $is_public ? 1 : 0,
    ]);
    models\GroupMember::insert([
        'group_id' => $group_id,
        'user_id'  => $user_id,
        'role'     => 'owner',
    ]);

    // 招待ユーザがいる場合はグループに追加
    if(is_array($invite_user_ids)){
        foreach($invite_user_ids as $invite_user_id){
            // 招待ユーザIDからユーザのIDを取得
            $user = models\User::where('user_id', $invite_user_id)->first(['id']);
            $id = $user['id'] ?? ($user->id ?? null);
            if($id === null){
                continue; // ユーザが存在しない場合はスキップ
            }
            models\GroupMember::insert([
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