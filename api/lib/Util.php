<?php
namespace lib;

use Dotenv\Dotenv;
class UtilException extends \Exception {}

/**
 * Utilクラス
 * このクラスは、一般的なユーティリティ関数を提供します。
 */
class Util {
    /**
     * 環境変数を読み込む
     * .envファイルから環境変数を読み込みます。
     */
    public static function loadEnv(): void {
        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to load environment variables: " . $e->getMessage());
            throw new UtilException('環境変数の読み込みに失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * ローカルホストかどうかをチェック
     * @return bool ローカルホストの場合はtrue、それ以外はfalse
     */
    public static function isLocalhost() {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        return $serverName === 'localhost' || $serverName === '127.0.0.1';
    }
    
    /**
     * ランダムな文字列を生成
     * @param int $length 生成する文字列の長さ
     * @return string 生成されたランダムな文字列
     */
    public static function generateRandomString($length = 5) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * APIの成功レスポンスを返す
     * @param array $data レスポンスデータ
     * @param string $message レスポンスメッセージ
     */
    public static function responseSuccess($data = [], $message = 'Success') {
        header('Content-Type: application/json');
        exit(
            json_encode([
                'status' => 'success',
                'message' => $message,
                'data' => $data
            ])
        );
    }

    /**
     * APIのエラーレスポンスを返す
     * @param int $errorCode HTTPステータスコード
     * @param string $message エラーメッセージ
     */
    public static function responseError($errorCode = 500, $message = 'Error') {
        http_response_code($errorCode);

        header('Content-Type: application/json');
        exit(
            json_encode([
                'status' => 'error',
                'message' => $message
            ])
        );
    }
}
?>