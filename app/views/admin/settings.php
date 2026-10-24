<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - إعدادات الأدمن">
    <title>العواطلية - إعدادات الأدمن</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="app-layout">

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/admin/dashboard" class="navbar-brand">
            <img src="/public/images/logo.jpg" alt="العواطلية">
            <span>العواطلية</span>
        </a>
        <div class="navbar-links">
            <a href="/admin/dashboard" class="nav-link">لوحة التحكم</a>
            <a href="/admin/users" class="nav-link">المستخدمين</a>
            <a href="/admin/jobs" class="nav-link">الوظائف</a>
        </div>
        <div class="navbar-actions">
            <a href="/admin/settings" class="user-pill admin-pill active-pill">
                <div class="user-avatar admin-avatar">🔐</div>
                <span><?= htmlspecialchars($user['FullName'] ?? 'مدير النظام') ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 750px;">

            <div class="page-header">
                <h1 class="page-title">إعدادات الأدمن</h1>
                <p class="page-subtitle">إدارة بيانات حساب المدير وإعدادات الأمان</p>
            </div>

            <!-- Admin Info -->
            <div class="card mb-3">
                <h2 class="card-title">👤 البيانات الشخصية</h2>
                <p class="card-desc">عدّل بيانات الأدمن</p>

                <form id="profileForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileName">الاسم</label>
                            <input type="text" id="profileName" name="name" class="form-control" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2" id="saveProfileBtn" style="width: auto;">
                        حفظ التغييرات
                    </button>
                </form>
            </div>

            <!-- Change Email -->
            <div class="card mb-3">
                <h2 class="card-title">📧 تغيير البريد الإلكتروني</h2>
                <p class="card-desc">غيّر الإيميل المرتبط بحسابك</p>

                <form id="changeEmailForm">
                    <div class="form-group">
                        <label for="currentEmail">الإيميل الحالي</label>
                        <input type="email" id="currentEmail" class="form-control" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" disabled dir="ltr">
                    </div>
                    <div class="form-group">
                        <label for="newEmail">الإيميل الجديد</label>
                        <input type="email" id="newEmail" name="new_email" class="form-control" placeholder="new-admin@example.com" required dir="ltr">
                    </div>
                    <div class="form-group">
                        <label for="emailPassword">كلمة المرور (للتأكيد)</label>
                        <input type="password" id="emailPassword" name="password" class="form-control" placeholder="••••••••" required dir="ltr">
                    </div>
                    <button type="submit" class="btn btn-outline mt-1" id="changeEmailBtn" style="width: auto;">
                        تغيير الإيميل
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card mb-3">
                <h2 class="card-title">🔑 تغيير كلمة المرور</h2>
                <p class="card-desc">غيّر الباسورد بتاعك</p>

                <form id="changePasswordForm">
                    <div class="form-group">
                        <label for="currentPassword">كلمة المرور الحالية</label>
                        <input type="password" id="currentPassword" name="current_password" class="form-control" placeholder="••••••••" required dir="ltr">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="settingsNewPassword">كلمة المرور الجديدة</label>
                            <input type="password" id="settingsNewPassword" name="new_password" class="form-control" placeholder="••••••••" required dir="ltr" minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="settingsConfirmPassword">تأكيد كلمة المرور</label>
                            <input type="password" id="settingsConfirmPassword" name="confirm_password" class="form-control" placeholder="••••••••" required dir="ltr" minlength="6">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline mt-1" id="changePasswordBtn" style="width: auto;">
                        تغيير كلمة المرور
                    </button>
                </form>
            </div>

            <!-- OTP (always enabled for admin but can view status) -->
            <div class="card mb-3">
                <h2 class="card-title">🔐 إعدادات الأمان</h2>
                <p class="card-desc">التحقق الثنائي مطلوب دائماً للأدمن</p>

                <div class="security-setting">
                    <div class="security-setting-info">
                        <h4>التحقق بكود OTP عند الدخول</h4>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            التحقق بـ OTP مطلوب دائماً لحسابات الأدمن ومش ممكن يتعطل لأسباب أمنية.
                        </p>
                    </div>
                    <div class="security-setting-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label" style="color: var(--success);">مفعّل دائماً</span>
                    </div>
                </div>
            </div>

        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
