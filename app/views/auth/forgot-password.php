<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - استعادة كلمة المرور">
    <title>العواطلية - نسيت الباسورد</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="login-page">
    <div class="login-wrapper" style="justify-content: center;">

        <div class="login-form-section" style="max-width: 440px;">
            <div class="login-card">
                <div class="login-logo">
                    <img src="/public/images/logo.jpg" alt="العواطلية">
                </div>
                <h1 class="login-title" style="font-size: 1.4rem;">نسيت الباسورد؟</h1>
                <p class="login-subtitle">ادخل الإيميل بتاعك وهنبعتلك كود OTP</p>

                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-danger"><?= Session::flash('error') ?></div>
                <?php endif; ?>
                <?php if (Session::hasFlash('success')): ?>
                    <div class="alert alert-success"><?= Session::flash('success') ?></div>
                <?php endif; ?>

                <!-- Step 1: Enter Email -->
                <div id="forgotStep1">
                    <form id="forgotForm" method="POST" action="/forgot-password">
                        <div class="form-group">
                            <label for="forgotEmail">البريد الإلكتروني</label>
                            <input 
                                type="email" 
                                id="forgotEmail" 
                                name="email" 
                                class="form-control" 
                                placeholder="example@email.com"
                                required
                                dir="ltr"
                            >
                        </div>

                        <div class="alert alert-info" id="forgotInfo" style="display: none;">
                            <span>ℹ️</span>
                            <span id="forgotInfoText"></span>
                        </div>
                        <div class="alert alert-danger" id="forgotError" style="display: none;">
                            <span>❌</span>
                            <span id="forgotErrorText"></span>
                        </div>

                        <button type="submit" class="btn btn-primary" id="forgotBtn">
                            ابعتلي كود OTP
                        </button>
                    </form>
                </div>

                <!-- Step 2: Enter OTP (shown after email verification) -->
                <div id="forgotStep2" style="display: none;">
                    <div class="alert alert-success">
                        <span>✅</span>
                        <span>تم إرسال كود OTP على الإيميل بتاعك</span>
                    </div>

                    <form id="otpVerifyForm">
                        <div class="form-group">
                            <label for="otpCode">كود التحقق OTP</label>
                            <div class="otp-inputs" id="otpInputs">
                                <input type="text" class="otp-digit" maxlength="1" data-index="0" autofocus dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="1" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="2" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="3" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="4" dir="ltr">
                                <input type="text" class="otp-digit" maxlength="1" data-index="5" dir="ltr">
                            </div>
                            <input type="hidden" id="otpFull" name="otp">
                        </div>

                        <div class="otp-timer">
                            <span id="otpTimer">02:00</span>
                            <button type="button" class="btn-link" id="resendOTP" disabled>ابعت تاني</button>
                        </div>

                        <div class="alert alert-danger" id="otpError" style="display: none;">
                            <span>❌</span>
                            <span id="otpErrorText"></span>
                        </div>

                        <button type="submit" class="btn btn-primary" id="verifyOTPBtn">
                            تحقق من الكود
                        </button>
                    </form>
                </div>

                <!-- Step 3: Reset Password -->
                <div id="forgotStep3" style="display: none;">
                    <div class="alert alert-success">
                        <span>✅</span>
                        <span>تم التحقق! اكتب الباسورد الجديد</span>
                    </div>

                    <form id="resetPasswordForm">
                        <div class="form-group">
                            <label for="newPassword">كلمة المرور الجديدة</label>
                            <input 
                                type="password" 
                                id="newPassword" 
                                name="new_password" 
                                class="form-control" 
                                placeholder="••••••••"
                                required
                                dir="ltr"
                                minlength="6"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirmNewPassword">تأكيد كلمة المرور</label>
                            <input 
                                type="password" 
                                id="confirmNewPassword" 
                                name="confirm_password" 
                                class="form-control" 
                                placeholder="••••••••"
                                required
                                dir="ltr"
                                minlength="6"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary" id="resetPassBtn">
                            غيّر الباسورد
                        </button>
                    </form>
                </div>

                <div class="form-footer mt-2">
                    <a href="/login">← ارجع لتسجيل الدخول</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
