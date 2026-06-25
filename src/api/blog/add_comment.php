<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログコメント追加API
 * 必要なパラメータ:
 * - csrf_token
 * - blog_id
 * - content
 * 
 * レスポンス:
 * - 成功: success: true/false
 * {
 *  "success": true,
 *  "message": {
 *    "comment_id": 3
 *  },
 *  "data": "コメントを追加しました"
 *}
 *
 * - 失敗: { "success": false, "message": "不正なリクエストです" ,"サインインが必要です"}
 * 
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
        lib\Util::responseError(401,'サインインが必要です');
    }

    //パラメータを受け取り
    $sentToken = $_POST['csrf_token'] ?? '';
    $blogId    = $_POST['blog_id'] ?? '';
    $content   = trim($_POST['content'] ?? '');

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($blogId === ''){
        lib\Util::responseError(400,'ブログIDを指定してください');
    }

    if(!ctype_digit((string)$blogId)){
        lib\Util::responseError(400,'ブログIDの形式が正しくありません');
    }

    if($content === ''){
        lib\Util::responseError(400,'コメントを入力してください');
    }

    if(mb_strlen($content) > 30000){
        lib\Util::responseError(400,'コメントは30000文字以内で入力してください');
    }

    $blogId = (int)$blogId;

    //ログイン中ユーザーID取得
    $currentUserId = $sessionHandler->getCurrentUserID();

//ブログ存在確認
$blog = models\Blog::query()
    ->where('id', $blogId)
    ->first([
        'id',
        'author_id',
        'visibility',
        'group_id'
    ]);

if(!$blog){
    lib\Util::responseError(404,'ブログが見つかりません');
}

// 非公開ブログは投稿者本人だけコメント可能//ブログ存在確認
$blog = models\Blog::query()
    ->where('id', $blogId)
    ->first([
        'id',
        'author_id',
        'visibility',
        'group_id'
    ]);

if(!$blog){
    lib\Util::responseError(404,'ブログが見つかりません');
}

// 非公開ブログは投稿者本人だけコメント可能
if($blog->visibility === 'private'){

    if((int)$blog->author_id !== (int)$currentUserId){
        lib\Util::responseError(403,'非公開ブログにはコメントできません');
    }

}

// グループブログは所属メンバーだけコメント可能
if($blog->visibility === 'group'){

    if(empty($blog->group_id)){
        lib\Util::responseError(403,'このグループブログにはコメントできません');
    }

    $member = models\GroupMember::query()
        ->where('group_id', $blog->group_id)
        ->where('user_id', $currentUserId)
        ->first(['id']);

    if(!$member){
        lib\Util::responseError(403,'グループメンバーのみコメントできます');
    }

}git config user.name 島田
git config user.email nkc20246178@st.denpa.jp
if($blog->visibility === 'private'){

    if((int)$blog->author_id !== (int)$currentUserId){
        lib\Util::responseError(403,'非公開ブログにはコメントできません');
    }

}

// グループブログは所属メンバーだけコメント可能
if($blog->visibility === 'group'){

    if(empty($blog->group_id)){
        lib\Util::responseError(403,'このグループブログにはコメントできません');
    }

    $member = models\GroupMember::query()
        ->where('group_id', $blog->group_id)
        ->where('user_id', $currentUserId)
        ->first(['id']);

    if(!$member){
        lib\Util::responseError(403,'グループメンバーのみコメントできます');
    }

}

    //コメント追加
    $comment = models\BlogComment::query()->create([
        'blog_id' => $blogId,
        'user_id' => $currentUserId,
        'content' => $content
    ]);

    $data = [
        'comment_id' => $comment->id
    ];

    lib\Util::responseSuccess($data, 'コメントを追加しました');

}catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500,'サーバーエラーが発生しました');
}
?>2