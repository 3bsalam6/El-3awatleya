<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - لوحة تحكم صاحب العمل">
    <title>العواطلية - لوحة تحكم صاحب العمل</title>
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
            <a href="/employer/dashboard" class="nav-link active">لوحة التحكم</a>
            <a href="/employer/post-job" class="nav-link">نشر وظيفة</a>
            <a href="/employer/applicants" class="nav-link">المتقدمين</a>
        </div>
        <div class="navbar-actions">
            <a href="/employer/settings" class="user-pill employer-pill">
                <div class="user-avatar employer-avatar"><?= mb_substr($user['CompanyName'] ?? $user['FullName'] ?? 'ش', 0, 1) ?></div>
                <span><?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? 'شركة') ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container">

            <div class="page-header">
                <h1 class="page-title">أهلاً بك، <?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? 'صاحب العمل') ?> 🏢</h1>
                <p class="page-subtitle">إدارة الوظائف والمتقدمين من هنا</p>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= $stats['total_jobs'] ?? 0 ?></div>
                    <div class="stat-label">وظائف منشورة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?= $stats['total_applications'] ?? 0 ?></div>
                    <div class="stat-label">إجمالي المتقدمين</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-value"><?= $stats['pending'] ?? 0 ?></div>
                    <div class="stat-label">طلبات قيد المراجعة</div>
                </div>
                <a href="/employer/applicants?status=shortlisted" class="stat-card" style="text-decoration: none; color: inherit; border-color: rgba(245, 158, 11, 0.4); cursor: pointer;">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15);">⭐</div>
                    <div class="stat-value" style="color: #f59e0b;"><?= $stats['shortlisted'] ?? 0 ?></div>
                    <div class="stat-label">القائمة القصيرة</div>
                </a>
            </div>

            <div class="dashboard-grid">

                <!-- Active Jobs -->
                <div class="card card-full">
                    <div class="card-header-row">
                        <h2 class="card-title">الوظائف المنشورة</h2>
                        <a href="/employer/post-job" class="btn btn-primary btn-sm">+ نشر وظيفة جديدة</a>
                    </div>
                    <p class="card-desc">الوظائف اللي نشرتها على المنصة</p>

                    <div class="employer-jobs-list">
                        <?php if (!empty($jobs)): ?>
                            <?php foreach ($jobs as $j): ?>
                                <div class="employer-job-item" id="employer-job-<?= $j['JobID'] ?>">
                                    <div class="employer-job-info">
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <h4><?= htmlspecialchars($j['Title']) ?></h4>
                                            <?php 
                                                $isRemovedAdmin = !empty($j['IsRemovedByAdmin']);
                                                $isPendingReview = $isRemovedAdmin && (!empty($j['AdminReviewStatus']) && $j['AdminReviewStatus'] === 'pending_review');
                                                $isJobPaused = empty($j['IsActive']) && !$isRemovedAdmin;
                                                $isJobExpired = !empty($j['ExpiresAt']) && strtotime($j['ExpiresAt']) <= time(); 
                                            ?>
                                            <?php if ($isPendingReview): ?>
                                                <span class="job-status-pill" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; background: rgba(59, 130, 246, 0.15); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.3); font-weight: 700;">🔄 قيد مراجعة الإدارة</span>
                                            <?php elseif ($isRemovedAdmin): ?>
                                                <span class="job-status-pill" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; background: rgba(239, 68, 68, 0.18); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.4); font-weight: 700;">🚫 معطلة بقرار من الإدارة</span>
                                            <?php elseif ($isJobPaused): ?>
                                                <span class="job-status-pill" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); font-weight: 600;">⏸️ التقديم متوقف</span>
                                            <?php elseif ($isJobExpired): ?>
                                                <span class="job-status-pill" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); font-weight: 600;">⛔ منتهية الصلاحية</span>
                                            <?php else: ?>
                                                <span class="job-status-pill" style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; background: rgba(74, 140, 92, 0.15); color: #4a8c5c; border: 1px solid rgba(74, 140, 92, 0.3); font-weight: 600;">🟢 نشطة</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="employer-job-meta">
                                            <span><?= htmlspecialchars($j['JobType'] ?? '') ?></span> · 
                                            <span><?= htmlspecialchars($j['City'] ?? 'عن بعد') ?></span> · 
                                            <span>نُشرت: <?= date('d/m/Y', strtotime($j['PostedAt'])) ?></span>
                                        </div>
                                        <div class="job-timer-box mt-1">
                                            <?php if ($isPendingReview): ?>
                                                <div style="background: rgba(59, 130, 246, 0.08); border: 1px dashed rgba(59, 130, 246, 0.35); border-radius: 6px; padding: 6px 10px; margin-top: 4px;">
                                                    <span class="timer-badge" style="background: rgba(59, 130, 246, 0.2); color: #1d4ed8; font-weight: 600;">🔄 تم تعديل الوظيفة وبانتظار موافقة الإدارة</span>
                                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 3px;">
                                                        أُرسلت التعديلات بتاريخ <?= date('d/m/Y h:i A', strtotime($j['ResubmittedAt'] ?? $j['UpdatedAt'])) ?>. سيتم تفعيل الوظيفة فور اعتمادها.
                                                    </div>
                                                </div>
                                            <?php elseif ($isRemovedAdmin): ?>
                                                <div style="background: rgba(239, 68, 68, 0.08); border: 1px dashed rgba(239, 68, 68, 0.35); border-radius: 6px; padding: 8px 12px; margin-top: 4px;">
                                                    <span class="timer-badge expired" style="display: inline-block; margin-bottom: 2px;">🚫 سبب الرفض / التعطيل من الإدارة:</span>
                                                    <?php if (!empty($j['AdminRemoveReason'])): ?>
                                                        <div style="font-size: 0.85rem; color: #b91c1c; font-weight: 600; margin-top: 2px;">
                                                            <?= htmlspecialchars($j['AdminRemoveReason']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                                        يمكنك تعديل بيانات الوظيفة وتحديد مدة العرض المقترحة وإعادة إرسالها للمراجعة.
                                                    </div>
                                                </div>
                                            <?php elseif ($isJobPaused): ?>
                                                <span class="timer-badge expired">⛔ التقديم متوقف (العداد: 00:00)</span>
                                            <?php elseif (!empty($j['ExpiresAt'])): ?>
                                                <?php if ($isJobExpired): ?>
                                                    <span class="timer-badge expired">⛔ انتهى التقديم (<?= date('d/m/Y h:i A', strtotime($j['ExpiresAt'])) ?>)</span>
                                                <?php else: ?>
                                                    <span class="timer-badge active job-countdown" data-expires="<?= htmlspecialchars(date('c', strtotime($j['ExpiresAt']))) ?>">
                                                        ⏳ متبقي: <strong class="countdown-text">جاري الحساب...</strong>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="timer-badge text-muted">♾️ إعلان مفتوح بدون موعد انتهاء</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="employer-job-stats" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <span class="applicant-count"><?= $j['ApplicationsCount'] ?? 0 ?> متقدم</span>
                                        <a href="/employer/applicants?job_id=<?= $j['JobID'] ?>" class="btn btn-outline btn-sm">عرض المتقدمين</a>
                                        <?php if ($isPendingReview): ?>
                                            <a href="/employer/jobs/edit?id=<?= $j['JobID'] ?>" class="btn btn-outline btn-sm" title="تعديل البيانات أو مدة العرض قبل اعتمادها">
                                                ✏️ تعديل مجدداً
                                            </a>
                                        <?php elseif ($isRemovedAdmin): ?>
                                            <a href="/employer/jobs/edit?id=<?= $j['JobID'] ?>" class="btn btn-primary btn-sm" style="font-weight: 600;" title="تعديل الوظيفة وإعادة إرسالها للإدارة">
                                                ✏️ تعديل وإعادة الإرسال
                                            </a>
                                        <?php elseif ($isJobPaused || $isJobExpired): ?>
                                            <button type="button" class="btn btn-primary btn-sm toggle-job-btn" onclick="openReopenModal(<?= $j['JobID'] ?>, '<?= htmlspecialchars(addslashes($j['Title'])) ?>', '<?= htmlspecialchars($j['ExpiresAt'] ? date('d/m/Y h:i A', strtotime($j['ExpiresAt'])) : '') ?>', this)" data-active="0" data-expired="1" title="تحديد موعد جديد وإعادة فتح التقديم">
                                                🔄 إعادة فتح التقديم
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline btn-sm toggle-job-btn" onclick="toggleEmployerJob(<?= $j['JobID'] ?>, this)" data-active="1" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.4);" title="إيقاف استقبال طلبات جديدة وتصفير العداد">
                                                ⏸️ إيقاف التقديم
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: 32px 0;">
                                <p class="text-muted">لم تنشر أي وظائف حتى الآن</p>
                                <a href="/employer/post-job" class="btn btn-primary btn-sm mt-2">انشر وظيفتك الأولى</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent CVs Received -->
                <div class="card">
                    <h2 class="card-title">آخر المتقدمين</h2>
                    <p class="card-desc">أحدث الطلبات التي وصلت لوظائفك</p>

                    <div class="cv-received-list">
                        <?php if (!empty($applications)): ?>
                            <?php foreach (array_slice($applications, 0, 4) as $a): ?>
                                <div class="cv-received-item">
                                    <div class="user-avatar"><?= mb_substr($a['FullName'] ?? 'م', 0, 1) ?></div>
                                    <div>
                                        <strong><?= htmlspecialchars($a['FullName'] ?? 'متقدم') ?></strong>
                                        <p class="text-muted" style="font-size: 0.8rem;">
                                            <?= htmlspecialchars($a['JobTitle'] ?? '') ?> · <?= date('d/m/Y', strtotime($a['AppliedAt'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center" style="padding: 24px 0; font-size: 0.85rem;">لا توجد طلبات توظيف مستلمة حتى الآن</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <h2 class="card-title">إجراءات سريعة</h2>
                    <p class="card-desc">أكشن سريع من هنا</p>
                    <div class="quick-actions">
                        <a href="/employer/post-job" class="quick-action-btn">
                            <span>📝</span> نشر وظيفة
                        </a>
                        <a href="/employer/applicants" class="quick-action-btn">
                            <span>👥</span> كل المتقدمين
                        </a>
                        <a href="/employer/applicants?status=shortlisted" class="quick-action-btn" style="border-color: rgba(245, 158, 11, 0.4);">
                            <span>⭐</span> القائمة القصيرة
                        </a>
                        <a href="/employer/interview" class="quick-action-btn">
                            <span>📅</span> جدولة مقابلة
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>

</div>

<!-- Reopen Job Modal -->
<div class="modal-overlay" id="reopenJobModal" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-card" style="max-width: 500px; width: 92%; text-align: right; border-radius: var(--radius-lg); padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h2 style="font-size: 1.25rem; margin: 0;">📅 إعادة فتح التقديم</h2>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeReopenModal()" style="font-size: 1.3rem; padding: 0 8px; cursor: pointer;">&times;</button>
        </div>
        <p class="text-muted" id="reopenJobPrompt" style="font-size: 0.9rem; margin-bottom: 16px;">
            انتهى موعد التقديم السابق على هذه الوظيفة. اختر موعداً جديداً لاستئناف استقبال طلبات المتقدمين:
        </p>

        <!-- Quick Duration Options -->
        <label style="font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; display: block;">تمديد سريع ومباشر:</label>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 16px;">
            <button type="button" class="btn btn-outline btn-sm quick-extend-btn" onclick="setQuickDeadline(1, 'minutes')" style="font-size: 0.82rem; background: rgba(245, 158, 11, 0.1); border-color: #f59e0b; color: #b45309; font-weight: 600;">⚡ +1 دقيقة (تجربة سريعة)</button>
            <button type="button" class="btn btn-outline btn-sm quick-extend-btn" onclick="setQuickDeadline(10, 'minutes')" style="font-size: 0.82rem;">⏱️ +10 دقائق (تجربة)</button>
            <button type="button" class="btn btn-outline btn-sm quick-extend-btn" onclick="setQuickDeadline(1, 'hours')" style="font-size: 0.82rem;">⏳ +ساعة واحدة</button>
            <button type="button" class="btn btn-outline btn-sm quick-extend-btn" onclick="setQuickDeadline(24, 'hours')" style="font-size: 0.82rem;">📅 +24 ساعة (يوم)</button>
            <button type="button" class="btn btn-outline btn-sm quick-extend-btn" onclick="setQuickDeadline(7, 'days')" style="font-size: 0.82rem; grid-column: span 2;">🗓️ +أسبوع كامل</button>
        </div>

        <!-- Custom Datetime Option -->
        <div class="form-group mb-3">
            <label for="reopenNewExpiry" style="font-weight: 600; font-size: 0.85rem;">أو حدد تاريخ وساعة معينة بدقة:</label>
            <input 
                type="datetime-local" 
                id="reopenNewExpiry" 
                class="form-control" 
                dir="ltr"
                min="<?= date('Y-m-d\TH:i') ?>"
                style="direction: ltr; text-align: right;"
                required
            >
        </div>

        <input type="hidden" id="reopenJobId" value="">

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeReopenModal()">إلغاء</button>
            <button type="button" class="btn btn-primary btn-sm" id="confirmReopenBtn" onclick="submitReopenJob()">
                ✅ حفظ وفتح التقديم
            </button>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
