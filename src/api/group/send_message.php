<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * グループ作成API
 * 必要なパラメータ:
 * - group_id: グループID
 * - message_content: メッセージ内容
 * - image_upload: 画像ファイル（任意）
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "グループの作成に成功しました" }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // データ受け取り
    $message_content = $_POST['message_content'] ?? null;
    $image_upload    = $_FILES['image_upload'] ?? null;
    $group_id        = $_POST['group_id'] ?? null;
    $csrf_token      = $_POST['csrf_token'] ?? null;
    $csrfToken       = new lib\CSRFToken();

    if(!$message_content){
        lib\Util::responseError(400,'メッセージ内容を入力してください');
    }

    // ログイン済みかチェック
    $sessionHandler = new lib\Session();
    $user_id = $sessionHandler->getCurrentUserID();
    
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }
    if(!$csrf_token || !$csrfToken->isValid($csrf_token)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    // グループに属しているかどうか検証
    $isMember = models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $user_id)
        ->exists();
    if(!$isMember){
        lib\Util::responseError(403,'グループに属していません');
    }

    // AIチェック
    $ai = new lib\AI();
    if($ai->word_check($message_content)){
        lib\Util::responseError(400,'メッセージ内容に不適切な内容が含まれています');
    }
    // aiとのチャットかどうか
    $isAiChat = models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $ai->getId())
        ->exists();

    // 画像アップロード
    $image_path = null;
    if($image_upload && $image_upload['error'] === UPLOAD_ERR_OK){
        $uploadDir = __DIR__.'/../../uploads/messages/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = basename($image_upload['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($image_upload['tmp_name'], $targetFilePath)) {
            $image_path = 'uploads/messages/' . $fileName; // データベースに保存するパス
        } else {
            lib\Util::responseError(500, '画像のアップロードに失敗しました');
        }
    } else {
        $image_path = null;
    }

    // メッセージ保存
    $chat_id = models\Chat::create([
        'group_id' => $group_id,
        'sender_id' => $user_id,
        'content' => $message_content,
        'image_url' => $image_path
    ])->id;

// メンション通知を作ったかどうか
$mentionNoticeCreated = false;

// メッセージ本文から @user_id を検出
preg_match_all('/[＠@]([A-Za-z0-9]{5,20}|ai)(?=\s|　|$|。|、|,|\.|!|！|\?|\？)/u', $message_content, $matches);

$mentionUserIds = array_unique($matches[1] ?? []);

foreach($mentionUserIds as $mentionUserId){

    // users.user_id でユーザー検索
    $mentionedUser = models\User::query()
        ->where('user_id', '=', $mentionUserId)
        ->first(['id', 'user_id']);

    // 存在しないユーザーなら無視
    if(!$mentionedUser){
        continue;
    }

    // メンションされた人の内部IDを取得
    $mentionedUserId = $mentionedUser['id'] ?? $mentionedUser->id ?? null;

    if(!$mentionedUserId){
        continue;
    }

    // 自分自身へのメンションは通知しない
    if((int)$mentionedUserId === (int)$user_id){
        continue;
    }

    // メンションされた人が同じグループにいるか確認
    $isMentionedMember = models\GroupMember::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $mentionedUserId)
        ->exists();

    if(!$isMentionedMember){
        continue;
    }

    // メンションされた人がこのグループの通知をオフにしているか確認
    $isNoticeBlocked = models\NoticeBlock::query()
        ->where('group_id', '=', $group_id)
        ->where('user_id', '=', $mentionedUserId)
        ->exists();

    if($isNoticeBlocked){
        continue;
    }

    // メンションされた人が送信者をブロックしているなら通知しない
    $isBlocked = models\BlockList::query()
        ->where('user_id', '=', $mentionedUserId)
        ->where('blocked_user_id', '=', $user_id)
        ->exists();

    if($isBlocked){
        continue;
    }

    // 通知内容の安全対策
    $safeMessage = htmlspecialchars($message_content, ENT_QUOTES, 'UTF-8');

    // メンション通知を作成
    models\NoticeSchedule::create([
        "user_id" => $mentionedUserId,
        "content" => "<h1>メンションされました。</h1><p>メッセージ内容: {$safeMessage}</p>",
        "is_checking" => false
    ]);

    $mentionNoticeCreated = true;
}

    // aiとのチャットの場合、AIの応答を生成して保存
    if($isAiChat){
        if(!models\ReplySchedule::where("group_id","=",$group_id)->exists()){
            models\ReplySchedule::create([
                "group_id" => $group_id,
                "type" => "chat"
            ]);
        }
    }


    if(!$mentionNoticeCreated){

    // メンバーを追加
    $members = models\GroupMember::query()
        ->leftJoin('notice_blocks', function($join) use ($group_id) {
            $join->on('group_members.user_id', '=', 'notice_blocks.user_id')
                 ->where('notice_blocks.group_id', '=', $group_id);
        })
        ->where('group_members.group_id', '=', $group_id)
        ->where('group_members.user_id', '!=', $user_id)
        ->get(["group_members.user_id", "notice_blocks.id as block_id"])
        ->filter(function($member) {
            return is_null($member->block_id);
        });

    $safeMessage = htmlspecialchars($message_content, ENT_QUOTES, 'UTF-8');

    foreach($members as $member){
       models\NoticeSchedule::create([
            "user_id" => $member->user_id,
            "content" => "<h1>グループに新しいメッセージがあります。</h1><p>メッセージ内容: {$safeMessage}</p>",
            "is_checking" => false
        ]);
    }
}
    lib\Util::responseSuccess('メッセージの送信に成功しました');
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}