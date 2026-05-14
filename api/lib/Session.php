<?php
namespace lib;

use models\User as UserModel;
use models\Session as SessionModel;
use models\SessionLog as SessionLogModel;

use function Illuminate\Support\now;

const SESSION_KEY = 'user_id';
const SESSION_EXPIRE = 31*7*24*60*60; // 1ヶ月（秒）
const REMEMBER_COOKIE_NAME = 'remember_token';
const REMEMBER_COOKIE_EXPIRE = 60*60*24*30; // 30日（秒）

class SessionException extends \Exception {}

class Session {
    /** コンストラクタ
     * @throws Exception セッションの開始に失敗した場合
    */
    public function __construct() {
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
                $this->signout();
            else
                $_SESSION['last_activity'] = time();
        }
        // セッションがない場合、クッキートークンによる認証を試みる
        elseif (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
            $this->loginWithRememberToken($_COOKIE[REMEMBER_COOKIE_NAME]);
        }
    }
    
    /** リメンバートークンでサインイン 
     * @param string $token リメンバートークン
     * @throws SessionException サインイン処理に失敗した場合
    */
    protected function loginWithRememberToken(string $token): void {
        try {
            /** @var int|null $user_id */
            $user_id = SessionModel::query()
                        ->where('token',     '=',   $token)
                        ->where('expire',    '>',   now())
                        ->where('user_agent','=',   $_SERVER['HTTP_USER_AGENT'])
                        ->first(['user_id']);
            
            if ($user_id) {
                $this->setSession($user_id);
                $this->setRememberToken($user_id); // トークンを更新
            } else {
                setcookie(REMEMBER_COOKIE_NAME, '', time() - 3600, '/'); // 無効なトークンの場合はクッキーを削除
            }
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to login with remember token: " . $e->getMessage());
            throw new SessionException('リメンバートークンによるサインインに失敗しました: ' . $e->getMessage());
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

            SessionLogModel::query()->create([
                'user_id' => $userID,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
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
    
    /** サインイン試行
     * @param string $mailAddress メールアドレス
     * @param string $password パスワード
     * @param bool $remember サインイン状態を保持するかどうか
     * @return bool サインイン成功時true
     * @throws SessionException サインイン処理に失敗した場合
     */
    public function tryLogin(string $mailAddress, string $password, bool $remember = true): bool {
        try {
            // 時間をずらしてサインイン試行の頻度を下げる
            usleep(rand(500000, 3000000));

            $user = UserModel::query()
                    ->where('mail_address', '=', $mailAddress)
                    ->first(['id', 'password']);

            if ($user && password_verify($password, $user['password'])) {
                $this->setSession($user['id']);
                
                if ($remember)
                    $this->setRememberToken($user['id']);
                
                return true;
            }else{
                error_log("[SanaeProject] Failed login attempt for email: " . $mailAddress);
            }
            return false;
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to try login for user: " . $mailAddress . " | Error: " . $e->getMessage());
            throw new SessionException('サインイン処理に失敗しました: ' . $e->getMessage());
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
            SessionModel::query()
                ->where('user_id', $userId)
                ->delete();
            
            // 新しいトークンを保存
            SessionModel::query()->create([
                'user_id' => $userId,
                'token' => $token,
                'expire' => $expiry,
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
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

    /** サインイン状態確認 */
    public function isSignedIn(): bool {
        return isset($_SESSION[SESSION_KEY]);
    }

    /** サインアウト処理
     * @throws SessionException サインアウト処理に失敗した場合
     */
    public function signout(): void {
        try {
            if ($this->isSignedIn()) {
                // ユーザーのリメンバートークンをDBから削除
                $userId = $this->getCurrentUserID();
                if ($userId) {
                    SessionModel::query()
                        ->where('user_id', $userId)
                        ->delete();
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
            error_log("[SanaeProject] Failed to signout: " . $e->getMessage());
            throw new SessionException('サインアウト処理に失敗しました: ' . $e->getMessage());
        }
    }

    /** 現在のサインインユーザーID取得 */
    public function getCurrentUserID(): ?int {
        return $this->isSignedIn() ? $_SESSION[SESSION_KEY] : null;
    }

    /** セッションID再生成
     * @throws SessionException セッション再生成に失敗した場合
     */
    public function regenerateSession(): void {
        try {
            if ($this->isSignedIn()) {
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