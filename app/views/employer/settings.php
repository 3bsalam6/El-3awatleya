<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - إعدادات صاحب العمل">
    <title>العواطلية - إعدادات الحساب</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="app-layout">

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/employer/dashboard" class="navbar-brand">
            <img src="/public/images/logo.jpg" alt="العواطلية">
            <span>العواطلية</span>
        </a>
        <div class="navbar-links">
            <a href="/employer/dashboard" class="nav-link">لوحة التحكم</a>
            <a href="/employer/post-job" class="nav-link">نشر وظيفة</a>
            <a href="/employer/applicants" class="nav-link">المتقدمين</a>
        </div>
        <div class="navbar-actions">
            <a href="/employer/settings" class="user-pill employer-pill active-pill">
                <div class="user-avatar employer-avatar"><?= mb_substr($user['CompanyName'] ?? $user['FullName'] ?? 'ش', 0, 1) ?></div>
                <span><?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? 'شركة') ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 750px;">

            <div class="page-header">
                <h1 class="page-title">إعدادات الحساب</h1>
                <p class="page-subtitle">إدارة بيانات الشركة وإعدادات الأمان</p>
            </div>

            <!-- Company Info -->
            <div class="card mb-3">
                <h2 class="card-title">🏢 بيانات الشركة</h2>
                <p class="card-desc">عدّل بيانات الشركة الأساسية</p>

                <form id="profileForm">
                    <div class="form-group">
                        <label for="companyName">اسم الشركة</label>
                        <input type="text" id="companyName" name="company_name" class="form-control" value="<?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? '') ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="industry">المجال</label>
                            <input type="text" id="industry" name="industry" class="form-control" value="<?= htmlspecialchars($user['Industry'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="companySize">حجم الشركة</label>
                            <?php $sz = $user['CompanySize'] ?? '11-50'; ?>
                            <select id="companySize" name="company_size" class="form-control">
                                <option value="1-10" <?= $sz === '1-10' ? 'selected' : '' ?>>1-10 موظفين</option>
                                <option value="11-50" <?= $sz === '11-50' ? 'selected' : '' ?>>11-50 موظف</option>
                                <option value="51-200" <?= $sz === '51-200' ? 'selected' : '' ?>>51-200 موظف</option>
                                <option value="201-500" <?= $sz === '201-500' ? 'selected' : '' ?>>201-500 موظف</option>
                                <option value="500+" <?= $sz === '500+' ? 'selected' : '' ?>>+500 موظف</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="companyCity">المدينة</label>
                            <input type="text" id="companyCity" name="city" class="form-control" value="<?= htmlspecialchars($user['City'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="companyWebsite">الموقع الإلكتروني</label>
                            <input type="url" id="companyWebsite" name="website" class="form-control" value="<?= htmlspecialchars($user['CompanyWebsite'] ?? '') ?>" placeholder="https://..." dir="ltr">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="companyAddress">العنوان</label>
                        <input type="text" id="companyAddress" name="address" class="form-control" value="<?= htmlspecialchars($user['Address'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="companyDesc">وصف الشركة</label>
                        <textarea id="companyDesc" name="description" class="form-control form-textarea" rows="3" placeholder="نبذة عن الشركة..."><?= htmlspecialchars($user['Description'] ?? '') ?></textarea>
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

            <!-- OTP Settings -->
            <div class="card mb-3">
                <h2 class="card-title">🔐 إعدادات الأمان</h2>
                <p class="card-desc">تفعيل التحقق الثنائي (OTP) لحماية حسابك</p>

                <div class="security-setting">
                    <div class="security-setting-info">
                        <h4>التحقق بكود OTP عند الدخول</h4>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            لما تفعّل الخيار ده، كل مرة تسجل دخول هيتم إرسال كود OTP على إيميلك وتدخله عشان تكمل الدخول.
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

        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
