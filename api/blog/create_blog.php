<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログ作成API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - blog_title: ブログタイトル
 * - blog_content: ブログ本文
 * - blog_tags: ブログタグ
 * - blog_visibility: ブログの公開設定
 * - group_id: グループID
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ブログの作成に成功しました" }
 * {
 * "success": true,
 * "message": [],
 * "data": {
 *   "blog_id": ""
 * }
 *}
 * - エラー: { "success": false, "message": "エラーメッセージ" ,"サインインが必要です","不正なリクエストです","タイトルを入力してください","本文を入力してください","公開設定を選択してください","不正なクエリパラメータです"}
 */

try{
    // POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }
    
    // セッション管理
    $sessionHandler = new lib\Session();

    // サインイン管理
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    // パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $visibility = $_POST['visibility'] ?? '';
    $group_id = $_POST['group_id'] ?? '';
    $tags = $_POST['tags'] ?? '';

    $groupId = $_POST['group_id'] ?? null;
    $groupId = $groupId !== null && $groupId !== '' ? (int)$groupId : null;

    // CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    // バリデーション
    if(!$title){
        lib\Util::responseError(400,'タイトルを入力してください');
    }

    if($content === ''){
        lib\Util::responseError(400,'本文を入力してください');
    }

    if(!in_array($visibility, ['public', 'private','group'], true)){
        lib\Util::responseError(400,'公開設定を選択してください');
    }

    if(mb_strlen($title) > 255){
        lib\Util::responseError(400,'タイトルは255文字以内で入力してください');
    }

    //ログイン中ユーザーID取得
    $currentUserid = $sessionHandler->getCurrentUserID();

    //group投稿の場合
    if($visibility === 'group'){
        if($groupId === null){
            lib\Util::responseError(405,'グループを選択してください');
        }
    }

// group投稿の場合だけ所属グループ確認
if($visibility === 'group'){
    if($groupId === null){
        lib\Util::responseError(406,'グループを選択してください');
    }

    $member = models\GroupMember::query()
        ->where('user_id', $currentUserid)
        ->where('group_id', $groupId)
        ->first(['group_id']);

    if(!$member){
        lib\Util::responseError(407,'所属グループがありません');
    }
}else{
    $groupId = null;
}
    // ブログ作成
    $blog = models\Blog::create([
        'author_id' => $currentUserid,
        'group_id' => $groupId,
        'title' => $title,
        'content' => $content,
        'visibility' => $visibility,
        'tags' => $tags,
    ]);

    $date = [
       'blog_id' => $blog->id
    ];

    lib\Util::responseSuccess('ブログの作成に成功しました', $date);

}

catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());    
    lib\Util::responseError(500,'サーバーエラーが発生しました');    
}

?>