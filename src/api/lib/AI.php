<?php
namespace lib;
require_once __DIR__ . '/Util.php';

class AI{
    private $api_password;
    public function __construct(){
        Util::loadEnv();

        $this->api_password = $_ENV['API_PASSWORD'] ?? '';
        if (empty($this->api_password)) {
            throw new \Exception("API_PASSWORD is not set in the environment variables.");
        }
    }

    /**
     * 問題のある内容かチェックします。
     * @param string $content
     * @return bool もし問題がある場合はtrue、問題がない場合はfalse
     */
    public function word_check(String $content): bool
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

        return $response === 'true';
    }

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