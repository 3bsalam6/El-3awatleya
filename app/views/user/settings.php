<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - إعدادات الحساب">
    <title>العواطلية - الإعدادات</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="app-layout">

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/dashboard" class="navbar-brand">
            <img src="/public/images/logo.jpg" alt="العواطلية">
            <span>العواطلية</span>
        </a>
        <div class="navbar-links">
            <a href="/dashboard" class="nav-link">لوحة التحكم</a>
            <a href="/jobs" class="nav-link">الوظائف</a>
            <a href="/my-applications" class="nav-link">طلباتي</a>
            <a href="/enhancements" class="nav-link">تحسين الـ CV</a>
        </div>
        <div class="navbar-actions">
            <a href="/settings" class="user-pill active-pill">
                <div class="user-avatar"><?= mb_substr($user['FullName'] ?? 'ع', 0, 1) ?></div>
                <span><?= htmlspecialchars(explode(' ', $user['FullName'] ?? 'مستخدم')[0]) ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 750px;">

            <div class="page-header">
                <h1 class="page-title">⚙️ إعدادات الحساب</h1>
                <p class="page-subtitle">إدارة بياناتك الشخصية وإعدادات الأمان</p>
            </div>

            <!-- Profile Info -->
            <div class="card mb-3">
                <h2 class="card-title">👤 البيانات الشخصية</h2>
                <p class="card-desc">عدّل بياناتك الأساسية</p>

                <form id="profileForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileName">الاسم</label>
                            <input type="text" id="profileName" name="name" class="form-control" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="profilePhone">رقم الموبايل</label>
                            <input type="tel" id="profilePhone" name="phone" class="form-control" value="<?= htmlspecialchars($user['Phone'] ?? '') ?>" dir="ltr">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileCity">المدينة</label>
                            <input type="text" id="profileCity" name="city" class="form-control" value="<?= htmlspecialchars($user['City'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="profileExp">سنوات الخبرة</label>
                            <input type="number" id="profileExp" name="years_exp" class="form-control" value="<?= htmlspecialchars($user['YearsOfExp'] ?? 0) ?>" dir="ltr">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profileBio">نبذة عنك</label>
                        <textarea id="profileBio" name="bio" class="form-control form-textarea" rows="3" placeholder="اكتب نبذة قصيرة عنك..."><?= htmlspecialchars($user['Bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileLinkedin">LinkedIn</label>
                            <input type="url" id="profileLinkedin" name="linkedin" class="form-control" value="<?= htmlspecialchars($user['LinkedInURL'] ?? '') ?>" placeholder="https://linkedin.com/in/..." dir="ltr">
                        </div>
                        <div class="form-group">
                            <label for="profileGithub">GitHub</label>
                            <input type="url" id="profileGithub" name="github" class="form-control" value="<?= htmlspecialchars($user['GitHubURL'] ?? '') ?>" placeholder="https://github.com/..." dir="ltr">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profilePortfolio">Portfolio</label>
                        <input type="url" id="profilePortfolio" name="portfolio" class="form-control" value="<?= htmlspecialchars($user['PortfolioURL'] ?? '') ?>" placeholder="https://yoursite.com" dir="ltr">
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
                        <input type="email" id="newEmail" name="new_email" class="form-control" placeholder="new-email@example.com" required dir="ltr">
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

            <!-- Security: OTP Settings -->
            <div class="card mb-3">
                <h2 class="card-title">🔐 إعدادات الأمان</h2>
                <p class="card-desc">تفعيل التحقق الثنائي (OTP) لحماية حسابك</p>

                <div class="security-setting">
                    <div class="security-setting-info">
                        <h4>التحقق بكود OTP عند الدخول</h4>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            لما تفعّل الخيار ده، كل مرة تسجل دخول هيتم إرسال كود OTP على إيميلك وتدخله عشان تكمل الدخول. ده بيزود أمان حسابك.
                        </p>
                    </div>
                    <div class="security-setting-toggle">
                        <label class="toggle-switch">
                            <input type="checkbox" id="otpToggle" name="otp_enabled" <?= !empty($user['IsOTPEnabled']) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label" id="otpStatus"><?= !empty($user['IsOTPEnabled']) ? 'مفعل' : 'معطّل' ?></span>
                    </div>
                </div>

                <div class="otp-setup-info" id="otpSetupInfo" style="<?= !empty($user['IsOTPEnabled']) ? '' : 'display: none;' ?>">
                    <div class="alert alert-success">
                        <span>✅</span>
                        <span>تم تفعيل التحقق بـ OTP! في كل مرة تسجل دخول هيتم إرسال كود على <strong><?= htmlspecialchars($user['Email'] ?? '') ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card danger-card">
                <h2 class="card-title" style="color: var(--danger);">⚠️ منطقة الخطر</h2>
                <p class="card-desc">إجراءات لا يمكن التراجع عنها</p>
                
                <button class="btn btn-danger" id="deleteAccountBtn" style="width: auto;" onclick="confirmDeleteAccount()">
                    🗑️ حذف الحساب نهائياً
                </button>
            </div>

        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
