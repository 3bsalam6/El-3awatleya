<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - قيّم سيرتك الذاتية واعرف جاهزيتك لسوق العمل">
    <title>العواطلية - تسجيل الدخول</title>
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

        <!-- Login Form -->
        <div class="login-form-section">
            <div class="login-card">
                <div class="login-logo">
                    <img src="/public/images/logo.jpg" alt="العواطلية">
                </div>
                <h1 class="login-title">العواطلية</h1>
                <p class="login-subtitle">عاوز تاكل عيش؟ يلا نشوف الـ CV بتاعك</p>

                <form id="loginForm" action="/login" method="POST">
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
                        >
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            فاكرني
                        </label>
                        <a href="#">نسيت الباسورد؟</a>
                    </div>

                    <button type="submit" class="btn btn-primary" id="loginBtn">
                        دخّلني
                    </button>
                </form>

                <div class="form-footer mt-2">
                    مش عندك حساب؟ <a href="/register">سجّل دلوقتي</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="/public/js/app.js"></script>
</body>
</html>
