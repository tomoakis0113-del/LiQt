<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();

$content = $_POST['content'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($content)) {
    http_response_code(400);
    exit("Content is required.");
}

if ($password !== $_ENV['API_PASSWORD']) {
    http_response_code(401);
    exit("Unauthorized.");
}

$url = "http://ollama:3141/v1/chat/completions"; 
$data = [
    "model" => "gemma3n:e2b",
    "messages" => [
        [
            "role" => "system",
            "content" => "あなたは優秀なカウンセラーです。入力されたテキストに対して、適切なアドバイスやサポートを提供してください。"
        ],
        [
            "role" => "user",
            "content" => "常に日本語で返してください。\n" .
                         "下記の内容に対して、適切なアドバイスやサポートを提供してください。\n" .
                         "相談相手を傷つけるような内容は必ず避けてください。\n" .
                         "相手を尊重する姿勢を持ち続けてください。\n" .
                         "回答は丁寧で、共感を示すものにしてください。\n" .
                         "回答はMarkDownを用いてください。\n" .
                         "最大100文字以内で回答してください。\n" .
                         "--- 対象テキスト ---\n" . $content
        ]
    ],
    "temperature" => 0.0
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    exit('API Error: ' . curl_error($ch));
}

curl_close($ch);

$result = json_decode($response, true);
echo json_encode(["content" => $result['choices'][0]['message']['content'] ?? '']);
