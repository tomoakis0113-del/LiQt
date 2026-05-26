<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * 
 * 
 * 
 * 
 * 
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
    $tag_serch = $_POST['tag_serch'] ?? '';
    $group_filter = $_POST['group_filter'] ?? '';

    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($tag_serch === '' && $group_filter === ''){
        lib\Util::responseError(400,'検索条件を指定してください');
    }
    
    //タグ検索
    if($tag_serch !== ''){
        $tag_serch = explode(',', $tag_serch);
        $tag_serch = array_map('trim', $tag_serch);
        $tag_serch = array_filter($tag_serch);
    }

    //グループ絞り込み
    if($group_filter !== ''){
        $group_filter = explode(',', $group_filter);
        $group_filter = array_map('trim', $group_filter);
        $group_filter = array_filter($group_filter);
    }

//ログイン中ユーザーID取得
$currentUserId = $sessionHandler->getCurrentUserID();

//DBからブログを取得する準備
$query = models\Blog::query();

//タグ検索
if($tag_serch !== ''){
    $query->where('tags', 'LIKE', '%' . $tag_serch . '%');
}

//グループ絞り込み
if($group_filter !== ''){
    if(!ctype_digit((string)$group_filter)){
        lib\Util::responseError(400,'グループIDの形式が正しくありません');
    }

    $groupId = (int)$group_filter;

    //そのグループに所属しているか確認
    $member = models\GroupMember::query()
        ->where('user_id', $currentUserId)
        ->where('group_id', $groupId)
        ->first(['group_id']);

    if(!$member){
        lib\Util::responseError(403,'このグループのブログは検索できません');
    }

    $query->where('group_id', $groupId);
}

//閲覧できる範囲だけ検索
$query->where(function($q) use ($currentUserId){
    $q->where('visibility', 'public')
      ->orWhere(function($q2) use ($currentUserId){
          $q2->where('visibility', 'private')
             ->where('author_id', $currentUserId);
      });
});

//グループ記事も含める場合
$query->orWhere(function($q) use ($currentUserId){
    $q->where('visibility', 'group')
      ->whereIn('group_id', function($sub) use ($currentUserId){
          $sub->select('group_id')
              ->from('group_members')
              ->where('user_id', $currentUserId);
      });
});

$blogs = $query
    ->orderBy('created_at', 'desc')
    ->get(['id', 'title', 'tags', 'visibility']);
    $publicBlogs = [];
    $privateBlogs = [];
    $myBlogs = [];

    foreach($blogs as $blog){
        if($blog->visibility === 'public'){
            $publicBlogs[] = $blog;
        }elseif($blog->visibility === 'private'){
            $privateBlogs[] = $blog;
        }else{
            $myBlogs[] = $blog;
        }
    }

    //レスポンス用に整形
    $publicBlogList = [];
    $privateBlogList = [];
    $myBlogList = [];

    foreach($publicBlogs as $blog){
        $publicBlogList[] = [
            'blog_id' => $blog->id,
            'title' => $blog->title,
            'tags' => $blog->tags ?? ''
        ];
    }

    foreach($privateBlogs as $blog){
        $privateBlogList[] = [
            'blog_id' => $blog->id,
            'title' => $blog->title,
            'tags' => $blog->tags ?? ''
        ];
    }

    foreach($myBlogs as $blog){
        $myBlogList[] = [
            'blog_id' => $blog->id,
            'title' => $blog->title,
            'tags' => $blog->tags ?? '',
            'visibility' => $blog->visibility
        ];
    }

    $response = [
        'public_blogs' => $publicBlogList,
        'private_blogs' => $privateBlogList,
        'my_blogs' => $myBlogList
    ];

    lib\Util::responseSuccess('ブログを取得しました', $response);   

    

}catch(Exception $e){
    lib\Util::responseError(500,$e->getMessage());

}




?>