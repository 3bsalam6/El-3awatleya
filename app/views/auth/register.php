<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - إنشاء حساب جديد">
    <title>العواطلية - حساب جديد</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="login-page">
    <div class="login-wrapper">
        
        <!-- Meme Section -->
        <div class="login-meme-section">
            <div class="meme-container">
                <img src="/public/images/meme.jpg" alt="ميم الطبلية">
                <div class="meme-caption">
                    "قلبت الطبلية على دماغ أخويا علشان عاوز ياكل عيش"
                </div>
            </div>
        </div>

        <!-- Register Form -->
        <div class="login-form-section">
            <div class="login-card">
                <div class="login-logo">
                    <img src="/public/images/logo.jpg" alt="العواطلية">
                </div>
                <h1 class="login-title">العواطلية</h1>
                <p class="login-subtitle">عاوز تاكل عيش؟ سجّل الأول</p>

                <form id="registerForm" action="/register" method="POST">
                    <div class="form-group">
                        <label for="name">الاسم</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control" 
                            placeholder="اكتب اسمك هنا"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="example@email.com"
                            required
                            dir="ltr"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="••••••••"
                            required
                            dir="ltr"
                            minlength="6"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">تأكيد كلمة المرور</label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            class="form-control" 
                            placeholder="••••••••"
                            required
                            dir="ltr"
                            minlength="6"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary" id="registerBtn">
                        سجّلني
                    </button>
                </form>

                <div class="form-footer mt-2">
                    عندك حساب؟ <a href="/login">ادخل من هنا</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="/public/js/app.js"></script>
</body>
</html>
