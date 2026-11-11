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
     * Format a datetime string into Arabic friendly format
     * e.g.: الأربعاء، 12 نوفمبر 2026 - الساعة 02:30 مساءً
     */
    public static function formatArabicDateTime($dateTimeStr)
    {
        $ts = strtotime($dateTimeStr);
        if (!$ts) return $dateTimeStr;

        $arabicDays = [
            'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت'
        ];
        $arabicMonths = [
            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
            '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
            '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر'
        ];

        $dayName = $arabicDays[date('l', $ts)] ?? date('l', $ts);
        $monthName = $arabicMonths[date('m', $ts)] ?? date('m', $ts);
        $dayNum = date('j', $ts);
        $yearNum = date('Y', $ts);
        $timeStr = date('g:i', $ts);
        $amPm = (date('A', $ts) === 'AM') ? 'صباحاً' : 'مساءً';

        return "{$dayName}، {$dayNum} {$monthName} {$yearNum} - الساعة {$timeStr} {$amPm}";
    }

    /**
     * Send interview invitation email to candidate (New Schedule)
     */
    public static function sendInterviewInvitation($email, $name, $jobTitle, $companyName, $interviewDateTime, $locationType, $location = '', $meetingLink = '', $notes = '')
    {
        // Handle backwards-compatibility if $meetingLink was passed as notes (8 args)
        if (func_num_args() === 8 && !empty($meetingLink) && empty($notes)) {
            $notes = $meetingLink;
            $meetingLink = ($locationType === 'online') ? $location : '';
        }

        $formattedDate = self::formatArabicDateTime($interviewDateTime);
        $subject = "📅 موعد وتفاصيل المقابلة الشخصية - وظيفة: {$jobTitle}";

        // Format location type text
        if ($locationType === 'online') {
            $typeText = '🌐 أونلاين عبر الإنترنت';
        } elseif ($locationType === 'hybrid') {
            $typeText = '🔄 هجين (حضوري وأونلاين)';
        } else {
            $typeText = '🏢 حضوري بمقر الشركة';
        }

        // Details rows
        $detailsRowsHtml = '';

        if (!empty($location) && in_array($locationType, ['onsite', 'hybrid'])) {
            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($location);
            $detailsRowsHtml .= "
                <div style='margin: 10px 0;'>
                    <strong style='color: #cbd5e1; font-size: 13px;'>📍 عنوان ومقر المقابلة:</strong>
                    <div style='color: #fff; font-size: 15px; margin-top: 4px; font-weight: 600;'>" . htmlspecialchars($location) . "</div>
                    <div style='margin-top: 6px;'>
                        <a href='{$mapsUrl}' target='_blank' style='color: #38bdf8; text-decoration: none; font-size: 12px;'>🗺️ فتح العنوان في خرائط Google ←</a>
                    </div>
                </div>
            ";
        }

        if (!empty($meetingLink) && in_array($locationType, ['online', 'hybrid'])) {
            $detailsRowsHtml .= "
                <div style='margin: 12px 0;'>
                    <strong style='color: #cbd5e1; font-size: 13px;'>🔗 رابط المقابلة أونلاين:</strong>
                    <div style='margin-top: 6px;'>
                        <a href='" . htmlspecialchars($meetingLink) . "' target='_blank' style='background: #0284c7; color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: bold; display: inline-block;'>انضم إلى المقابلة أونلاين ←</a>
                    </div>
                    <div style='color: #94a3b8; font-size: 11px; margin-top: 4px; word-break: break-all;'>" . htmlspecialchars($meetingLink) . "</div>
                </div>
            ";
        }

        $notesHtml = !empty($notes) ? "
            <div style='background: rgba(196, 113, 43, 0.1); border-right: 4px solid #c4712b; padding: 14px; border-radius: 6px; margin: 16px 0;'>
                <strong style='color: #c4712b; font-size: 14px;'>📝 تعليمات وملاحظات من الشركة:</strong>
                <p style='margin: 6px 0 0; color: #eee; line-height: 1.6; font-size: 14px; white-space: pre-line;'>" . htmlspecialchars($notes) . "</p>
            </div>
        " : "";

        // Google Calendar URL
        $ivTs = strtotime($interviewDateTime);
        $calTitle = urlencode('مقابلة عمل: ' . $jobTitle . ' - ' . $companyName);
        $calStart = gmdate('Ymd\THis\Z', $ivTs);
        $calEnd = gmdate('Ymd\THis\Z', $ivTs + 3600);
        $calLoc = urlencode(($locationType === 'online') ? ($meetingLink ?: $location) : $location);
        $calDetails = urlencode("تفاصيل المقابلة الشخصية لوظيفة: {$jobTitle}\nالشركة: {$companyName}\nالملاحظات: {$notes}");
        $calUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calTitle}&dates={$calStart}/{$calEnd}&details={$calDetails}&location={$calLoc}";

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 580px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 24px;'>
                <h2 style='color: #c4712b; margin: 0; font-size: 26px;'>العواطلية</h2>
                <span style='color: #10b981; font-size: 14px; font-weight: bold;'>🎉 قبول مبدئي ودعوة لمقابلة عمل</span>
            </div>

            <div style='background: #16213e; padding: 24px; border-radius: 10px; border-right: 4px solid #10b981; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 16px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.7; margin: 12px 0; color: #cbd5e1;'>
                    يسرنا إبلاغك بأنه تمت مراجعة طلبك لوظيفة <strong>" . htmlspecialchars($jobTitle) . "</strong> لدى شركة <strong>" . htmlspecialchars($companyName) . "</strong>، ويسعدنا دعوتك لحضور المقابلة الشخصية.
                </p>

                <div style='background: #0f172a; padding: 18px; border-radius: 8px; margin: 18px 0; border: 1px solid #1e293b;'>
                    <div style='margin-bottom: 12px;'>
                        <strong style='color: #cbd5e1; font-size: 13px;'>📅 موعد المقابلة:</strong>
                        <div style='color: #f59e0b; font-size: 17px; font-weight: bold; margin-top: 4px;'>{$formattedDate}</div>
                    </div>

                    <div style='margin-bottom: 12px;'>
                        <strong style='color: #cbd5e1; font-size: 13px;'>📌 نوع المقابلة:</strong>
                        <span style='color: #fff; font-size: 14px; font-weight: 600; margin-right: 6px;'>{$typeText}</span>
                    </div>

                    {$detailsRowsHtml}
                </div>

                {$notesHtml}

                <div style='margin: 20px 0 10px;'>
                    <a href='{$calUrl}' target='_blank' style='background: #4a8c5c; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: bold; display: inline-block;'>📅 إضافة للتقويم (Google Calendar)</a>
                </div>

                <p style='line-height: 1.6; color: #94a3b8; font-size: 13px; margin: 16px 0 0;'>
                    💡 <strong>تنويه:</strong> يرجى الالتزام بالموعد المحدد والتحضير الجيد، والتواجد أو الانضمام للاجتماع قبل الموعد بـ 10 دقائق. نتمنى لك كل التوفيق والنجاح!
                </p>
            </div>

            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - بوابتك لفرص العمل الحقيقية</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send interview updated / edited email to candidate
     */
    public static function sendInterviewUpdated($email, $name, $jobTitle, $companyName, $newDateTime, $locationType, $location = '', $meetingLink = '', $notes = '', $oldDateTime = null)
    {
        $newFormattedDate = self::formatArabicDateTime($newDateTime);
        $subject = "🔄 تحديث موعد وتفاصيل المقابلة الشخصية - وظيفة: {$jobTitle}";

        // Format location type text
        if ($locationType === 'online') {
            $typeText = '🌐 أونلاين عبر الإنترنت';
        } elseif ($locationType === 'hybrid') {
            $typeText = '🔄 هجين (حضوري وأونلاين)';
        } else {
            $typeText = '🏢 حضوري بمقر الشركة';
        }

        // Details rows
        $detailsRowsHtml = '';

        if (!empty($location) && in_array($locationType, ['onsite', 'hybrid'])) {
            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($location);
            $detailsRowsHtml .= "
                <div style='margin: 10px 0;'>
                    <strong style='color: #cbd5e1; font-size: 13px;'>📍 عنوان ومقر المقابلة:</strong>
                    <div style='color: #fff; font-size: 15px; margin-top: 4px; font-weight: 600;'>" . htmlspecialchars($location) . "</div>
                    <div style='margin-top: 6px;'>
                        <a href='{$mapsUrl}' target='_blank' style='color: #38bdf8; text-decoration: none; font-size: 12px;'>🗺️ فتح العنوان في خرائط Google ←</a>
                    </div>
                </div>
            ";
        }

        if (!empty($meetingLink) && in_array($locationType, ['online', 'hybrid'])) {
            $detailsRowsHtml .= "
                <div style='margin: 12px 0;'>
                    <strong style='color: #cbd5e1; font-size: 13px;'>🔗 رابط المقابلة أونلاين الجديد:</strong>
                    <div style='margin-top: 6px;'>
                        <a href='" . htmlspecialchars($meetingLink) . "' target='_blank' style='background: #0284c7; color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: bold; display: inline-block;'>انضم إلى المقابلة أونلاين ←</a>
                    </div>
                    <div style='color: #94a3b8; font-size: 11px; margin-top: 4px; word-break: break-all;'>" . htmlspecialchars($meetingLink) . "</div>
                </div>
            ";
        }

        // Comparison of dates
        $dateComparisonHtml = "";
        if (!empty($oldDateTime) && strtotime($oldDateTime) !== strtotime($newDateTime)) {
            $oldFormattedDate = self::formatArabicDateTime($oldDateTime);
            $dateComparisonHtml = "
                <div style='background: rgba(239, 68, 68, 0.1); border: 1px dashed rgba(239, 68, 68, 0.4); padding: 10px 14px; border-radius: 6px; margin-bottom: 12px;'>
                    <span style='color: #fca5a5; font-size: 12px; display: block;'>❌ الموعد السابق (تم إلغاؤه وتعديله):</span>
                    <span style='color: #94a3b8; text-decoration: line-through; font-size: 14px;'>{$oldFormattedDate}</span>
                </div>
            ";
        }

        $notesHtml = !empty($notes) ? "
            <div style='background: rgba(245, 158, 11, 0.1); border-right: 4px solid #f59e0b; padding: 14px; border-radius: 6px; margin: 16px 0;'>
                <strong style='color: #f59e0b; font-size: 14px;'>📝 تعليمات وملاحظات الشركة المحدثة:</strong>
                <p style='margin: 6px 0 0; color: #eee; line-height: 1.6; font-size: 14px; white-space: pre-line;'>" . htmlspecialchars($notes) . "</p>
            </div>
        " : "";

        // Google Calendar URL
        $ivTs = strtotime($newDateTime);
        $calTitle = urlencode('مقابلة عمل (موعد محدث): ' . $jobTitle . ' - ' . $companyName);
        $calStart = gmdate('Ymd\THis\Z', $ivTs);
        $calEnd = gmdate('Ymd\THis\Z', $ivTs + 3600);
        $calLoc = urlencode(($locationType === 'online') ? ($meetingLink ?: $location) : $location);
        $calDetails = urlencode("تفاصيل المقابلة الشخصية المحدثة لوظيفة: {$jobTitle}\nالشركة: {$companyName}\nالملاحظات: {$notes}");
        $calUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calTitle}&dates={$calStart}/{$calEnd}&details={$calDetails}&location={$calLoc}";

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 580px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 24px;'>
                <h2 style='color: #c4712b; margin: 0; font-size: 26px;'>العواطلية</h2>
                <span style='color: #f59e0b; font-size: 14px; font-weight: bold;'>🔄 إشعار تعديل موعد المقابلة الشخصية</span>
            </div>

            <div style='background: #16213e; padding: 24px; border-radius: 10px; border-right: 4px solid #f59e0b; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 16px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                <p style='line-height: 1.7; margin: 12px 0; color: #cbd5e1;'>
                    نود إحاطتك علماً بأن شركة <strong>" . htmlspecialchars($companyName) . "</strong> قد قامت <strong>بتعديل تفاصيل المقابلة الشخصية</strong> الخاصة بطلبك لوظيفة <strong>" . htmlspecialchars($jobTitle) . "</strong>.
                </p>

                <div style='background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); padding: 12px 16px; border-radius: 8px; margin: 16px 0; color: #fef3c7; font-size: 13.5px;'>
                    ⚠️ <strong>تنبيه هام:</strong> يرجى اعتماد الموعد والتفاصيل الجديدة الموضحة أدناه والتأكد من تواجدك في الموعد الجديد.
                </div>

                <div style='background: #0f172a; padding: 18px; border-radius: 8px; margin: 18px 0; border: 1px solid #1e293b;'>
                    {$dateComparisonHtml}

                    <div style='margin-bottom: 12px;'>
                        <strong style='color: #cbd5e1; font-size: 13px;'>📅 الموعد الجديد المعتمد:</strong>
                        <div style='color: #10b981; font-size: 18px; font-weight: bold; margin-top: 4px;'>{$newFormattedDate}</div>
                    </div>

                    <div style='margin-bottom: 12px;'>
                        <strong style='color: #cbd5e1; font-size: 13px;'>📌 نوع المقابلة:</strong>
                        <span style='color: #fff; font-size: 14px; font-weight: 600; margin-right: 6px;'>{$typeText}</span>
                    </div>

                    {$detailsRowsHtml}
                </div>

                {$notesHtml}

                <div style='margin: 20px 0 10px;'>
                    <a href='{$calUrl}' target='_blank' style='background: #f59e0b; color: #000; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: bold; display: inline-block;'>📅 تحديث التقويم (Google Calendar)</a>
                </div>

                <p style='line-height: 1.6; color: #94a3b8; font-size: 13px; margin: 16px 0 0;'>
                    نتمنى لك كل التوفيق والنجاح. إذا كانت لديك أي استفسارات أو تعذر الحضور في الموعد الجديد، يرجى التواصل مع الشركة.
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
            </div>
            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - إدارة منصة التوظيف</p>
        </div>
        ";

        return self::send($email, $subject, $body);
    }

    /**
     * Send application status notification to candidate (acceptance, rejection, shortlisting, review)
     */
    public static function sendApplicationStatusNotification($email, $name, $jobTitle, $companyName, $status, $notes = '')
    {
        $statusConfig = [
            'accepted' => [
                'subject'      => "🎉 تهانينا! تم قبول طلب توظيفك لوظيفة ({$jobTitle}) - العواطلية",
                'title'        => "تهانينا! تم قبول طلبك",
                'badge'        => "✅ تم القبول (Accepted)",
                'badge_bg'     => "rgba(16, 185, 129, 0.2)",
                'badge_color'  => "#10b981",
                'border_color' => "#10b981",
                'message'      => "يسعدنا إبلاغك بأن طلبك لوظيفة <strong>" . htmlspecialchars($jobTitle) . "</strong> لدى شركة <strong>" . htmlspecialchars($companyName) . "</strong> قد تم <strong>قبوله بنجاح</strong>! سيتواصل معك فريق التوظيف بالشركة قريباً لاستكمال الخطوات القادمة.",
            ],
            'shortlisted' => [
                'subject'      => "⭐ تم اختيارك في القائمة المختصرة لوظيفة ({$jobTitle}) - العواطلية",
                'title'        => "تم ترشيحك للقائمة المختصرة",
                'badge'        => "⭐ القائمة المختصرة (Shortlisted)",
                'badge_bg'     => "rgba(245, 158, 11, 0.2)",
                'badge_color'  => "#f59e0b",
                'border_color' => "#f59e0b",
                'message'      => "نود إعلامك بأن طلبك لوظيفة <strong>" . htmlspecialchars($jobTitle) . "</strong> لدى شركة <strong>" . htmlspecialchars($companyName) . "</strong> قد لفت انتباه فريق التوظيف وتم <strong>إدراجه في القائمة المختصرة للمرشحين</strong> لمتابعة باقي مراحل التقييم.",
            ],
            'rejected' => [
                'subject'      => "تحديث بخصوص طلب توظيفك لوظيفة ({$jobTitle}) - العواطلية",
                'title'        => "تحديث بخصوص طلب التوظيف",
                'badge'        => "❌ لم يتم الاختيار",
                'badge_bg'     => "rgba(239, 68, 68, 0.2)",
                'badge_color'  => "#ef4444",
                'border_color' => "#ef4444",
                'message'      => "نشكرك جزيل الشكر على وقتك واهتمامك بالتقديم لوظيفة <strong>" . htmlspecialchars($jobTitle) . "</strong> لدى شركة <strong>" . htmlspecialchars($companyName) . "</strong>. نأسف لإبلاغك بأنه لم يقع الاختيار على طلبك لهذه الوظيفة في هذه المرحلة، ونتمنى لك دوام التوفيق والنجاح في الفرص الوظيفية الأخرى المتاحة على المنصة.",
            ],
            'reviewed' => [
                'subject'      => "👀 تمت مراجعة طلبك لوظيفة ({$jobTitle}) - العواطلية",
                'title'        => "تمت مراجعة طلبك",
                'badge'        => "🔍 تمت المراجعة (Reviewed)",
                'badge_bg'     => "rgba(59, 130, 246, 0.2)",
                'badge_color'  => "#3b82f6",
                'border_color' => "#3b82f6",
                'message'      => "نحيطك علماً بأن شركة <strong>" . htmlspecialchars($companyName) . "</strong> قامت <strong>بمراجعة طلبك وملفك الشخصي</strong> لوظيفة <strong>" . htmlspecialchars($jobTitle) . "</strong> وسيتم إشعارك بأي تحديثات قادمة.",
            ],
        ];

        if (!isset($statusConfig[$status])) {
            return false;
        }

        $cfg = $statusConfig[$status];
        $notesHtml = !empty($notes) ? "
            <div style='background: rgba(255, 255, 255, 0.05); border-right: 4px solid {$cfg['border_color']}; padding: 12px 16px; border-radius: 6px; margin: 15px 0;'>
                <strong style='color: #cbd5e1; font-size: 13px;'>ملاحظات الشركة:</strong>
                <p style='margin: 6px 0 0; color: #fff; line-height: 1.6; font-size: 14px;'>" . htmlspecialchars($notes) . "</p>
            </div>
        " : "";

        $appUrl = defined('APP_URL') ? APP_URL : 'http://localhost:8000';

        $body = "
        <div dir='rtl' style='font-family: Cairo, Tahoma, sans-serif; max-width: 550px; margin: 0 auto; padding: 30px; background: #1a1a2e; color: #eee; border-radius: 12px; border: 1px solid #2d3748;'>
            <div style='text-align: center; margin-bottom: 22px;'>
                <h2 style='color: #c4712b; margin: 0; font-size: 26px;'>العواطلية</h2>
                <span style='color: {$cfg['border_color']}; font-size: 14px; font-weight: bold;'>{$cfg['title']}</span>
            </div>

            <div style='background: #16213e; padding: 24px; border-radius: 10px; border-right: 4px solid {$cfg['border_color']}; margin-bottom: 20px;'>
                <p style='margin: 0; font-size: 16px;'>مرحباً <strong>" . htmlspecialchars($name) . "</strong>،</p>
                
                <div style='text-align: center; margin: 18px 0;'>
                    <span style='background: {$cfg['badge_bg']}; color: {$cfg['badge_color']}; border: 1px solid {$cfg['border_color']}; padding: 8px 18px; border-radius: 20px; font-size: 14px; font-weight: bold; display: inline-block;'>
                        {$cfg['badge']}
                    </span>
                </div>

                <div style='background: #0f172a; padding: 16px; border-radius: 8px; margin: 16px 0; border: 1px solid #1e293b;'>
                    <div style='margin-bottom: 8px;'>
                        <span style='color: #94a3b8; font-size: 12px;'>الوظيفة:</span>
                        <div style='color: #fff; font-size: 15px; font-weight: bold;'>" . htmlspecialchars($jobTitle) . "</div>
                    </div>
                    <div>
                        <span style='color: #94a3b8; font-size: 12px;'>الشركة المعلنة:</span>
                        <div style='color: #cbd5e1; font-size: 14px; font-weight: 600;'>" . htmlspecialchars($companyName) . "</div>
                    </div>
                </div>

                <p style='line-height: 1.8; margin: 16px 0; color: #cbd5e1; font-size: 14.5px;'>
                    {$cfg['message']}
                </p>

                {$notesHtml}

                <div style='margin-top: 22px; text-align: center;'>
                    <a href='{$appUrl}/my-applications' target='_blank' style='background: #c4712b; color: #fff; text-decoration: none; padding: 10px 22px; border-radius: 6px; font-size: 13.5px; font-weight: bold; display: inline-block;'>
                        عرض طلباتي على المنصة ←
                    </a>
                </div>
            </div>

            <hr style='border-color: #333; margin: 25px 0;'>
            <p style='color: #888; font-size: 12px; text-align: center; margin: 0;'>العواطلية - بوابتك لفرص العمل الحقيقية</p>
        </div>
        ";

        return self::send($email, $cfg['subject'], $body);
    }

    /**
     * Send sensitive data change approval email
     */
    public static function sendNameChangeApproved($email, $name, $fieldName, $newValue)
    {
        $fieldLabels = [
            'CompanyName' => 'اسم الشركة',
            'FullName'    => 'الاسم الكامل',
            'Email'       => 'البريد الإلكتروني',
            'Phone'       => 'رقم الموبايل',
        ];
        $fieldLabel = $fieldLabels[$fieldName] ?? $fieldName;
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
        $fieldLabels = [
            'CompanyName' => 'اسم الشركة',
            'FullName'    => 'الاسم الكامل',
            'Email'       => 'البريد الإلكتروني',
            'Phone'       => 'رقم الموبايل',
        ];
        $fieldLabel = $fieldLabels[$fieldName] ?? $fieldName;
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


