<?php
namespace TnpPortal;
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Mailer {
    private static $logger;

    private static function getLogger() {
        if (!self::$logger) {
            self::$logger = new Logger('mailer');
            self::$logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/auth.log', Logger::ERROR));
        }
        return self::$logger;
    }

    public static function sendOtp($recipient, $otp) {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            self::getLogger()->error("Invalid recipient email: $recipient");
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'harikrishan99816@gmail.com';
            $mail->Password = '40coc901';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('harikrishan99816@gmail.com', 'TNP Cell');
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = 'Your TNP Cell Login OTP';
            $mail->Body = "
                <h2>TNP Cell Login OTP</h2>
                <p>Your One-Time Password (OTP) is: <strong>$otp</strong></p>
                <p>Valid for 5 minutes. Do not share.</p>
            ";
            $mail->AltBody = "Your TNP Cell Login OTP is: $otp\nValid for 5 minutes.";
            $mail->send();
            self::getLogger()->info("OTP email sent to $recipient");
            return true;
        } catch (Exception $e) {
            self::getLogger()->error("Failed to send OTP to $recipient: " . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendNotification($recipient, $message) {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            self::getLogger()->error("Invalid recipient email: $recipient");
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];
            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = 'TNP Cell Notification';
            $mail->Body = "<p>$message</p>";
            $mail->AltBody = $message;
            $mail->send();
            self::getLogger()->info("Notification sent to $recipient: $message");
            return true;
        } catch (Exception $e) {
            self::getLogger()->error("Failed to send notification to $recipient: " . $mail->ErrorInfo);
            return false;
        }
    }
}
?>