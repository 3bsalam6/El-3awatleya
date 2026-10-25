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

    /**
     * Send account approval email
     */
    public static function sendAccountApproval($email, $name = '', $role = 'user')
    {
        $roleText = ($role === 'employer') ? 'صاحب عمل' : 'باحث عن عمل';
        $subject = "✅ تمت الموافقة على حسابك - العواطلية";
        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 520px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #10b981; font-size: 14px; font-weight: bold;'>منصة التوظيف وتقييم السير الذاتية</span>
            </div>
            <div style='background: #16213e; padding: 20px; border-radius: 10px; border-right: 4px solid #10b981; margin-bottom: 20px;'>
                <h3 style='color: #10b981; margin-top: 0;'>🎉 مبروك! تمت الموافقة على تفعيل حسابك</h3>
                <p style='margin: 0; line-height: 1.6;'>أهلاً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.6; margin: 10px 0;'>تمت مراجعة بياناتك بنجاح واعتماد حسابك كـ <strong>({$roleText})</strong> من قِبل إدارة منصة العواطلية.</p>
                <p style='line-height: 1.6; color: #cbd5e1; margin-bottom: 0;'>يمكنك الآن تسجيل الدخول مباشرة والاستفادة من جميع مميزات المنصة.</p>
            </div>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='http://localhost:8000/login' style='background: #c4712b; color: #fff; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; display: inline-block; box-shadow: 0 4px 12px rgba(196, 113, 43, 0.3);'>تسجيل الدخول الآن ←</a>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - رحلتك لسوق العمل تبدأ هنا</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send account disapproval email
     */
    public static function sendAccountDisapproval($email, $name = '', $role = 'user', $reason = '')
    {
        $roleText = ($role === 'employer') ? 'صاحب عمل' : 'باحث عن عمل';
        $subject = "❌ إشعار بخصوص طلب إنشاء حسابك - العواطلية";
        $reasonHtml = !empty($reason) ? "
            <div style='background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 6px; margin: 15px 0;'>
                <strong style='color: #ef4444;'>سبب الرفض:</strong>
                <p style='margin: 5px 0 0; color: #fca5a5;'>" . htmlspecialchars($reason) . "</p>
            </div>
        " : "";

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 520px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #ef4444; font-size: 14px; font-weight: bold;'>إشعار طلب الحساب</span>
            </div>
            <div style='background: #16213e; padding: 20px; border-radius: 10px; border-right: 4px solid #ef4444; margin-bottom: 20px;'>
                <h3 style='color: #ef4444; margin-top: 0;'>طلب إنشاء الحساب غير مقبول</h3>
                <p style='margin: 0; line-height: 1.6;'>أهلاً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.6; margin: 10px 0;'>نود إحاطتك بأنه بعد مراجعة بيانات تسجيل الحساب الخاص بك كـ <strong>({$roleText})</strong>، تعذر على إدارة المنصة قبول وتفعيل الحساب لمنع الحسابات الوهمية وللحفاظ على موثوقية المنصة.</p>
                {$reasonHtml}
                <p style='line-height: 1.6; color: #cbd5e1; margin-bottom: 0;'>إذا كنت تعتقد أن هذا الإجراء تم بالخطأ أو ترغب في إعادة التقديم ببيانات صحيحة، يسعدنا تواصلك مع الدعم الفني.</p>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - منصة التوظيف</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send account banned email
     */
    public static function sendAccountBanned($email, $name = '', $role = 'user', $reason = '')
    {
        $roleText = ($role === 'employer') ? 'صاحب عمل' : 'باحث عن عمل';
        $subject = "🚫 تم حظر حسابك - العواطلية";
        $reasonHtml = !empty($reason) ? "
            <div style='background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 6px; margin: 15px 0;'>
                <strong style='color: #ef4444;'>سبب الحظر:</strong>
                <p style='margin: 5px 0 0; color: #fca5a5; font-size: 14px;'>" . htmlspecialchars($reason) . "</p>
            </div>
        " : "
            <div style='background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 6px; margin: 15px 0;'>
                <p style='margin: 0; color: #fca5a5; font-size: 14px;'>لمخالفة سياسات وقواعد الاستخدام لمنصة العواطلية.</p>
            </div>
        ";

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 520px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #ef4444; font-size: 14px; font-weight: bold;'>إشعار حظر الحساب</span>
            </div>
            <div style='background: #16213e; padding: 20px; border-radius: 10px; border-right: 4px solid #ef4444; margin-bottom: 20px;'>
                <h3 style='color: #ef4444; margin-top: 0;'>تم حظر حسابك على المنصة</h3>
                <p style='margin: 0; line-height: 1.6;'>أهلاً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.6; margin: 10px 0;'>نحيطك علماً بأنه قد تم حظر حسابك كـ <strong>({$roleText})</strong> من قِبل إدارة منصة العواطلية، ولن تتمكن من تسجيل الدخول أو استخدام خدمات الموقع في الوقت الحالي.</p>
                {$reasonHtml}
                <p style='line-height: 1.6; color: #cbd5e1; margin-bottom: 0;'>إذا كنت تعتقد أن هذا الإجراء تم عن طريق الخطأ أو ترغب في الاستفسار أو تقديم التماس، يسعدنا تواصلك مع إدارة المنصة عبر البريد الإلكتروني.</p>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - منصة التوظيف</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send account unbanned email
     */
    public static function sendAccountUnbanned($email, $name = '', $role = 'user')
    {
        $roleText = ($role === 'employer') ? 'صاحب عمل' : 'باحث عن عمل';
        $subject = "✅ تم رفع الحظر عن حسابك - العواطلية";
        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 520px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #10b981; font-size: 14px; font-weight: bold;'>إشعار رفع الحظر</span>
            </div>
            <div style='background: #16213e; padding: 20px; border-radius: 10px; border-right: 4px solid #10b981; margin-bottom: 20px;'>
                <h3 style='color: #10b981; margin-top: 0;'>🎉 تم رفع الحظر وإعادة تفعيل حسابك</h3>
                <p style='margin: 0; line-height: 1.6;'>أهلاً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.6; margin: 10px 0;'>يسعدنا إبلاغك بأنه قد تمت مراجعة حسابك كـ <strong>({$roleText})</strong> وقررت إدارة منصة العواطلية رفع الحظر وإعادة تفعيل حسابك بنجاح.</p>
                <p style='line-height: 1.6; color: #cbd5e1; margin-bottom: 0;'>يمكنك الآن تسجيل الدخول مجدداً ومتابعة جميع أنشطتك على المنصة بكل سهولة.</p>
            </div>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='http://localhost:8000/login' style='background: #c4712b; color: #fff; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; display: inline-block; box-shadow: 0 4px 12px rgba(196, 113, 43, 0.3);'>تسجيل الدخول الآن ←</a>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - رحلتك لسوق العمل تبدأ هنا</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send interview invitation email to candidate
     */
    public static function sendInterviewInvitation($email, $name, $jobTitle, $companyName, $interviewDateTime, $locationType, $locationOrLink, $notes = '')
    {
        $subject = "📅 موعد وتفاصيل المقابلة الشخصية - وظيفة: {$jobTitle}";
        $typeText = ($locationType === 'online') ? '🌐 أونلاين عبر الإنترنت' : '🏢 حضورية بمقر الشركة';
        $locationLabel = ($locationType === 'online') ? 'رابط المقابلة / الاجتماع' : 'عنوان ومقر المقابلة';
        $formattedDate = date('d/m/Y - h:i A', strtotime($interviewDateTime));

        $notesHtml = !empty($notes) ? "
            <div style='background: rgba(196, 113, 43, 0.1); border-right: 4px solid #c4712b; padding: 14px; border-radius: 6px; margin: 15px 0;'>
                <strong style='color: #c4712b; font-size: 14px;'>تفاصيل وملاحظات إضافية من الشركة:</strong>
                <p style='margin: 6px 0 0; color: #eee; line-height: 1.6; font-size: 14px; white-space: pre-line;'>" . htmlspecialchars($notes) . "</p>
            </div>
        " : "";

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 560px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 24px;'>
                <h2 style='color: #c4712b; margin: 0; font-size: 24px;'>العواطلية</h2>
                <span style='color: #10b981; font-size: 14px; font-weight: bold;'>🎉 قبول مبدئي ودعوة لمقابلة عمل</span>
            </div>
            <div style='background: #16213e; padding: 22px; border-radius: 10px; border-right: 4px solid #10b981; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 16px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.7; margin: 12px 0; color: #cbd5e1;'>
                    يسرنا إبلاغك بأنه تمت مراجعة طلبك لوظيفة <strong>" . htmlspecialchars($jobTitle) . "</strong> لدى <strong>" . htmlspecialchars($companyName) . "</strong>، ويسعدنا دعوتك لحضور المقابلة الشخصية.
                </p>
                <div style='background: #0f172a; padding: 16px; border-radius: 8px; margin: 16px 0;'>
                    <p style='margin: 6px 0;'><strong>📅 موعد المقابلة:</strong> <span style='color: #f59e0b; font-weight: bold;'>{$formattedDate}</span></p>
                    <p style='margin: 6px 0;'><strong>📌 نوع المقابلة:</strong> {$typeText}</p>
                    <p style='margin: 6px 0;'><strong>📍 {$locationLabel}:</strong> <span style='color: #38bdf8; word-break: break-all;'>" . htmlspecialchars($locationOrLink) . "</span></p>
                </div>
                {$notesHtml}
                <p style='line-height: 1.6; color: #cbd5e1; font-size: 13px; margin-bottom: 0;'>
                    نتمنى لك كل التوفيق، ونرجو منك الالتزام بالموعد المحدد والتحضير الجيد للمقابلة.
                </p>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - بوابتك لفرص العمل الحقيقية</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send job deactivation notification to employer
     */
    public static function sendJobDeactivatedToEmployer($email, $name, $jobTitle, $reason = '')
    {
        $subject = "⚠️ تنبيه: تم إيقاف إعلان الوظيفة ({$jobTitle}) - العواطلية";
        $reasonHtml = !empty($reason) ? "
            <div style='background: rgba(239, 68, 68, 0.1); border-right: 4px solid #ef4444; padding: 14px; border-radius: 6px; margin: 15px 0;'>
                <strong style='color: #ef4444; font-size: 14px;'>سبب التعطيل من إدارة المنصة:</strong>
                <p style='margin: 6px 0 0; color: #fca5a5; line-height: 1.6; font-size: 14px;'>" . htmlspecialchars($reason) . "</p>
            </div>
        " : "";

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 540px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #ef4444; font-size: 14px; font-weight: bold;'>إشعار إداري بخصوص إعلان وظيفة</span>
            </div>
            <div style='background: #16213e; padding: 22px; border-radius: 10px; border-right: 4px solid #ef4444; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 15px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.7; margin: 12px 0; color: #cbd5e1;'>
                    نحيطكم علماً بأنه تم إيقاف ظهور إعلان الوظيفة <strong>(" . htmlspecialchars($jobTitle) . ")</strong> مؤقتاً بقرار من إدارة المنصة.
                </p>
                {$reasonHtml}
                <p style='line-height: 1.6; color: #cbd5e1; font-size: 13px; margin-bottom: 0;'>
                    إذا كانت لديكم أي استفسارات أو رغبة في تعديل الإعلان لإعادة نشره، يرجى التواصل مع إدارة المنصة.
                </p>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - إدارة منصة التوظيف</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send job reactivation notification to employer
     */
    public static function sendJobActivatedToEmployer($email, $name, $jobTitle)
    {
        $subject = "✅ تم إعادة تفعيل إعلان الوظيفة ({$jobTitle}) - العواطلية";
        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 540px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #10b981; font-size: 14px; font-weight: bold;'>إشعار تفعيل إعلان وظيفة</span>
            </div>
            <div style='background: #16213e; padding: 22px; border-radius: 10px; border-right: 4px solid #10b981; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 15px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.7; margin: 12px 0; color: #cbd5e1;'>
                    يسرنا إبلاغكم بأنه تمت إعادة تفعيل إعلان الوظيفة <strong>(" . htmlspecialchars($jobTitle) . ")</strong> وأصبحت متاحة للتقديم واستقبال طلبات الباحثين عن عمل على المنصة.
                </p>
                <div style='text-align: center; margin: 20px 0 10px;'>
                    <a href='http://localhost:8000/employer/dashboard' style='background: #c4712b; color: #fff; text-decoration: none; padding: 10px 24px; border-radius: 6px; font-weight: bold; display: inline-block;'>فتح لوحة التحكم ←</a>
                </div>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - إدارة منصة التوظيف</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send sensitive data change approval email
     */
    public static function sendNameChangeApproved($email, $name, $fieldName, $newValue)
    {
        $fieldLabel = ($fieldName === 'CompanyName') ? 'اسم الشركة' : 'الاسم الكامل';
        $subject = "✅ تمت الموافقة على تعديل {$fieldLabel} - العواطلية";
        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 540px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #10b981; font-size: 14px; font-weight: bold;'>إشعار موافقة على تعديل البيانات</span>
            </div>
            <div style='background: #16213e; padding: 22px; border-radius: 10px; border-right: 4px solid #10b981; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 15px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.7; margin: 12px 0; color: #cbd5e1;'>
                    نحيطك علماً بأن إدارة المنصة قد راجعت ووافقت على طلب تعديل <strong>{$fieldLabel}</strong> الخاص بحسابك.
                </p>
                <div style='background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 12px 16px; border-radius: 6px; margin: 15px 0;'>
                    <span style='color: #10b981; font-weight: bold;'>القيمة الجديدة المعتمدة:</span>
                    <strong style='color: #fff; font-size: 15px; display: block; margin-top: 4px;'>" . htmlspecialchars($newValue) . "</strong>
                </div>
                <p style='line-height: 1.6; color: #cbd5e1; font-size: 13px; margin-bottom: 0;'>
                    تم تحديث بياناتك الرسمية على المنصة بنجاح.
                </p>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - بوابتك لفرص العمل الحقيقية</p>
        </div>
        ";
        return self::send($email, $subject, $body);
    }

    /**
     * Send sensitive data change rejection email
     */
    public static function sendNameChangeRejected($email, $name, $fieldName, $reason = '')
    {
        $fieldLabel = ($fieldName === 'CompanyName') ? 'اسم الشركة' : 'الاسم الكامل';
        $subject = "❌ تم رفض طلب تعديل {$fieldLabel} - العواطلية";
        $reasonHtml = !empty($reason) ? "
            <div style='background: rgba(239, 68, 68, 0.1); border-right: 4px solid #ef4444; padding: 14px; border-radius: 6px; margin: 15px 0;'>
                <strong style='color: #ef4444; font-size: 14px;'>سبب الرفض من الإدارة:</strong>
                <p style='margin: 6px 0 0; color: #fca5a5; line-height: 1.6; font-size: 14px;'>" . htmlspecialchars($reason) . "</p>
            </div>
        " : "";

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 540px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #c4712b; margin: 0;'>العواطلية</h2>
                <span style='color: #ef4444; font-size: 14px; font-weight: bold;'>إشعار إداري بخصوص تعديل البيانات</span>
            </div>
            <div style='background: #16213e; padding: 22px; border-radius: 10px; border-right: 4px solid #ef4444; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 15px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.7; margin: 12px 0; color: #cbd5e1;'>
                    نأسف لإبلاغك بأنه تم رفض طلبك لتعديل <strong>{$fieldLabel}</strong> من قِبل إدارة الموقع، وبقيت بياناتك السابقة كما هي دون تغيير.
                </p>
                {$reasonHtml}
                <p style='line-height: 1.6; color: #cbd5e1; font-size: 13px; margin-bottom: 0;'>
                    إذا كانت لديك مستندات أو استفسارات إضافية، يرجى التواصل مع الدعم الفني للإدارة.
                </p>
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - بوابتك لفرص العمل الحقيقية</p>
        </div>
        ";
        return self::send($email, $subject, $body);
    }
}


