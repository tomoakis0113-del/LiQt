<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * サインアウトAPI
 * 必要なパラメータ:
 * - csrf_token: CSRFトークン
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "サインアウトに成功しました" }
 * - エラー: { "success": false, "message": "エラーが発生しました" }
 */
try {
    $csrfToken = new lib\CSRFToken();

    $sessionHandler = new lib\Session();
    $sessionHandler->signout();
    lib\Util::responseSuccess('サインアウトに成功しました');
} catch (\Throwable $e) {
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}
