<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - التقديم على وظيفة">
    <title>العواطلية - قدّم على الوظيفة</title>
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
        <div class="navbar-actions">
            <a href="/jobs" class="btn btn-outline btn-sm">← الوظائف</a>
            <a href="/settings" class="user-pill">
                <div class="user-avatar"><?= mb_substr($user['FullName'] ?? 'ع', 0, 1) ?></div>
                <span><?= htmlspecialchars(explode(' ', $user['FullName'] ?? 'مستخدم')[0]) ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 800px;">

            <!-- Job Details -->
            <div class="card mb-3">
                <div class="job-detail-header">
                    <div class="job-company-logo" style="font-size: 2.5rem;">🏢</div>
                    <div>
                        <h1 class="page-title" id="jobTitleDetail"><?= htmlspecialchars($job['Title']) ?></h1>
                        <p class="page-subtitle" id="jobCompanyDetail"><?= htmlspecialchars($job['CompanyName'] ?? $job['EmployerName'] ?? 'شركة') ?></p>
                    </div>
                </div>

                <div class="job-tags mt-2">
                    <span class="tag tag-type"><?= htmlspecialchars($job['JobType'] ?? 'دوام كامل') ?></span>
                    <span class="tag tag-level"><?= htmlspecialchars($job['ExperienceLevel'] ?? 'متوسط') ?></span>
                    <?php if (!empty($job['City'])): ?>
                        <span class="tag tag-location"><?= htmlspecialchars($job['City']) ?></span>
                    <?php endif; ?>
                    <span class="tag tag-salary">
                        <?= !empty($job['SalaryMin']) ? number_format($job['SalaryMin']) . (!empty($job['SalaryMax']) ? ' - ' . number_format($job['SalaryMax']) : '') . ' ' . htmlspecialchars($job['SalaryCurrency'] ?? 'ج.م') : 'راتب تنافسي' ?>
                    </span>
                    <?php if (!empty($job['ExpiresAt'])): ?>
                        <?php if (!empty($isExpired)): ?>
                            <span class="tag" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);">
                                ⛔ انتهى التقديم
                            </span>
                        <?php else: ?>
                            <span class="tag job-countdown" data-expires="<?= htmlspecialchars(date('c', strtotime($job['ExpiresAt']))) ?>" style="background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3);">
                                ⏳ متبقي: <strong class="countdown-text">جاري الحساب...</strong>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="job-detail-section mt-3">
                    <h3>وصف الوظيفة</h3>
                    <p><?= nl2br(htmlspecialchars($job['Description'])) ?></p>
                </div>

                <?php if (!empty($job['Requirements'])): ?>
                    <div class="job-detail-section mt-2">
                        <h3>المتطلبات</h3>
                        <ul class="requirements-list">
                            <?php foreach (explode("\n", $job['Requirements']) as $req): ?>
                                <?php if (trim($req)): ?>
                                    <li><?= htmlspecialchars(trim($req)) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($job['Skills'])): ?>
                    <div class="job-detail-section mt-2">
                        <h3>المهارات المطلوبة</h3>
                        <div class="job-skills">
                            <?php foreach (array_filter(array_map('trim', explode(',', $job['Skills']))) as $s): ?>
                                <span class="skill-tag"><?= htmlspecialchars($s) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Application Form -->
            <div class="card">
                <?php if (!empty($isRemovedAdmin)): ?>
                    <div class="alert alert-danger" style="padding: 32px; text-align: center; border-radius: var(--radius-md);">
                        <span style="font-size: 2.5rem;">🚫</span>
                        <h3 class="mt-2" style="color: var(--danger);">هذه الوظيفة غير متاحة حالياً</h3>
                        <p class="text-muted mt-1">
                            تم تعطيل إعلان هذه الوظيفة من قِبل إدارة المنصة لمخالفة سياسات النشر أو لإجراء مراجعة.
                        </p>
                        <a href="/jobs" class="btn btn-primary mt-3" style="display: inline-block;">تصفح وظائف أخرى</a>
                    </div>
                <?php elseif (!empty($isJobClosed)): ?>
                    <div class="alert alert-warning" style="padding: 32px; text-align: center; border-radius: var(--radius-md); background: rgba(196, 113, 43, 0.1); border: 1px solid rgba(196, 113, 43, 0.3);">
                        <span style="font-size: 2.5rem;">⏸️</span>
                        <h3 class="mt-2" style="color: var(--accent);">تم إيقاف استقبال طلبات التقديم مؤقتاً</h3>
                        <p class="text-muted mt-1">
                            قام صاحب العمل بإيقاف استقبال عروض التقديم على هذه الوظيفة في الوقت الحالي.
                        </p>
                        <a href="/jobs" class="btn btn-primary mt-3" style="display: inline-block;">تصفح وظائف أخرى</a>
                    </div>
                <?php elseif (!empty($isExpired)): ?>
                    <div class="alert alert-danger" style="padding: 32px; text-align: center; border-radius: var(--radius-md);">
                        <span style="font-size: 2.5rem;">⛔</span>
                        <h3 class="mt-2" style="color: var(--danger);">انتهى موعد التقديم على هذه الوظيفة</h3>
                        <p class="text-muted mt-1">
                            تم إغلاق باب التقديم تلقائياً بتاريخ <?= date('d/m/Y h:i A', strtotime($job['ExpiresAt'])) ?>. نتمنى لك التوفيق في فرص قادمة!
                        </p>
                        <a href="/jobs" class="btn btn-primary mt-3" style="display: inline-block;">تصفح وظائف أخرى</a>
                    </div>
                <?php elseif (!empty($alreadyApplied)): ?>
                    <div class="alert alert-success" style="padding: 24px; text-align: center;">
                        <span style="font-size: 2rem;">✅</span>
                        <h3 class="mt-2">لقد قدمت بالفعل على هذه الوظيفة!</h3>
                        <p class="text-muted mt-1">طلبك مسجل وقيد المراجعة لدى صاحب العمل.</p>
                        <a href="/my-applications" class="btn btn-primary mt-2">متابعة طلباتي</a>
                    </div>
                <?php else: ?>
                    <h2 class="card-title">قدّم على الوظيفة دي</h2>
                    <p class="card-desc">ارفع الـ CV بتاعك ووضح ليه الوظيفة دي مناسبة ليك</p>

                    <form id="applyForm">
                        <input type="hidden" name="job_id" value="<?= htmlspecialchars($job['JobID']) ?>">

                        <!-- Upload CV -->
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" id="cvFile" name="cv" accept=".pdf,.doc,.docx" required>
                            <span class="upload-icon">📄</span>
                            <h3>ارفع الـ CV بتاعك</h3>
                            <p>PDF, DOC, DOCX — أقصى حجم 5MB</p>
                        </div>

                        <div class="file-info" id="fileInfo">
                            <span>✅</span>
                            <span id="fileName">filename.pdf</span>
                            <span id="fileSize" class="text-muted">2.3 MB</span>
                            <button type="button" class="remove-file" id="removeFile">x حذف</button>
                        </div>

                        <!-- Why this job is suitable -->
                        <div class="form-group mt-3">
                            <label for="coverLetter">
                                ليه الوظيفة دي مناسبة ليك؟ <span style="color: var(--danger);">*</span>
                            </label>
                            <textarea 
                                id="coverLetter" 
                                name="cover_letter" 
                                class="form-control form-textarea"
                                placeholder="اكتب بالتفصيل ليه شايف نفسك مناسب للوظيفة دي ومهاراتك بتخدم متطلباتها..."
                                rows="5"
                                required
                            ></textarea>
                        </div>

                        <div class="alert alert-danger" id="applyError" style="display: none;">
                            <span>❌</span>
                            <span id="applyErrorText"></span>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2" id="submitApplicationBtn" disabled>
                            قدّم الطلب
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </main>

</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon success">✅</div>
        <h2>تم التقديم بنجاح!</h2>
        <p>طلبك اتبعت لصاحب العمل. هيتواصل معاك لو مهتم.</p>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
            <a href="/my-applications" class="btn btn-primary">طلباتي</a>
            <a href="/jobs" class="btn btn-outline">وظائف تانية</a>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
