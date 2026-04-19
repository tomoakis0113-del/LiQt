<?php
namespace lib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use lib\Util;

class SendMail{
    public static function send($to, $subject, $body): bool {
        $mail = new PHPMailer(true);
        Util::loadEnv();
        
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USER'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_USER'], 'SanaeProject');
            $mail->addAddress($to, 'Recipient Name');

            // **エンコーディング設定**
            $mail->CharSet  = 'UTF-8';  // メールのエンコーディングをUTF-8に設定
            $mail->Encoding = 'base64'; // MIMEエンコーディングを指定

            // メール件名と本文
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(true); // プレーンテキストメール（HTMLの場合はtrueに変更）

            $mail->send();

            return true;
        } catch (\Exception $e) {
            error_log("メールの送信に失敗しました: " . $mail->ErrorInfo);
            return false;
        }
    }
}
?>
