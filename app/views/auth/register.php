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

                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-danger"><?= Session::flash('error') ?></div>
                <?php endif; ?>
                <?php if (Session::hasFlash('success')): ?>
                    <div class="alert alert-success"><?= Session::flash('success') ?></div>
                <?php endif; ?>

                <!-- Role Selector Tabs -->
                <div class="role-tabs" id="roleTabs">
                    <button type="button" class="role-tab active" data-role="user">
                        <span class="role-tab-icon">👤</span>
                        مشتغل
                    </button>
                    <button type="button" class="role-tab" data-role="employer">
                        <span class="role-tab-icon">🏢</span>
                        صاحب عمل
                    </button>
                </div>

                <form id="registerForm" action="/register" method="POST">
                    <input type="hidden" name="role" id="registerRole" value="user">

                    <!-- Common Fields -->
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
                        <label for="phone">رقم الموبايل</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-control" 
                            placeholder="01xxxxxxxxx"
                            dir="ltr"
                        >
                    </div>

                    <!-- Employer-only Fields -->
                    <div class="employer-fields" id="employerFields" style="display: none;">
                        <div class="form-group">
                            <label for="companyName">اسم الشركة</label>
                            <input 
                                type="text" 
                                id="companyName" 
                                name="company_name" 
                                class="form-control" 
                                placeholder="اسم الشركة أو المؤسسة"
                            >
                        </div>

                        <div class="form-group">
                            <label for="industry">المجال</label>
                            <div class="select-wrapper">
                                <select id="industry" name="industry" class="form-select">
                                    <option value="" disabled selected>اختار المجال...</option>
                                    <option value="tech">تكنولوجيا</option>
                                    <option value="finance">مالية وبنوك</option>
                                    <option value="healthcare">صحة وطب</option>
                                    <option value="education">تعليم</option>
                                    <option value="retail">تجارة</option>
                                    <option value="construction">مقاولات وبناء</option>
                                    <option value="media">إعلام ودعاية</option>
                                    <option value="other">أخرى</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="companySize">حجم الشركة</label>
                            <div class="select-wrapper">
                                <select id="companySize" name="company_size" class="form-select">
                                    <option value="" disabled selected>اختار...</option>
                                    <option value="1-10">1-10 موظفين</option>
                                    <option value="11-50">11-50 موظف</option>
                                    <option value="51-200">51-200 موظف</option>
                                    <option value="201-500">201-500 موظف</option>
                                    <option value="500+">أكثر من 500</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- User-only Fields -->
                    <div class="user-fields" id="userFields">
                        <div class="form-group">
                            <label for="city">المدينة</label>
                            <input 
                                type="text" 
                                id="city" 
                                name="city" 
                                class="form-control" 
                                placeholder="مثلاً: القاهرة"
                            >
                        </div>
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
                            name="confirm_password" 
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

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
