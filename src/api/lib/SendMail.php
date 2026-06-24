<?php
namespace lib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use lib\Util;

class SendMailException extends \Exception {}

class SendMail{
    /**
     * メールを送信する
     * 
     * @param string $to 送信先メールアドレス
     * @param string $subject メール件名
     * @param string $body メール本文
     * @return bool 送信成功時true
     * @throws SendMailException メール送信に失敗した場合
     */
    public static function send($to, $subject, $body): bool {
        Util::loadEnv();
        if (empty($_ENV['MAIL_API_URL']) || empty($_ENV['MAIL_API_PASSWORD'])) {
            return false; // メールAPIのURLまたはパスワードが設定されていない場合、falseを返す
        }
        $curl = curl_init($_ENV['MAIL_API_URL'] ?? '');
    
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'target' => $to,
            'title' => $subject,
            'content' => $body,
            'password' => $_ENV['MAIL_API_PASSWORD'] ?? ''
        ]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        
        if ($response === false) {
            return false;
        }
        $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        error_log("[SanaeProject] Failed to send mail via API: " . curl_error($curl));
        return $statusCode === 200;

        /*
        $mail = new PHPMailer(true);
        
        try {
            Util::loadEnv();
            
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
            error_log("[SanaeProject] Failed to send mail: " . $e->getMessage() . " | ErrorInfo: " . $mail->ErrorInfo);
            return false;
        }
        */
    }
}
?>
