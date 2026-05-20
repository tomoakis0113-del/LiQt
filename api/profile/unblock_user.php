<?php
require_once __DIR__ . '/../../vendor/autoload.php';
/**
 * ブロック解除API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - user_id: ブロック解除したい相手のユーザーID
 * 
 * レスポンス:
 * - 成功: 
 *      "success": true,
 *      "message": [],
 *      "data": "ブロックを解除しました"
 *  }
 * 
 * - エラー: { "status": "error", "message": "エラーメッセージ","ユーザーが見つかりません","ブロックしてないユーザーです","サインインが必要です"} }
 */

try{
    //POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    //セッション管理
    $sessionHandler = new lib\Session();

    //サインイン確認
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインしてください');
    }

    //パラメータを受け取り
    $sentToken    = $_POST['csrf_token'] ?? '';
    $targetUserId = trim($_POST['user_id'] ?? '');

    $csrfToken = new lib\CSRFToken();

    //CSRFチェック
    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正リクエストです');
    }

    //バリデーション
    if($targetUserId === ''){
        lib\Util::responseError(400,'ユーザーIDを入力してください');
    }

    if(!preg_match('/^[A-Za-z0-9]{5,20}$/', $targetUserId)){
        lib\Util::responseError(400,'ユーザーIDは英数字5文字以上20文字以内で入力してください');
    }

    //ログイン中ユーザーID取得
    $currentUserId = $sessionHandler->getCurrentUserID();

    //相手ユーザー取得
    $targetUser = models\User::query()
        ->where('user_id', $targetUserId)
        ->first(['id']);

    if(!$targetUser){
        lib\Util::responseError(404,'ユーザーが見つかりません');
    }

    $targetInternalId = $targetUser->id;

    //自分自身は対象外
    if($currentUserId == $targetInternalId){
        lib\Util::responseError(400,'自分自身はブロック解除できません');
    }

    //ブロックしているか確認
    $block = models\BlockList::query()
        ->where('user_id', $currentUserId)
        ->where('blocked_user_id', $targetInternalId)
        ->first(['id']);

    if(!$block){
        lib\Util::responseError(404,'ブロックしていないユーザーです');
    }

    //ブロック解除
    models\BlockList::query()
        ->where('user_id', $currentUserId)
        ->where('blocked_user_id', $targetInternalId)
        ->delete();

    lib\Util::responseSuccess([], 'ブロックを解除しました');
}
catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500,'サーバーエラーが発生しました');
}
?>