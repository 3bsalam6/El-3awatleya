<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - التحقق بكود OTP">
    <title>العواطلية - تحقق OTP</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="login-page">
    <div class="login-wrapper" style="justify-content: center;">

        <div class="login-form-section" style="max-width: 440px;">
            <div class="login-card text-center">
                <div class="login-logo">
                    <img src="/public/images/logo.jpg" alt="العواطلية">
                </div>
                <h1 class="login-title" style="font-size: 1.4rem;">التحقق بكود OTP</h1>
                <p class="login-subtitle">ادخل الكود المكون من 6 أرقام المرسل إلى <?= !empty($userEmail) ? '<strong dir="ltr">' . htmlspecialchars($userEmail) . '</strong>' : 'بريدك الإلكتروني' ?></p>

                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-danger"><?= Session::flash('error') ?></div>
                <?php endif; ?>
                <?php if (Session::hasFlash('success')): ?>
                    <div class="alert alert-success"><?= Session::flash('success') ?></div>
                <?php endif; ?>

                <?php 
                $resendSeconds = Session::getOTPResendSecondsLeft();
                ?>

                <form id="otpLoginForm" method="POST" action="/otp">
                    <div class="form-group">
                        <div class="otp-inputs" id="otpInputs" dir="ltr">
                            <input type="text" class="otp-digit" maxlength="1" data-index="0" autofocus dir="ltr" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                            <input type="text" class="otp-digit" maxlength="1" data-index="1" dir="ltr" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                            <input type="text" class="otp-digit" maxlength="1" data-index="2" dir="ltr" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                            <input type="text" class="otp-digit" maxlength="1" data-index="3" dir="ltr" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                            <input type="text" class="otp-digit" maxlength="1" data-index="4" dir="ltr" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                            <input type="text" class="otp-digit" maxlength="1" data-index="5" dir="ltr" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                        </div>
                        <input type="hidden" id="otpFull" name="otp" value="">
                    </div>

                    <div class="otp-validity-note" style="text-align: center; font-size: 0.83rem; color: var(--text-muted); margin-bottom: 10px;">
                        ⏱️ الكود صالح للاستخدام لمدة <?= OTP_EXPIRY_MINUTES ?> دقائق
                    </div>

                    <div class="otp-resend-area" style="text-align: center; margin-bottom: 16px;">
                        <span id="resendCountdownNotice" style="font-size: 0.86rem; color: var(--text-muted); <?= ($resendSeconds <= 0) ? 'display: none;' : '' ?>">
                            إعادة إرسال الكود بعد: <strong id="resendTimerCountdown" style="color: var(--accent); font-variant-numeric: tabular-nums;"><?= sprintf('%02d:%02d', floor($resendSeconds / 60), $resendSeconds % 60) ?></strong>
                        </span>
                        <button type="button" class="btn-link" id="resendOTP" data-resend-seconds="<?= $resendSeconds ?>" style="font-size: 0.88rem; font-weight: 600; <?= ($resendSeconds > 0) ? 'display: none;' : '' ?>">
                            🔄 ابعت تاني
                        </button>
                    </div>

                    <div class="otp-attempts" id="otpAttempts">
                        محاولات متبقية: <strong id="attemptsLeft"><?= max(0, OTP_MAX_ATTEMPTS - ($otp['attempts'] ?? 0)) ?></strong>
                    </div>

                    <div class="alert alert-danger" id="otpError" style="display: none;">
                        <span>❌</span>
                        <span id="otpErrorText"></span>
                    </div>

                    <button type="submit" class="btn btn-primary" id="verifyBtn">
                        تحقق وادخل
                    </button>
                </form>

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
