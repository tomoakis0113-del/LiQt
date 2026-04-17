<?php
namespace lib;

use lib\PDOHandler;
use Dotenv\Dotenv;

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
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }

    /**
     * データベースに接続する
     */
    public static function connectDB(): PDOHandler {
        Util::loadEnv();
        return PDOHandler::getInstanceMYSQL($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
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
     * ログを追加
     * @param PDOHandler $pdoHandler PDOハンドラー
     * @param string $message ログメッセージ
     */
    public static function addLog(PDOHandler $pdoHandler, string $message, string $status = 'undefined'): void {
        try{
            $ipAddr    = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $pdoHandler->exec("INSERT INTO log(user_id, user_agent, ip, message, status) VALUES(:user_id, :user_agent, :ip, :message, :status)", [":user_id"=>$_SESSION['user_id'], ":user_agent"=>$userAgent, ":ip"=>$ipAddr, ":message"=>$message, ":status"=>$status]);
        }
        catch(\Exception $e){
            error_log("ログの追加に失敗しました: " . $e->getMessage());
        }
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
}
?>