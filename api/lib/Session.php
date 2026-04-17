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

    /** コンストラクタ 
     * @param PDOHandler $pdoHandler PDOハンドラーインスタンス
     * @throws Exception セッションの開始に失敗した場合
    */
    public function __construct(PDOHandler $pdoHandler) {
        $this->pdoHandler = $pdoHandler;

        // セッションが開始されていない場合開始
        if (session_status() === PHP_SESSION_NONE){
            try{
                session_start();
            } catch (\Exception $e) {
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
    */
    protected function loginWithRememberToken(string $token): void {
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
    }

    /** セッションを設定する 
     * @param int $userID ユーザーID
    */
    protected function setSession(int $userID) {
        session_regenerate_id(true); // セッションIDの再生成
        $_SESSION[SESSION_KEY] = $userID;
        $_SESSION['last_activity'] = time();

        $this->pdoHandler->exec(
            "INSERT INTO session_log (user_id, user_agent, ip) VALUES (?, ?, ?)",
            [$userID, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']]
        );
    }

    /** セッションを破棄する */
    protected function destroySession() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /** ログイン試行 */
    public function tryLogin(string $userName, string $password, bool $remember = false): bool {
        // 時間をずらしてログイン試行の頻度を下げる
        usleep(rand(500000, 3000000));

        $result = $this->pdoHandler->exec(
            "SELECT id,password FROM user WHERE name = ?",
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
    }
    
    /** リメンバートークンを設定する 
     * @param int $userId ユーザーID
    */
    protected function setRememberToken(int $userId): void {
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
    }

    /** ログイン状態確認 */
    public function isLoggedIn(): bool {
        return isset($_SESSION[SESSION_KEY]);
    }

    /** ログアウト処理 */
    public function logout(): void {
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
    }

    /** 現在のログインユーザーID取得 */
    public function getCurrentUserID(): ?int {
        return $this->isLoggedIn() ? $_SESSION[SESSION_KEY] : null;
    }

    /** 現在のログインユーザー情報取得 */
    public function getCurrentUser(): ?array {
        if ($this->isLoggedIn()) {
            $userID = $_SESSION[SESSION_KEY];
            return $this->pdoHandler->exec(
                "SELECT id,name,password FROM user WHERE id = ?",
                [$userID]
            )[0] ?? null;
        }
        return null;
    }

    /** セッションID再生成 */
    public function regenerateSession(): void {
        if ($this->isLoggedIn()) {
            session_regenerate_id(true);
            $_SESSION['last_activity'] = time();
        }
    }
}

?>