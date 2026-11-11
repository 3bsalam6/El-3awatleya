<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - دخول الأدمن">
    <title>العواطلية - دخول الأدمن</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="login-page admin-login-page">
    <div class="login-wrapper" style="justify-content: center;">

        <div class="login-form-section" style="max-width: 440px;">
            <div class="login-card admin-card">
                <div class="login-logo">
                    <img src="/public/images/logo.jpg" alt="العواطلية">
                </div>
                <h1 class="login-title" style="font-size: 1.4rem;">🔐 دخول الأدمن</h1>
                <p class="login-subtitle">الدخول محمي بكود OTP إجباري</p>

                <!-- Step 1: Credentials -->
                <div id="adminStep1">
                    <form id="adminLoginForm">
                        <div class="form-group">
                            <label for="adminEmail">البريد الإلكتروني</label>
                            <input 
                                type="email" 
                                id="adminEmail" 
                                name="email" 
                                class="form-control" 
                                placeholder="admin@el3awatleya.com"
                                required
                                dir="ltr"
                            >
                        </div>

                        <div class="form-group">
                            <label for="adminPassword">كلمة المرور</label>
                            <input 
                                type="password" 
                                id="adminPassword" 
                                name="password" 
                                class="form-control" 
                                placeholder="••••••••"
                                required
                                dir="ltr"
                            >
                        </div>

                        <div class="alert alert-danger" id="adminError" style="display: none;">
                            <span>❌</span>
                            <span id="adminErrorText"></span>
                        </div>

                        <button type="submit" class="btn btn-primary" id="adminLoginBtn">
                            🔐 تسجيل الدخول
                        </button>
                    </form>
                </div>

                <!-- Step 2: OTP (Required for Admin) -->
                <div id="adminStep2" style="display: none;">
                    <div class="alert alert-warning">
                        <span>🔒</span>
                        <span>التحقق بـ OTP مطلوب للأدمن - تم إرسال الكود</span>
                    </div>

                    <form id="adminOTPForm">
                        <div class="form-group">
                            <label>كود التحقق OTP</label>
                            <div class="otp-inputs" id="adminOTPInputs">
                                <input type="text" class="otp-digit" maxlength="1" data-index="0" autofocus dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="1" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="2" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="3" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="4" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="5" dir="ltr">
                            </div>
                            <input type="hidden" id="adminOTPFull" name="otp">
                        </div>

                        <div class="otp-validity-note" style="text-align: center; font-size: 0.83rem; color: var(--text-muted); margin-bottom: 10px;">
                            ⏱️ الكود صالح للاستخدام لمدة <?= OTP_EXPIRY_MINUTES ?> دقائق
                        </div>

                        <div class="otp-resend-area" style="text-align: center; margin-bottom: 16px;">
                            <span id="adminResendCountdownNotice" style="font-size: 0.86rem; color: var(--text-muted); display: none;">
                                إعادة إرسال الكود بعد: <strong id="adminResendTimerCountdown" style="color: var(--accent); font-variant-numeric: tabular-nums;">01:00</strong>
                            </span>
                            <button type="button" class="btn-link" id="adminResendOTP" data-resend-seconds="60" style="font-size: 0.88rem; font-weight: 600;">
                                🔄 ابعت تاني
                            </button>
                        </div>

                        <div class="alert alert-danger" id="adminOTPError" style="display: none;">
                            <span>❌</span>
                            <span id="adminOTPErrorText"></span>
                        </div>

                        <button type="submit" class="btn btn-primary" id="adminVerifyBtn">
                            🔓 تحقق وادخل
                        </button>
                    </form>
                </div>

                <div class="form-footer mt-2">
                    <a href="/login">← ارجع لتسجيل الدخول العادي</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
