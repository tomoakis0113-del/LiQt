<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログ編集API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * - blog_id: 編集したいブログID
 *  
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ブログの編集に成功しました" }
 * {
 *"success": true,
 * "message": [],
 * "data": {
 *   "id": ブログID,
 *   "title": "ブログのタイトル",
 *   "content": "ブログの本文",
 *   "visibility": "public","private","group",
 *   "tags": "ブログのタグ",
 *   "created_at": "作成日時",
 *   "updated_at": "更新日時"
 * }
 *}
 * 
 * - 失敗: { "success": false, "message": "エラーメッセージ","サインインが必要です","不正なリクエストです","ブログIDが指定されていません" }
 * 
 */

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
    $blogId = $_POST['blog_id'] ?? '';

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($blogId === ''){
        lib\Util::responseError(400,'ブログIDが指定されていません');
    }

    //ログイン中のユーザーIDを取得
    $currentUserId = $sessionHandler->getCurrentUserID();

    //ブログ習得
    $blog = models\Blog::query()
        ->where('id', $blogId)
        ->first();

    if($blog === null){
        lib\Util::responseError(400,'指定されたブログが見つかりません');
    }

    //閲覧権限チェック
    if($blog->visibility === 'private'){
        //privateは投稿者本人だけ
        if($blog->author_id != $currentUserId){
            lib\Util::responseError(403,'このブログを見る権限がありません');
        }
    }

    if($blog->visibility === 'group'){
        //groupはそのグループに所属している人だけ
        $member = models\GroupMember::query()
            ->where('group_id', $blog->group_id)
            ->where('user_id', $currentUserId)
            ->first(['group_id']);

        if(!$member){
            lib\Util::responseError(403,'このグループのブログを見る権限がありません');
        }
    }
    //いいね数を取得
    $likeCount = models\BlogLike::query()
        ->where('blog_id', $blogId)
        ->count();

    //ログイン中ユーザーがいいね済みか確認
    $isLiked = models\BlogLike::query()
        ->where('blog_id', $blogId)
        ->where('user_id', $currentUserId)
        ->first(['blog_id', 'user_id']) ? true : false;

    $isAuthor = ($blog->author_id == $currentUserId);

    //コメント一覧を取得
    $comments = models\BlogComment::query()
        ->where('blog_id', $blogId)
        ->orderBy('created_at', 'desc')
        ->get(['id', 'user_id', 'content', 'created_at']);

    $commentList = [];

    foreach($comments as $comment){
        $commentUser = models\User::query()
            ->where('id', $comment->user_id)
            ->first(['id', 'user_id']);

        $commentProfile = models\Profile::query()
            ->where('user_id', $comment->user_id)
            ->first(['display_name', 'icon_url']);

        $commentList[] = [
            'comment_id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at,
            'is_mine' => $comment->user_id == $currentUserId,
            'author' => [
                'user_id' => $commentUser ? $commentUser->user_id : '',
                'display_name' => $commentProfile ? $commentProfile->display_name : '名無しユーザー',
                'icon_url' => $commentProfile ? $commentProfile->icon_url : ''
            ]
        ];
    }

    $author = models\User::query()
        ->join('profiles', 'users.id', '=', 'profiles.user_id')
        ->where('id', $blog->author_id)
        ->first(['users.user_id', 'profiles.display_name', 'profiles.icon_url']);

    $blog = [
        'id' => $blog->id,
        'title' => $blog->title,
        'content' => $blog->content,
        'visibility' => $blog->visibility,
        'tags' => $blog->tags,
        'created_at' => $blog->created_at,
        'updated_at' => $blog->updated_at,
        'likes' => $likeCount,
        'is_liked' => $isLiked,
        'is_author' => $isAuthor,
        'author' => $author,
        'comments' => $commentList

    ];
    lib\Util::responseSuccess('ブログを取得しました',  $blog);

}catch(Exception $e){
    lib\Util::responseError(500,'予期せぬエラーが発生しました');
}

?>