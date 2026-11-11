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
                <div class="user-avatar" id="navUserAvatar">
                    <?php if (!empty($user['ProfilePicture'])): ?>
                        <img src="<?= htmlspecialchars($user['ProfilePicture']) ?>" alt="">
                    <?php else: ?>
                        <?= mb_substr($user['FullName'] ?? 'ع', 0, 1) ?>
                    <?php endif; ?>
                </div>
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

            <!-- Profile Picture Upload -->
            <div class="card mb-3">
                <h2 class="card-title">📷 الصورة الشخصية</h2>
                <p class="card-desc">ارفع صورتك الشخصية اللي هتظهر في حسابك</p>

                <div class="avatar-upload-area" id="avatarUploadArea">
                    <div class="avatar-preview" id="avatarPreview">
                        <?php if (!empty($user['ProfilePicture'])): ?>
                            <img src="<?= htmlspecialchars($user['ProfilePicture']) ?>" alt="صورتك الشخصية" id="avatarImg">
                        <?php else: ?>
                            <span class="avatar-initial" id="avatarInitial"><?= mb_substr($user['FullName'] ?? 'ع', 0, 1) ?></span>
                        <?php endif; ?>
                        <div class="avatar-overlay">
                            <span>📷</span>
                            <small>تغيير الصورة</small>
                        </div>
                    </div>
                    <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">
                    <div class="avatar-upload-info">
                        <p style="font-weight: 600; margin-bottom: 4px;">اضغط على الصورة لتغييرها</p>
                        <p class="text-muted" style="font-size: 0.8rem;">JPG, PNG, WebP, GIF — أقصى حجم 5MB</p>
                        <?php if (!empty($user['ProfilePicture'])): ?>
                            <button type="button" class="btn btn-danger btn-sm mt-1" id="deleteAvatarBtn" style="font-size: 0.8rem;">
                                🗑️ حذف الصورة
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="avatarUploadStatus" style="display:none; margin-top: 10px;"></div>
            </div>

            <!-- Profile Info -->
            <div class="card mb-3">
                <h2 class="card-title">👤 البيانات الشخصية</h2>
                <p class="card-desc">عدّل بياناتك الأساسية</p>

                <?php if (!empty($pendingRequests['FullName'])): ?>
                    <div class="alert alert-warning mb-3" style="display: flex; align-items: flex-start; gap: 12px; background: rgba(234, 179, 8, 0.12); border: 1.5px solid rgba(234, 179, 8, 0.35); border-radius: 10px; padding: 14px 16px;">
                        <span style="font-size: 1.4rem;">⏳</span>
                        <div>
                            <strong style="color: #eab308; display: block; font-size: 0.95rem;">طلب تعديل الاسم قيد المراجعة من الإدارة</strong>
                            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.85rem;">
                                طلبت تغيير اسمك إلى: <strong style="color: var(--text-primary);"><?= htmlspecialchars($pendingRequests['FullName']['NewValue']) ?></strong>
                                (بتاريخ: <?= date('d/m/Y H:i', strtotime($pendingRequests['FullName']['RequestedAt'])) ?>).
                                سيتم تطبيق الاسم بمجرد مراجعة وموافقة الإدارة.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pendingRequests['Phone'])): ?>
                    <div class="alert alert-warning mb-3" style="display: flex; align-items: flex-start; gap: 12px; background: rgba(234, 179, 8, 0.12); border: 1.5px solid rgba(234, 179, 8, 0.35); border-radius: 10px; padding: 14px 16px;">
                        <span style="font-size: 1.4rem;">⏳</span>
                        <div>
                            <strong style="color: #eab308; display: block; font-size: 0.95rem;">طلب تعديل رقم الموبايل قيد المراجعة من الإدارة</strong>
                            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.85rem;">
                                طلبت تغيير رقم الموبايل إلى: <strong style="color: var(--text-primary); direction: ltr; display: inline-block;"><?= htmlspecialchars($pendingRequests['Phone']['NewValue']) ?></strong>
                                (بتاريخ: <?= date('d/m/Y H:i', strtotime($pendingRequests['Phone']['RequestedAt'])) ?>).
                                سيتم تحديث الرقم في ملفك بعد موافقة الإدارة.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <form id="profileForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileName">الاسم <small style="color: var(--text-muted); font-size: 0.75rem;">(يتطلب موافقة الإدارة عند التعديل)</small></label>
                            <input type="text" id="profileName" name="name" class="form-control" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="profilePhone">رقم الموبايل <small style="color: var(--text-muted); font-size: 0.75rem;">(يتطلب موافقة الإدارة عند التعديل)</small></label>
                            <input type="tel" id="profilePhone" name="phone" class="form-control" value="<?= htmlspecialchars($user['Phone'] ?? '') ?>" dir="ltr" maxlength="11" inputmode="numeric" pattern="01[0125][0-9]{8}" placeholder="01xxxxxxxxx">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileCity">المدينة</label>
                            <input type="text" id="profileCity" name="city" class="form-control" value="<?= htmlspecialchars($user['City'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="profileExp">سنوات الخبرة</label>
                            <input type="number" id="profileExp" name="years_exp" class="form-control" value="<?= htmlspecialchars($user['YearsOfExp'] ?? 0) ?>" min="0" max="60" dir="ltr" oninput="if(this.value < 0) this.value = 0;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profileBio">نبذة عنك</label>
                        <textarea id="profileBio" name="bio" class="form-control form-textarea" rows="3" maxlength="500" placeholder="اكتب نبذة قصيرة عنك..."><?= htmlspecialchars($user['Bio'] ?? '') ?></textarea>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                            <small class="text-muted" style="font-size: 0.75rem;">الحد الأقصى 500 حرف</small>
                            <small id="bioCharCount" style="color: var(--text-muted); font-size: 0.75rem;">0 / 500 حرف</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="profileLinkedin">LinkedIn <span style="color: var(--danger); font-weight: bold;">*</span></label>
                            <input type="url" id="profileLinkedin" name="linkedin" class="form-control" value="<?= htmlspecialchars($user['LinkedInURL'] ?? '') ?>" placeholder="https://linkedin.com/in/username" dir="ltr" required>
                        </div>
                        <div class="form-group">
                            <label for="profileGithub">GitHub <span style="color: var(--danger); font-weight: bold;">*</span></label>
                            <input type="url" id="profileGithub" name="github" class="form-control" value="<?= htmlspecialchars($user['GitHubURL'] ?? '') ?>" placeholder="https://github.com/username" dir="ltr" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profilePortfolio">Portfolio <small class="text-muted">(اختياري)</small></label>
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
                <p class="card-desc">غيّر الإيميل المرتبط بحسابك (يتطلب موافقة الإدارة قبل تفعيله)</p>

                <?php if (!empty($pendingRequests['Email'])): ?>
                    <div class="alert alert-warning mb-3" style="display: flex; align-items: flex-start; gap: 12px; background: rgba(234, 179, 8, 0.12); border: 1.5px solid rgba(234, 179, 8, 0.35); border-radius: 10px; padding: 14px 16px;">
                        <span style="font-size: 1.4rem;">⏳</span>
                        <div>
                            <strong style="color: #eab308; display: block; font-size: 0.95rem;">طلب تغيير البريد الإلكتروني قيد المراجعة من الإدارة</strong>
                            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.85rem;">
                                طلبت تغيير بريدك الإلكتروني إلى: <strong style="color: var(--text-primary); direction: ltr; display: inline-block;"><?= htmlspecialchars($pendingRequests['Email']['NewValue']) ?></strong>
                                (بتاريخ: <?= date('d/m/Y H:i', strtotime($pendingRequests['Email']['RequestedAt'])) ?>).
                                سيتم اعتماد وتطبيق الإيميل الجديد فور مراجعة وموافقة الإدارة.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

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
                            <input type="password" id="settingsNewPassword" name="new_password" class="form-control" placeholder="••••••••" required dir="ltr" minlength="6" maxlength="30">
                        </div>
                        <div class="form-group">
                            <label for="settingsConfirmPassword">تأكيد كلمة المرور</label>
                            <input type="password" id="settingsConfirmPassword" name="confirm_password" class="form-control" placeholder="••••••••" required dir="ltr" minlength="6" maxlength="30">
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
