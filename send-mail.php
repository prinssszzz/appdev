<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/vendor/autoload.php');

function sendResetMail($toEmail, $resetLink) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($_ENV['MAIL_USERNAME'], 'appdev');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';
        $mail->Body    = "Hi,<br><br>Click here to reset your password:
                          <a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "</a><br><br>
                          This link will expire in 1 hour.";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}
