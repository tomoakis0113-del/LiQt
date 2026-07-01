<?php
set_time_limit(300);
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
            "content" => $systemPrompt =
                "【ルール】\n" .
                "1. あなたの名前は「ai」です。\n" .
                "2. 挨拶は不要。\n" .
                "3. 丁寧で共感するトーンを維持してください。\n\n" .
                "4. 相談内容に対して、適切なアドバイスやサポートを提供してください。\n" .
                "5. 相談内容に対して、必要に応じて質問をしてください。\n" .
                "6. 相談内容に対して、必要に応じて具体的な行動やリソースを提案してください。\n" .
                "7. 相談内容に対して、必要に応じて励ましや慰めの言葉を提供してください。\n" .
                "8. Markdown形式で出力してください。\n" .
                "【相談内容】\n" . $content
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
