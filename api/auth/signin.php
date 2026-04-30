<?php
require_once __DIR__ . '/../../vendor/autoload.php';

try{
    $mailaddress = $_POST['mailaddress'] ?? '';
    $password    = $_POST['password'] ?? '';

    $csrfToken = new lib\CSRFToken();


}
catch(\Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
?>