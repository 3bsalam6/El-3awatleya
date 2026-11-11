<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - تعديل الوظيفة وإعادة الإرسال للمراجعة">
    <title>تعديل وظيفة: <?= htmlspecialchars($job['Title']) ?> - العواطلية</title>
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
            <a href="/employer/settings" class="user-pill employer-pill">
                <div class="user-avatar employer-avatar">
                    <?php if (!empty($user['CompanyLogo']) || !empty($user['ProfilePicture'])): ?>
                        <img src="<?= htmlspecialchars($user['CompanyLogo'] ?? $user['ProfilePicture']) ?>" alt="">
                    <?php else: ?>
                        <?= mb_substr($user['CompanyName'] ?? $user['FullName'] ?? 'ش', 0, 1) ?>
                    <?php endif; ?>
                </div>
                <span><?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? 'شركة') ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 800px;">

            <div class="page-header" style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h1 class="page-title">✏️ تعديل الوظيفة وإعادة الإرسال للمراجعة</h1>
                        <p class="page-subtitle">عدّل بيانات الوظيفة، وحدد مدة العرض الجديدة ليتم فحصها واعتمادها من الإدارة</p>
                    </div>
                    <a href="/employer/dashboard" class="btn btn-outline btn-sm">← رجوع للوحة التحكم</a>
                </div>
            </div>

            <?php if (!empty($job['AdminRemoveReason'])): ?>
                <div class="alert alert-danger mb-4" style="border-radius: var(--radius-md); padding: 18px 22px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3);">
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <span style="font-size: 1.8rem; line-height: 1;">🚫</span>
                        <div>
                            <strong style="display: block; font-size: 1.05rem; margin-bottom: 4px; color: #dc2626;">سبب رفض / تعطيل الإعلان من قِبل الإدارة:</strong>
                            <p style="margin: 0; font-size: 0.95rem; line-height: 1.6; color: #991b1b; font-weight: 500;">
                                <?= nl2br(htmlspecialchars($job['AdminRemoveReason'])) ?>
                            </p>
                            <small class="text-muted" style="display: block; margin-top: 8px;">
                                💡 يرجى تعديل البنود المخالفة، وتحديد مدة العرض المناسبة أدناه ثم النقر على زر الإرسال.
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($job['AdminReviewStatus']) && $job['AdminReviewStatus'] === 'pending_review'): ?>
                <div class="alert alert-warning mb-4" style="border-radius: var(--radius-md); padding: 14px 18px;">
                    <span>⏳ هذه الوظيفة تم تعديلها مسبقاً وهي قيد مراجعة الإدارة حالياً. يمكنك إجراء تعديلات إضافية إذا رغبت.</span>
                </div>
            <?php endif; ?>

            <div class="card" style="padding: 30px;">
                <form id="editJobForm" onsubmit="submitEditJobForm(event)">
                    <input type="hidden" name="job_id" id="editJobId" value="<?= $job['JobID'] ?>">

                    <!-- Job Title -->
                    <div class="form-group">
                        <label for="editJobTitle" style="font-weight: 600;">المسمى الوظيفي *</label>
                        <input 
                            type="text" 
                            id="editJobTitle" 
                            name="title" 
                            class="form-control" 
                            placeholder="مثلاً: مبرمج PHP / Laravel"
                            value="<?= htmlspecialchars($job['Title']) ?>"
                            required
                        >
                    </div>

                    <!-- Job Description -->
                    <div class="form-group">
                        <label for="editJobDesc" style="font-weight: 600;">الوصف الوظيفي *</label>
                        <textarea 
                            id="editJobDesc" 
                            name="description" 
                            class="form-control form-textarea" 
                            rows="5"
                            placeholder="اوصف الوظيفة والمهام والمسؤوليات بالتفصيل..."
                            required
                        ><?= htmlspecialchars($job['Description']) ?></textarea>
                    </div>

                    <!-- Requirements -->
                    <div class="form-group">
                        <label for="editJobReqs" style="font-weight: 600;">المتطلبات والشروط</label>
                        <textarea 
                            id="editJobReqs" 
                            name="requirements" 
                            class="form-control form-textarea" 
                            rows="4"
                            placeholder="شروط التقدم للوظيفة (اكتب كل شرط في سطر منفصل)..."
                        ><?= htmlspecialchars($job['Requirements'] ?? '') ?></textarea>
                    </div>

                    <!-- Job Type & Experience Level -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editJobType" style="font-weight: 600;">نوع الوظيفة *</label>
                            <div class="select-wrapper">
                                <select id="editJobType" name="job_type" class="form-select" required>
                                    <option value="full-time" <?= ($job['JobType'] ?? '') === 'full-time' ? 'selected' : '' ?>>دوام كامل (Full Time)</option>
                                    <option value="part-time" <?= ($job['JobType'] ?? '') === 'part-time' ? 'selected' : '' ?>>دوام جزئي (Part Time)</option>
                                    <option value="remote" <?= ($job['JobType'] ?? '') === 'remote' ? 'selected' : '' ?>>عن بعد (Remote)</option>
                                    <option value="freelance" <?= ($job['JobType'] ?? '') === 'freelance' ? 'selected' : '' ?>>عمل حر (Freelance)</option>
                                    <option value="internship" <?= ($job['JobType'] ?? '') === 'internship' ? 'selected' : '' ?>>تدريب (Internship)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="editExpLevel" style="font-weight: 600;">مستوى الخبرة *</label>
                            <div class="select-wrapper">
                                <select id="editExpLevel" name="experience_level" class="form-select" required>
                                    <option value="entry" <?= ($job['ExperienceLevel'] ?? '') === 'entry' ? 'selected' : '' ?>>مبتدئ (0-1 سنة)</option>
                                    <option value="mid" <?= ($job['ExperienceLevel'] ?? '') === 'mid' ? 'selected' : '' ?>>متوسط (2-4 سنوات)</option>
                                    <option value="senior" <?= ($job['ExperienceLevel'] ?? '') === 'senior' ? 'selected' : '' ?>>خبرة (5+ سنوات)</option>
                                    <option value="lead" <?= ($job['ExperienceLevel'] ?? '') === 'lead' ? 'selected' : '' ?>>قيادي / إداري</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="form-group">
                        <label for="editJobCity" style="font-weight: 600;">المدينة ومكان العمل</label>
                        <input 
                            type="text" 
                            id="editJobCity" 
                            name="city" 
                            class="form-control" 
                            placeholder="مثلاً: القاهرة أو عن بُعد"
                            value="<?= htmlspecialchars($job['City'] ?? '') ?>"
                        >
                    </div>

                    <!-- Salary Range -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editSalaryMin" style="font-weight: 600;">الحد الأدنى للمرتب (ج.م)</label>
                            <input 
                                type="number" 
                                id="editSalaryMin" 
                                name="salary_min" 
                                class="form-control" 
                                placeholder="5000"
                                min="0"
                                step="100"
                                dir="ltr"
                                value="<?= !empty($job['SalaryMin']) ? (int)$job['SalaryMin'] : '' ?>"
                                oninput="if(this.value < 0) this.value = 0;"
                            >
                        </div>
                        <div class="form-group">
                            <label for="editSalaryMax" style="font-weight: 600;">الحد الأقصى للمرتب (ج.م)</label>
                            <input 
                                type="number" 
                                id="editSalaryMax" 
                                name="salary_max" 
                                class="form-control" 
                                placeholder="15000"
                                min="0"
                                step="100"
                                dir="ltr"
                                value="<?= !empty($job['SalaryMax']) ? (int)$job['SalaryMax'] : '' ?>"
                                oninput="if(this.value < 0) this.value = 0;"
                            >
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="form-group">
                        <label for="editJobSkills" style="font-weight: 600;">المهارات المطلوبة</label>
                        <input 
                            type="text" 
                            id="editJobSkills" 
                            name="skills" 
                            class="form-control" 
                            placeholder="مثلاً: PHP, MySQL, JavaScript, Git (افصل بفاصلة)"
                            value="<?= htmlspecialchars($job['Skills'] ?? '') ?>"
                        >
                        <small class="text-muted">افصل بين كل مهارة بفاصلة</small>
                    </div>

                    <hr style="margin: 24px 0; border: none; border-top: 1px dashed var(--border-color, #e2e8f0);">

                    <!-- Expiry (Duration Selection) -->
                    <div class="form-group">
                        <label style="font-weight: 700; font-size: 0.95rem; display: block; margin-bottom: 8px;">
                            ⏱️ مدة العرض المقترحة (الموعد النهائي للتقديم) *
                        </label>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 12px;">
                            حدد مدة التقديم التي ترغب بها، وسيتم تفعيل الإعلان بناءً عليها فور موافقة الإدارة:
                        </p>

                        <!-- Quick duration presets -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; margin-bottom: 12px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="setEditDuration(1, 'minutes')" style="font-size: 0.82rem; background: rgba(245, 158, 11, 0.1); border-color: #f59e0b; color: #b45309; font-weight: 600;">⚡ +1 دقيقة (تجربة)</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="setEditDuration(10, 'minutes')" style="font-size: 0.82rem;">⏱️ +10 دقائق</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="setEditDuration(1, 'hours')" style="font-size: 0.82rem;">⏳ +ساعة واحدة</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="setEditDuration(24, 'hours')" style="font-size: 0.82rem;">📅 +24 ساعة (يوم)</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="setEditDuration(7, 'days')" style="font-size: 0.82rem;">🗓️ +أسبوع كامل</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="setEditDuration(30, 'days')" style="font-size: 0.82rem;">🗓️ +30 يوم (شهر)</button>
                        </div>

                        <!-- Datetime Picker -->
                        <label for="editExpiryDate" style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 4px;">أو حدد تاريخ وساعة معينة بدقة:</label>
                        <input 
                            type="datetime-local" 
                            id="editExpiryDate" 
                            name="expires_at" 
                            class="form-control"
                            dir="ltr"
                            min="<?= date('Y-m-d\TH:i') ?>"
                            value="<?= !empty($job['ExpiresAt']) && strtotime($job['ExpiresAt']) > time() ? date('Y-m-d\TH:i', strtotime($job['ExpiresAt'])) : '' ?>"
                            required
                        >
                    </div>

                    <!-- Employer Notes to Admin -->
                    <div class="form-group mb-4">
                        <label for="editEmployerNotes" style="font-weight: 600; font-size: 0.9rem;">
                            💬 ملاحظات وتوضيح للإدارة (اختياري)
                        </label>
                        <textarea 
                            id="editEmployerNotes" 
                            name="employer_notes" 
                            class="form-control form-textarea" 
                            rows="3"
                            placeholder="وضح هنا ما تم تعديله لتسريع عملية فحص الإعلان واعتماده..."
                        ><?= htmlspecialchars($job['EmployerReviewNotes'] ?? '') ?></textarea>
                    </div>

                    <div class="alert alert-danger" id="editJobError" style="display: none; margin-bottom: 16px;">
                        <span>❌</span>
                        <span id="editJobErrorText"></span>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                        <a href="/employer/dashboard" class="btn btn-outline">إلغاء والعودة</a>
                        <button type="submit" class="btn btn-primary" id="saveEditJobBtn" style="min-width: 200px;">
                            💾 حفظ وإرسال للمراجعة
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

</div>

<!-- Success Modal -->
<div class="modal-overlay" id="editSuccessModal" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-card" style="max-width: 480px; width: 92%; text-align: center; border-radius: var(--radius-lg); padding: 32px;">
        <div class="modal-icon success" style="font-size: 2.5rem; margin-bottom: 12px;">🎉</div>
        <h2 style="font-size: 1.3rem; margin-bottom: 8px;">تم إرسال التعديلات بنجاح!</h2>
        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.6; margin-bottom: 24px;">
            تم استلام تعديلات الوظيفة وتحديد مدة العرض المقترحة. الوظيفة الآن قيد مراجعة إدارة المنصة وسيتم إشعارك فور تفعيلها.
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="/employer/dashboard" class="btn btn-primary">العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
<script>
function setEditDuration(amount, unit) {
    var now = new Date();
    if (unit === 'minutes') {
        now.setMinutes(now.getMinutes() + amount);
    } else if (unit === 'hours') {
        now.setHours(now.getHours() + amount);
    } else if (unit === 'days') {
        now.setDate(now.getDate() + amount);
    }
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var formatted = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    var input = document.getElementById('editExpiryDate');
    if (input) {
        input.value = formatted;
        input.classList.add('highlight-pulse');
        setTimeout(function() { input.classList.remove('highlight-pulse'); }, 1000);
    }
}

function submitEditJobForm(e) {
    e.preventDefault();
    var errBox = document.getElementById('editJobError');
    var errText = document.getElementById('editJobErrorText');
    if (errBox) errBox.style.display = 'none';

    var salMinEl = document.getElementById('editSalaryMin');
    var salMaxEl = document.getElementById('editSalaryMax');
    var salMin = (salMinEl && salMinEl.value !== '') ? parseFloat(salMinEl.value) : null;
    var salMax = (salMaxEl && salMaxEl.value !== '') ? parseFloat(salMaxEl.value) : null;

    if (salMin !== null && salMin < 0) {
        if (errBox && errText) {
            errText.textContent = 'الحد الأدنى للمرتب لا يمكن أن يكون بالسالب';
            errBox.style.display = 'flex';
        } else {
            alert('الحد الأدنى للمرتب لا يمكن أن يكون بالسالب');
        }
        salMinEl.focus();
        return;
    }

    if (salMax !== null && salMax < 0) {
        if (errBox && errText) {
            errText.textContent = 'الحد الأقصى للمرتب لا يمكن أن يكون بالسالب';
            errBox.style.display = 'flex';
        } else {
            alert('الحد الأقصى للمرتب لا يمكن أن يكون بالسالب');
        }
        salMaxEl.focus();
        return;
    }

    if (salMin !== null && salMax !== null && salMin > salMax) {
        if (errBox && errText) {
            errText.textContent = 'الحد الأدنى للمرتب لا يمكن أن يكون أكبر من الحد الأقصى';
            errBox.style.display = 'flex';
        } else {
            alert('الحد الأدنى للمرتب لا يمكن أن يكون أكبر من الحد الأقصى');
        }
        salMinEl.focus();
        return;
    }

    var btn = document.getElementById('saveEditJobBtn');
    var origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner" style="display:inline-block;width:12px;height:12px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> جاري الحفظ والإرسال...';
    }

    var form = document.getElementById('editJobForm');
    var formData = new FormData(form);

    fetch('/employer/jobs/edit', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
        if (data.success) {
            var modal = document.getElementById('editSuccessModal');
            if (modal) {
                modal.style.display = 'flex';
            } else {
                alert(data.message);
                window.location.href = '/employer/dashboard';
            }
        } else {
            if (errBox && errText) {
                errText.textContent = data.message || 'حدث خطأ أثناء حفظ التعديلات';
                errBox.style.display = 'flex';
            } else {
                alert(data.message || 'حدث خطأ');
            }
        }
    })
    .catch(function(err) {
        if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
        if (errBox && errText) {
            errText.textContent = 'تعذر الاتصال بالسيرفر';
            errBox.style.display = 'flex';
        } else {
            alert('تعذر الاتصال بالسيرفر');
        }
    });
}
</script>
</body>
</html>
