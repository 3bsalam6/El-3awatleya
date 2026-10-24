<?php
/**
 * العواطلية - Mailer Helper (PHPMailer wrapper)
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Send an email
     */
    public static function send($to, $subject, $body)
    {
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../../config/mail.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];

            $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send OTP email
     */
    public static function sendOTP($email, $code, $name = '')
    {
        $subject = "🔐 كود التحقق - العواطلية";
        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 500px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px;'>
            <h2 style='color: #c4712b; text-align: center;'>العواطلية</h2>
            <p>أهلاً {$name}،</p>
            <p>كود التحقق بتاعك هو:</p>
            <div style='text-align: center; margin: 25px 0;'>
                <span style='font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #c4712b; background: #16213e; padding: 15px 30px; border-radius: 10px; display: inline-block;'>{$code}</span>
            </div>
            <p style='color: #888; font-size: 13px;'>الكود ده صالح لمدة " . OTP_EXPIRY_MINUTES . " دقايق فقط.</p>
            <p style='color: #888; font-size: 13px;'>لو مش إنت اللي طلبت الكود ده، اتجاهل الرسالة دي.</p>
            <hr style='border-color: #333; margin: 20px 0;'>
            <p style='color: #666; font-size: 11px; text-align: center;'>العواطلية - منصة التوظيف</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordReset($email, $code, $name = '')
    {
        $subject = "🔑 إعادة تعيين كلمة المرور - العواطلية";
        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 500px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px;'>
            <h2 style='color: #c4712b; text-align: center;'>العواطلية</h2>
            <p>أهلاً {$name}،</p>
            <p>إنت طلبت إعادة تعيين كلمة المرور. استخدم الكود ده:</p>
            <div style='text-align: center; margin: 25px 0;'>
                <span style='font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #c4712b; background: #16213e; padding: 15px 30px; border-radius: 10px; display: inline-block;'>{$code}</span>
            </div>
            <p style='color: #888; font-size: 13px;'>الكود ده صالح لمدة " . OTP_EXPIRY_MINUTES . " دقايق فقط.</p>
            <hr style='border-color: #333; margin: 20px 0;'>
            <p style='color: #666; font-size: 11px; text-align: center;'>العواطلية - منصة التوظيف</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }
}
