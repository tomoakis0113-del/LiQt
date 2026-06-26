<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * プロフィール更新API
 * 必要なパラメータ:
 * - csrf_token
 * - display_name
 * - introduction
 * - tags
 * - icon 任意
 * 
 * レスポンス:
 * - 成功:
 * {
 *   "success": true,
 *   "message": "プロフィール更新に成功しました",
 *   "data": []
 * }
 */
try{
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    // パラメータの受け取りとバリデーション
    $sentToken    = $_POST['csrf_token'] ?? '';
    $displayName  = trim($_POST['display_name'] ?? '');
    $introduction = trim($_POST['introduction'] ?? '');
    $tags         = trim($_POST['tags'] ?? '');

    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    if($displayName === ''){
        lib\Util::responseError(400,'表示名を入力してください');
    }

    if(mb_strlen($displayName) > 100){
        lib\Util::responseError(400,'表示名は100文字以内で入力してください');
    }

    if(mb_strlen($introduction) > 1000){
        lib\Util::responseError(400,'自己紹介は1000文字以内で入力してください');
    }

    if($tags !== '' && !preg_match('/^[^,]+(,[^,]+)*$/u', $tags)){
        lib\Util::responseError(400,'タグは半角カンマ区切りで入力してください');
    }

    // サインイン状態の確認
    $sessionHandler = new lib\Session();
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインしてください');
    }
    $currentUserId = $sessionHandler->getCurrentUserID();

    // AIチェック
    $ai = new lib\AI();
    if(!$ai->word_check($displayName . $introduction . $tags)){
        lib\Util::responseError(400,'プロフィールに不適切な内容が含まれています');
    }

    $iconUrl = null;
    if(isset($_FILES['icon']) && $_FILES['icon']['error'] !== UPLOAD_ERR_NO_FILE){
        if($_FILES['icon']['error'] !== UPLOAD_ERR_OK){
            lib\Util::responseError(400,'アイコンのアップロードに失敗しました');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if(!in_array($_FILES['icon']['type'], $allowedTypes, true)){
            lib\Util::responseError(400,'アイコンは画像ファイルを指定してください');
        }

        $uploadDir = __DIR__ . '/../../uploads/icons/';

        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
        $fileName = 'icon_' . $currentUserId . '_' . time() . '.' . $extension;
        $savePath = $uploadDir . $fileName;

        if(!move_uploaded_file($_FILES['icon']['tmp_name'], $savePath)){
            lib\Util::responseError(500,'アイコンの保存に失敗しました');
        }

        $iconUrl = '/uploads/icons/' . $fileName;
    }

    // プロフィール更新
    if($iconUrl !== null){
        models\Profile::query()
            ->where('user_id', $currentUserId)
            ->update([
                'display_name' => $displayName,
                'introduction' => $introduction,
                'tags' => $tags,
                'icon_url' => $iconUrl
            ]);
    }else{
        models\Profile::query()
            ->where('user_id', $currentUserId)
            ->update([
                'display_name' => $displayName,
                'introduction' => $introduction,
                'tags' => $tags
            ]);
    }

    lib\Util::responseSuccess( 'プロフィールを更新しました');
}
catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました: ' . $e->getMessage());
}
?>