<?php
namespace lib;
require_once __DIR__ . '/../../vendor/autoload.php';

class AI{
    private $api_password;
    private $ngwords;

    public function __construct(){
        Util::loadEnv();
        $wordsfile = trim(file_get_contents(__DIR__ . "/ngwords.csv"));
        $this->ngwords = str_getcsv($wordsfile);

        $this->api_password = $_ENV['API_PASSWORD'] ?? '';
        if (empty($this->api_password)) {
            throw new \Exception("API_PASSWORD is not set in the environment variables.");
        }
    }

    /**
     * AIのユーザーIDを取得します。
     * @return string AIのユーザーID
     */
    public function getId(): string
    {
        $user = \models\User::query()->where('user_id', 'AI')->firstOrFail();
        return $user->id;
    }

    private function word_check_local(String $content): bool
    {
        foreach ($this->ngwords as $word) {
            if (stripos($content, $word) !== false) {
                error_log("[SanaeProject] Detected prohibited word: " . $word . " in content: " . $content);
                return true;
            }
        }
        return false;
    }
    private function word_check_api(String $content): bool
    {
        $url = "https://api.sanae.tech/check.php";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'password' => $this->api_password,
            'content' => $content
        ]));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response !== 'false';
    }

    /**
     * 問題のある内容かチェックします。
     * @param string $content
     * @return bool もし問題がある場合はtrue、問題がない場合はfalse
     */
    public function word_check(String $content): bool
    {
        if($this->word_check_local($content)){
            error_log("[SanaeProject] Detected prohibited content locally: " . $content);
            return true;
        }

        return $this->word_check_api($content);
    }

    /**
     * AIにチャットを送信し、応答を取得します。
     * @param string $content
     * @return string AIの応答
     */
    public function chat(String $content): string
    {
        $url = "https://api.sanae.tech/chat.php";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'password' => $this->api_password,
            'content' => $content
        ]));

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response['content'] ?? '';
    }
}