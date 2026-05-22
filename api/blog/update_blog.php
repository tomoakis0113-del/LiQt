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
}




?>