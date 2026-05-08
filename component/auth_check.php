<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * 認証チェックコンポーネント
 * サインイン状態を確認し、必要に応じてリダイレクトします
 * 
 * 使用例:
 * require_once __DIR__.'/auth_check.php';
 * 
 * サインインが必要なページの先頭でこのファイルをインクルードしてください
 */
$sessionHandler = new lib\Session();
if(!$sessionHandler->isSignedIn()){
    header('Location: /auth/signin.php');
    exit();
}