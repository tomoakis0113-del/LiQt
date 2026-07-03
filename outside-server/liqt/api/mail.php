<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();

$target = $_POST['target'] ?? '';
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$in_password = $_POST['password'] ?? '';

if(empty($target) || empty($title) || empty($content) || empty($in_password)) {
    exit(http_response_code(402));
}
if($in_password !== $_ENV['API_PASSWORD']) {
    exit(http_response_code(401));
}

$mail = new PHPMailer(true);

if(empty($_ENV['MAIL_HOST']) || empty($_ENV['MAIL_USER']) || empty($_ENV['MAIL_PASSWORD']) || empty($_ENV['MAIL_PORT'])) {
    error_log("Mail configuration is not set properly in the environment variables.");
    exit(http_response_code(500));
}

try {
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USER'];
    $mail->Password = $_ENV['MAIL_PASSWORD'];

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['MAIL_PORT'];

    $mail->setFrom($_ENV['MAIL_USER'], 'SanaeProject');
    $mail->addAddress($target, 'Recipient Name');

    // **エンコーディング設定**
    $mail->CharSet  = 'UTF-8';  // メールのエンコーディングをUTF-8に設定
    $mail->Encoding = 'base64'; // MIMEエンコーディングを指定

    // メール件名と本文
    $mail->Subject = $title;
    $mail->Body = $content;
    $mail->isHTML(true);
    $status = $mail->send();

    if (!$status) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        exit(http_response_code(401));
    }
    exit(http_response_code(200));
} catch (\Exception $e) {
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    exit(http_response_code(500));
}