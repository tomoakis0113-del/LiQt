<?php
namespace lib;

class CSRFTokenException extends \Exception {}

class CSRFToken{
    /** @var string CSRFトークン */
    private $token;

    /** コンストラクタ */
    public function __construct() {
        try {
            if (session_status() === PHP_SESSION_NONE)
                session_start();
            
            // セッションにトokenがない場合は生成
            if (!isset($_SESSION['csrf_token']))
                $this->generateToken();
            else
                $this->token = $_SESSION['csrf_token'];
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to initialize CSRF token: " . $e->getMessage());
            throw new CSRFTokenException('CSRFトークンの初期化に失敗しました: ' . $e->getMessage());
        }
    }

    /** CSRFトークン生成 */
    private function generateToken(): void {
        try {
            $this->token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $this->token;
        } catch (\Exception $e) {
            error_log("[SanaeProject] Failed to generate CSRF token: " . $e->getMessage());
            throw new CSRFTokenException('CSRFトークンの生成に失敗しました: ' . $e->getMessage());
        }
    }

    /** CSRFトークン取得 */
    public function getToken(): string {
        return $this->token;
    }

    /** CSRFトークン検証 */
    public function isValid(string $token): bool {
        if (empty($token) || !isset($_SESSION['csrf_token']))
            return false;
        
        return hash_equals($this->token, $token);
    }
}
?>