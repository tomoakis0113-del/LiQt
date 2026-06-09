<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * //必要なパラメータ
 * - csrf_token: CSRFトークン
 * - blog_id: 更新したいブログのID
 * - title: タイトル
 * - content: 内容
 * - visibility: 公開/非公開/グループ
 * - tags: タグ
 * 
 * 
 * //レスポンス
 * - 成功: { "success": true, "message": "ブログを更新しました" }
 * {
 *  "success": true,
 *  "message": "[]",
 *  "data": {
 *    "id": "ブログID",
 *    "title": "ブログのタイトル",
 *    "content": "ブログの内容",
 *    "visibility": "public / private / group",
 *    "tags": "ブログのタグ",
 *    "created_at": "2026-05-22T05:18:14.000000Z",
 *    "updated_at": "2026-05-26T04:44:30.000000Z"
 *  }
 *}
 * 
 * - 失敗: { "success": false, "message": "エラーッセージ","サインインが必要です","不正なリクエストです","ブログIDが指定されていません"}
 */


try{
    //POST以外を拒否する
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
    // パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $blogId = $_POST['blog_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $visibility = $_POST['visibility'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $groupId = trim($_POST['group_id'] ?? '');

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //編集データを受け取る
    $blogData = [
        'title' => $title,
        'content' => $content,
        'visibility' => $visibility,
        'tags' => $tags
    ];

    //入力チェック
    if($blogId === ''){
        lib\Util::responseError(400,'ブログIDが指定されていません');
    }

    if(!ctype_digit((string)$blogId)){
        lib\Util::responseError(400,'ブログIDの形式が正しくありません');
    }

    if(trim($title) === ''){
        lib\Util::responseError(400,'タイトルを入力してください');
    }

    if(trim($content) === ''){
        lib\Util::responseError(400,'本文を入力してください');
    }

    if(!in_array($visibility, ['public', 'private', 'group'], true)){
        lib\Util::responseError(400,'公開設定が正しくありません');
    }

    if(mb_strlen($title) > 255){
        lib\Util::responseError(400,'タイトルは255文字以内で入力してください');
    }
    // 編集対象のブログを取得
    $blog = models\Blog::query()    
    ->where('id', $blogId)
    ->first();

    if (!$blog) {
        lib\Util::responseError(404, 'ブログが見つかりません');
    }

    $currentUserId = $sessionHandler->getCurrentUserID();

    // 投稿者本人か確認
    if ((int)$blog->author_id !== (int)$currentUserId) {
        lib\Util::responseError(403, '編集権限がありません');
    }

    // グループ公開時の確認
    if ($visibility === 'group') {

        if ($groupId === '' || !ctype_digit($groupId)) {
            lib\Util::responseError(400, '公開するグループを選択してください');
        }

        $groupId = (int)$groupId;

        $member = models\GroupMember::query()
            ->where('user_id', $currentUserId)
            ->where('group_id', $groupId)
            ->first(['group_id']);

        if (!$member) {
            lib\Util::responseError(403, '所属していないグループには公開できません');
        }

    } else {
        $groupId = null;
    }

    // ブログ更新
    $blog->title = $title;
    $blog->content = $content;
    $blog->visibility = $visibility;
    $blog->group_id = $groupId;
    $blog->tags = $tags;
    $blog->save();

    $blog = [
        'id' => $blog->id,
        'title' => $blog->title,
        'content' => $blog->content,
        'visibility' => $blog->visibility,
        'tags' => $blog->tags,
        'created_at' => $blog->created_at,
        'updated_at' => $blog->updated_at,
    ];

    lib\Util::responseSuccess('ブログを更新しました',  $blog);

}catch(Exception $e){
    lib\Util::responseError(500,$e->getMessage());
}

 




?>