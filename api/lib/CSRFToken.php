<?php
namespace lib;

class CSRFToken{
    /** @var string CSRFトークン */
    private $token;

    /** コンストラクタ */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        
        // セッションにトークンがない場合は生成
        if (!isset($_SESSION['csrf_token']))
            $this->generateToken();
        else
            $this->token = $_SESSION['csrf_token'];
    }

    /** CSRFトークン生成 */
    private function generateToken(): void {
        $this->token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $this->token;
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