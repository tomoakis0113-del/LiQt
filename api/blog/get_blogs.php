<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログ取得API
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "ブログ一覧を取得します" }
 * - エラー: { "success": false, "message": "不正なリクエストです" ,"サインインが必要です"}
 */
   use models\Blog as BlogModel;

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

    $csrfToken = new lib\CSRFToken();

    //CSRFチェック
    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //ログイン中ユーザーID取得
    $currentUserid = $sessionHandler->getCurrentUserID();    

    //公開記事取得一覧
    $publicBlogs = BlogModel::query()
        ->where('visibility', 'public')
        ->orderBy('created_at', 'desc')
        ->get(['id','title','tags']);
    
    //自分の非公開記事一覧取得
    $privateBlogs = BlogModel::query()
        ->where('author_id',$currentUserid)
        ->where('visibility', 'private')
        ->orderBy('created_at', 'desc')
        ->get(['id','title','tags']);

    //自分の投稿一覧を取得
    $myBlogs = BlogModel::query()
        ->where('author_id', $currentUserid)
        ->orderBy('created_at', 'desc')
        ->get(['id','title','tags','visibility']);
    
    //公開記事をレスポンス用に整形
    $publicBlogList = [];

    foreach($publicBlogs as $blog){
        $publicBlogList[] = [
            'blog_id' => $blog->id,
            'title' => $blog->title,
            'tags' => $blog->tags ?? ''
        ];
    }

    //非公開記事をレスポンス用に整形
    $privateBlogList = [];

    foreach($privateBlogs as $blog){
        $privateBlogList[] = [
            'blog_id' => $blog->id,
            'title' => $blog->title,
            'tags' => $blog->tags ?? ''
        ];
    }

    //自分の記事をレスポンス用に整形
    $myBlogList = [];

    foreach($myBlogs as $blog){
        $myBlogList[] = [
            'blog_id' => $blog->id,
            'title' => $blog->title,
            'tags' => $blog->tags ?? '',
            'visibility' => $blog->visibility
        ];
    }

    //レスポンスデータ作成
    $data = [
        'public_blogs' => $publicBlogList,
        'private_blogs' => $privateBlogList,
        'my_blogs' => $myBlogList
    ];
    
    lib\Util::responseSuccess('ブログ一覧を取得します',$data);
    }

    catch(\Throwable $e){
        error_log("エラーが発生しました: " . $e->getMessage());
        lib\Util::responseError(500,'予期せぬエラーが発生しました');
    }


?>