<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - تعيين كلمة المرور الجديدة">
    <title>العواطلية - كلمة مرور جديدة</title>
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
                <h1 class="login-title" style="font-size: 1.4rem;">كلمة مرور جديدة</h1>
                <p class="login-subtitle">ادخل كلمة المرور الجديدة لحسابك وقم بتأكيدها</p>

                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-danger"><?= Session::flash('error') ?></div>
                <?php endif; ?>
                <?php if (Session::hasFlash('success')): ?>
                    <div class="alert alert-success"><?= Session::flash('success') ?></div>
                <?php endif; ?>

                <div class="alert alert-danger" id="resetError" style="display: none;">
                    <span>❌</span>
                    <span id="resetErrorText"></span>
                </div>

                <form method="POST" action="/reset-password" id="resetPasswordForm">
                    <div class="form-group">
                        <label for="password">كلمة المرور الجديدة</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="••••••••" 
                            required 
                            dir="ltr" 
                            minlength="6"
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            placeholder="••••••••" 
                            required 
                            dir="ltr" 
                            minlength="6"
                            autocomplete="new-password"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary" id="resetBtn">
                        تغيير كلمة المرور
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
