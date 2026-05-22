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
    $sentToken = $_POST['csrf_token'] ?? '';
    $blogId = $_POST['blog_id'] ?? '';
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $visibility = $_POST['visibility'] ?? '';
    $tags = $_POST['tags'] ?? '';

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

    //入力チェックをする
    $validator = new lib\Validator($blogData);

    $validator->required('title');
    $validator->required('content');
    $validator->required('visibility');
    $validator->required('tags');

    if($validator->errors()){
        lib\Util::responseError(400,$validator->errors());
    }

    //編集対象のブログをDBから探す
    $blog = new models\Blog();
    $blog = $blog->find($blogId);
    
    //

 




?>