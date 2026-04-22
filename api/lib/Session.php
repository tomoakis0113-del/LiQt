<?php
namespace lib;

use lib\PDOHandler;

const SESSION_KEY = 'user_id';
const SESSION_EXPIRE = 31*7*24*60*60; // 1ヶ月（秒）
const REMEMBER_COOKIE_NAME = 'remember_token';
const REMEMBER_COOKIE_EXPIRE = 60*60*24*30; // 30日（秒）

class SessionException extends \Exception {}

class Session {
    /** @var pdoHandler PDOハンドラーインスタンス */
    protected $pdoHandler;

    /**
     * テーブルがない場合は作成する
     */
    protected function createTablesIfNotExist() {
        try{
            $this->pdoHandler->exec(
                "CREATE TABLE IF NOT EXISTS session (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expire DATETIME NOT NULL,
                    user_agent VARCHAR(255) NOT NULL,
                    UNIQUE KEY (token),
                    INDEX (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );

            $this->pdoHandler->exec(
                "CREATE TABLE IF NOT EXISTS session_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    user_agent VARCHAR(255) NOT NULL,
                    ip VARCHAR(45) NOT NULL,
                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        }
        catch(\Exception $e){
            error_log("[SanaeProject] Faild to create session tables: " . $e->getMessage());
            throw new SessionException('セッションテーブルの作成に失敗しました: ' . $e->getMessage());
        }
    }

    /** コンストラクタ 
     * @param PDOHandler $pdoHandler PDOハンドラーインスタンス
     * @throws Exception セッションの開始に失敗した場合
    */
    public function __construct(PDOHandler $pdoHandler) {
        $this->pdoHandler = $pdoHandler;
        $this->createTablesIfNotExist();

        // セッションが開始されていない場合開始
        if (session_status() === PHP_SESSION_NONE){
            try{
                session_start();
            } catch (\Exception $e) {
                error_log("[SanaeProject] Faild to start session: " . $e->getMessage());
                throw new SessionException('セッションの開始に失敗しました: ' . $e->getMessage());
            }
        }
        
        // セッションの有効期限確認
        if (isset($_SESSION[SESSION_KEY])) {
            if (time() - $_SESSION['last_activity'] > SESSION_EXPIRE)
                $this->logout();
            else
                $_SESSION['last_activity'] = time();
        }
        // セッションがない場合、クッキートークンによる認証を試みる
        elseif (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
            $this->loginWithRememberToken($_COOKIE[REMEMBER_COOKIE_NAME]);
        }
    }
    
    /** リメンバートークンでログイン 
     * @param string $token リメンバートークン
     * @throws SessionException ログイン処理に失敗した場合
    */
    protected function loginWithRememberToken(string $token): void {
        try {
            $result = $this->pdoHandler->exec(
                "SELECT user_id FROM session WHERE token = :token AND expire > NOW() AND user_agent = :user_agent",
                ['token' => $token, 'user_agent' => $_SERVER['HTTP_USER_AGENT']]
            );
            
            if ($result && isset($result[0]['user_id'])) {
                // クッキー認証に成功したらセッションを設定
                $this->setSession($result[0]['user_id']);
                
                // トークンを更新
                $this->setRememberToken($result[0]['user_id']);
            } else {
                // 無効なトークンの場合はクッキーを削除
                setcookie(REMEMBER_COOKIE_NAME, '', time() - 3600, '/');
            }
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to login with remember token: " . $e->getMessage());
            throw new SessionException('リメンバートークンによるログインに失敗しました: ' . $e->getMessage());
        }
    }

    /** セッションを設定する 
     * @param int $userID ユーザーID
     * @throws SessionException セッション設定に失敗した場合
    */
    protected function setSession(int $userID) {
        try {
            session_regenerate_id(true); // セッションIDの再生成
            $_SESSION[SESSION_KEY] = $userID;
            $_SESSION['last_activity'] = time();

            $this->pdoHandler->exec(
                "INSERT INTO session_log (user_id, user_agent, ip) VALUES (?, ?, ?)",
                [$userID, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']]
            );
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to set session: " . $e->getMessage());
            throw new SessionException('セッション設定に失敗しました: ' . $e->getMessage());
        }
    }

    /** セッションを破棄する
     * @throws SessionException セッション破棄に失敗した場合
     */
    protected function destroySession() {
        try {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to destroy session: " . $e->getMessage());
            throw new SessionException('セッション破棄に失敗しました: ' . $e->getMessage());
        }
    }
    
    /** ログイン試行
     * @param string $userName ユーザー名
     * @param string $password パスワード
     * @param bool $remember ログイン状態を保持するかどうか
     * @return bool ログイン成功時true
     * @throws SessionException ログイン処理に失敗した場合
     */
    public function tryLogin(string $userName, string $password, bool $remember = false): bool {
        try {
            // 時間をずらしてログイン試行の頻度を下げる
            usleep(rand(500000, 3000000));

            $result = $this->pdoHandler->exec(
                "SELECT id,password FROM users WHERE name = ?",
                [$userName]
            );

            if ($result && password_verify($password, $result[0]['password'])) {
                $this->setSession($result[0]['id']);
                
                // 「ログイン状態を維持する」が選択されていればrememberトークンを設定
                if ($remember)
                    $this->setRememberToken($result[0]['id']);
                
                return true;
            }
            return false;
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to try login for user: " . $userName . " | Error: " . $e->getMessage());
            throw new SessionException('ログイン処理に失敗しました: ' . $e->getMessage());
        }
    }
    
    /** リメンバートークンを設定する 
     * @param int $userId ユーザーID
     * @throws SessionException トークン設定に失敗した場合
    */
    protected function setRememberToken(int $userId): void {
        try {
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + REMEMBER_COOKIE_EXPIRE);
            
            // ユーザーIDとトークンでの既存レコードを削除
            $this->pdoHandler->exec(
                "DELETE FROM session WHERE user_id = :user_id",
                ['user_id' => $userId]
            );
            
            // 新しいトークンを保存
            $this->pdoHandler->exec(
                "INSERT INTO session (user_id, token, expire, user_agent) VALUES (:user_id, :token, :expire, :user_agent)",
                [
                    'user_id' => $userId,
                    'token' => $token,
                    'expire' => $expiry,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                ]
            );
            
            // クッキーにトークンを保存
            setcookie(
                REMEMBER_COOKIE_NAME,
                $token,
                [
                    'expires' => time() + REMEMBER_COOKIE_EXPIRE,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to set remember token for user: " . $userId . " | Error: " . $e->getMessage());
            throw new SessionException('リメンバートークン設定に失敗しました: ' . $e->getMessage());
        }
    }

    /** ログイン状態確認 */
    public function isLoggedIn(): bool {
        return isset($_SESSION[SESSION_KEY]);
    }

    /** ログアウト処理
     * @throws SessionException ログアウト処理に失敗した場合
     */
    public function logout(): void {
        try {
            if ($this->isLoggedIn()) {
                // ユーザーのリメンバートークンをDBから削除
                $userId = $this->getCurrentUserID();
                if ($userId) {
                    $this->pdoHandler->exec(
                        "DELETE FROM session WHERE user_id = ?",
                        [$userId]
                    );
                }
                
                // リメンバートークンのクッキーを削除
                setcookie(
                    REMEMBER_COOKIE_NAME,
                    '',
                    [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
                
                $this->destroySession();
            }
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to logout: " . $e->getMessage());
            throw new SessionException('ログアウト処理に失敗しました: ' . $e->getMessage());
        }
    }

    /** 現在のログインユーザーID取得 */
    public function getCurrentUserID(): ?int {
        return $this->isLoggedIn() ? $_SESSION[SESSION_KEY] : null;
    }

    /** セッションID再生成
     * @throws SessionException セッション再生成に失敗した場合
     */
    public function regenerateSession(): void {
        try {
            if ($this->isLoggedIn()) {
                session_regenerate_id(true);
                $_SESSION['last_activity'] = time();
            }
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to regenerate session: " . $e->getMessage());
            throw new SessionException('セッション再生成に失敗しました: ' . $e->getMessage());
        }
    }
}

?>