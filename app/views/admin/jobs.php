<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - إدارة الوظائف">
    <title>العواطلية - إدارة الوظائف</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="app-layout">

    <!-- Navbar -->
    <nav class="navbar admin-navbar">
        <a href="/admin/dashboard" class="navbar-brand">
            <img src="/public/images/logo.jpg" alt="العواطلية">
            <span>العواطلية <small style="font-size: 0.6rem; color: var(--danger);">ADMIN</small></span>
        </a>
        <div class="navbar-links">
            <a href="/admin/dashboard" class="nav-link">لوحة التحكم</a>
            <a href="/admin/users" class="nav-link">المستخدمين</a>
            <a href="/admin/jobs" class="nav-link active">الوظائف</a>
        </div>
        <div class="navbar-actions">
            <a href="/admin/settings" class="user-pill admin-pill">
                <div class="user-avatar admin-avatar">🔐</div>
                <span>مدير النظام</span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container">

            <div class="page-header">
                <h1 class="page-title">📋 إدارة الوظائف</h1>
                <p class="page-subtitle">مراجعة وإزالة إعلانات الوظائف</p>
            </div>

            <!-- Stats overview -->
            <div class="stats-row mb-4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card" style="cursor: pointer;" onclick="document.getElementById('filterJobStatus').value=''; filterAdminJobsTable();">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= $jobStats['TotalJobs'] ?? count($jobs) ?></div>
                    <div class="stat-label">إجمالي الوظائف</div>
                </div>
                <div class="stat-card" style="cursor: pointer;" onclick="document.getElementById('filterJobStatus').value='pending_review'; filterAdminJobsTable();">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #2563eb;">🔄</div>
                    <div class="stat-value" style="color: #2563eb;"><?= $jobStats['PendingReviewJobs'] ?? 0 ?></div>
                    <div class="stat-label">بانتظار المراجعة (تعديلات)</div>
                </div>
                <div class="stat-card" style="cursor: pointer;" onclick="document.getElementById('filterJobStatus').value='active'; filterAdminJobsTable();">
                    <div class="stat-icon" style="background: rgba(74, 140, 92, 0.15); color: #4a8c5c;">🟢</div>
                    <div class="stat-value" style="color: #4a8c5c;"><?= $jobStats['ActiveJobs'] ?? 0 ?></div>
                    <div class="stat-label">نشطة ومتاحة</div>
                </div>
                <div class="stat-card" style="cursor: pointer;" onclick="document.getElementById('filterJobStatus').value='removed'; filterAdminJobsTable();">
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">🚫</div>
                    <div class="stat-value" style="color: #ef4444;"><?= $jobStats['RemovedJobs'] ?? 0 ?></div>
                    <div class="stat-label">معطلة من الإدارة</div>
                </div>
            </div>

            <!-- Filters & Stats -->
            <div class="filters-bar mb-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div class="filter-group" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <div class="select-wrapper">
                        <select id="filterJobStatus" class="form-select form-select-sm" onchange="filterAdminJobsTable()">
                            <option value="">جميع الحالات</option>
                            <option value="pending_review">🔄 بانتظار المراجعة بعد التعديل</option>
                            <option value="active">🟢 نشطة ومتاحة</option>
                            <option value="removed">🚫 معطلة من الإدارة</option>
                            <option value="paused">⏸️ موقوفة (صاحب العمل)</option>
                            <option value="expired">⛔ منتهية الصلاحية</option>
                        </select>
                    </div>
                    <input type="text" class="form-control form-control-sm" id="searchAdminJobs" placeholder="🔍 ابحث بالوظيفة، الشركة، أو الكود..." style="min-width: 260px;" oninput="filterAdminJobsTable()">
                </div>
                <div id="adminJobsCountBadge" class="badge" style="background: rgba(255,255,255,0.06); padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; border: 1px solid var(--border);">
                    المعروض: <strong id="visibleJobsCount"><?= count($jobs) ?></strong> من أصل <strong><?= count($jobs) ?></strong> وظيفة
                </div>
            </div>

            <!-- Jobs Table -->
            <div class="card card-full">
                <div class="admin-table-wrapper">
                    <table class="admin-table" id="adminJobsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الوظيفة</th>
                                <th>الشركة / صاحب العمل</th>
                                <th>النوع</th>
                                <th>المتقدمين</th>
                                <th>تاريخ النشر</th>
                                <th>انتهاء التقديم</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jobs)): ?>
                                <?php foreach ($jobs as $j): ?>
                                    <?php
                                        $isRemovedAdmin = !empty($j['IsRemovedByAdmin']);
                                        $isPendingReview = $isRemovedAdmin && (!empty($j['AdminReviewStatus']) && $j['AdminReviewStatus'] === 'pending_review');
                                        $isExpired = !empty($j['ExpiresAt']) && strtotime($j['ExpiresAt']) <= time();
                                        $isPaused = empty($j['IsActive']) && !$isRemovedAdmin;
                                        $isActive = !empty($j['IsActive']) && !$isRemovedAdmin && !$isExpired;

                                        $filterCat = 'active';
                                        $rowClass = '';
                                        if ($isPendingReview) {
                                            $filterCat = 'pending_review';
                                            $rowClass = 'pending-review-row';
                                        } elseif ($isRemovedAdmin) {
                                            $filterCat = 'removed';
                                            $rowClass = 'removed-row';
                                        } elseif ($isPaused) {
                                            $filterCat = 'paused';
                                            $rowClass = 'paused-row';
                                        } elseif ($isExpired) {
                                            $filterCat = 'expired';
                                            $rowClass = 'expired-row';
                                        }
                                    ?>
                                    <tr id="admin-job-row-<?= $j['JobID'] ?>" class="<?= $rowClass ?>" data-status-cat="<?= $filterCat ?>" data-title="<?= htmlspecialchars(mb_strtolower($j['Title'])) ?>" data-company="<?= htmlspecialchars(mb_strtolower($j['CompanyName'] ?? '')) ?>" data-employer="<?= htmlspecialchars(mb_strtolower($j['EmployerName'] ?? '')) ?>" data-job-id="<?= $j['JobID'] ?>">
                                        <td><strong><?= $j['JobID'] ?></strong></td>
                                        <td>
                                            <strong><?= htmlspecialchars($j['Title']) ?></strong>
                                            <?php if (!empty($j['City'])): ?>
                                                <small class="text-muted" style="display: block; font-size: 0.78rem;">📍 <?= htmlspecialchars($j['City']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span><?= htmlspecialchars($j['CompanyName'] ?? $j['EmployerName'] ?? 'شركة') ?></span>
                                            <?php if (!empty($j['EmployerName']) && $j['EmployerName'] !== ($j['CompanyName'] ?? '')): ?>
                                                <small class="text-muted" style="display: block; font-size: 0.78rem;">👤 <?= htmlspecialchars($j['EmployerName']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="tag tag-sm"><?= htmlspecialchars($j['JobType'] ?? '') ?></span></td>
                                        <td>
                                            <span class="badge" style="background: rgba(196,113,43,0.15); color: #c4712b; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                                <?= $j['ApplicationsCount'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($j['PostedAt'])) ?></td>
                                        <td class="job-expiry-cell">
                                            <?php if (!empty($j['ExpiresAt'])): ?>
                                                <span style="font-size: 0.85rem; <?= $isExpired ? 'color: var(--danger); font-weight: 600;' : '' ?>">
                                                    <?= date('d/m/Y h:i A', strtotime($j['ExpiresAt'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted" style="font-size: 0.82rem;">مفتوح</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="job-status-cell">
                                            <?php if ($isPendingReview): ?>
                                                <span class="status-dot" style="background: #2563eb;"></span> <strong style="color: #2563eb;">بانتظار المراجعة</strong>
                                                <div class="text-muted" style="font-size: 0.74rem; margin-top: 2px;">
                                                    عدلها صاحب العمل
                                                    <?php if (!empty($j['ResubmittedAt'])): ?>
                                                        <br><span style="font-size: 0.7rem;">(<?= date('d/m H:i', strtotime($j['ResubmittedAt'])) ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($isRemovedAdmin): ?>
                                                <span class="status-dot banned"></span> <strong style="color: var(--danger);">معطلة من الإدارة</strong>
                                                <?php if (!empty($j['AdminRemoveReason'])): ?>
                                                    <div class="admin-remove-reason-text text-muted" style="font-size: 0.75rem; margin-top: 3px; max-width: 200px; line-height: 1.3;" title="<?= htmlspecialchars($j['AdminRemoveReason']) ?>">
                                                        السبب: <?= htmlspecialchars(mb_substr($j['AdminRemoveReason'], 0, 45)) ?><?= mb_strlen($j['AdminRemoveReason']) > 45 ? '...' : '' ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif ($isPaused): ?>
                                                <span class="status-dot" style="background: #f59e0b;"></span> <span style="color: #f59e0b; font-weight: 600;">موقوفة (صاحب العمل)</span>
                                            <?php elseif ($isExpired): ?>
                                                <span class="status-dot banned"></span> <span class="text-muted">منتهية الصلاحية</span>
                                            <?php else: ?>
                                                <span class="status-dot active"></span> <span style="color: var(--success); font-weight: 600;">نشطة ومتاحة</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="job-actions-cell">
                                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                                <?php if ($isPendingReview): ?>
                                                    <button type="button" class="btn btn-outline btn-sm action-btn" onclick="openAdminReviewModal(<?= $j['JobID'] ?>)" style="color: #2563eb; border-color: #2563eb; padding: 4px 8px; font-size: 0.8rem;" title="فحص بيانات وتعديلات الوظيفة">
                                                        📋 فحص
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm action-btn" onclick="openAdminActivateModal(<?= $j['JobID'] ?>, '<?= htmlspecialchars(addslashes($j['Title'])) ?>', '<?= !empty($j['ExpiresAt']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($j['ExpiresAt']))) : '' ?>', this)" style="padding: 4px 8px; font-size: 0.8rem;" title="قبول وتحديد مدة العرض">
                                                        ✅ قبول
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm action-btn" onclick="openAdminRemoveModal(<?= $j['JobID'] ?>, '<?= htmlspecialchars(addslashes($j['Title'])) ?>', this)" style="padding: 4px 8px; font-size: 0.8rem;" title="رفض التعديلات مع ذكر السبب">
                                                        ❌ رفض
                                                    </button>
                                                <?php elseif ($isActive): ?>
                                                    <button type="button" class="btn btn-danger btn-sm action-btn" onclick="openAdminRemoveModal(<?= $j['JobID'] ?>, '<?= htmlspecialchars(addslashes($j['Title'])) ?>', this)">
                                                        🗑️ تعطيل
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-success btn-sm action-btn" onclick="openAdminActivateModal(<?= $j['JobID'] ?>, '<?= htmlspecialchars(addslashes($j['Title'])) ?>', '<?= !empty($j['ExpiresAt']) && strtotime($j['ExpiresAt']) > time() ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($j['ExpiresAt']))) : '' ?>', this)">
                                                        ♻️ تفعيل
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center" style="padding: 32px;">لا توجد أي وظائف معروضة حتى الآن</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</div>

<!-- Admin Activate Modal (Duration Selection) -->
<div class="modal-overlay" id="adminActivateJobModal" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-card" style="max-width: 520px; width: 92%; text-align: right; border-radius: var(--radius-lg); padding: 26px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div class="modal-icon" style="color: var(--success); font-size: 1.4rem; margin: 0;">♻️</div>
                <h2 style="font-size: 1.25rem; margin: 0;">تفعيل الوظيفة وتحديد مدة العرض</h2>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeAdminActivateModal()" style="font-size: 1.3rem; padding: 0 8px; cursor: pointer;">&times;</button>
        </div>
        <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 12px;">
            اختر مدة ظهور الوظيفة للمتقدمين (الموعد النهائي للتقديم):
        </p>
        <div id="adminActivateTargetTitle" style="font-weight: 700; margin-bottom: 14px; background: rgba(74, 140, 92, 0.08); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(74, 140, 92, 0.2); color: #2e7d32; font-size: 0.95rem;">
            <!-- Target Job Title -->
        </div>
        <input type="hidden" id="adminActivateTargetId" value="">

        <!-- Quick Duration Options -->
        <label style="font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; display: block;">مدة سريعة:</label>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; margin-bottom: 16px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="setAdminActivateQuickDuration(1, 'minutes')" style="font-size: 0.82rem; background: rgba(245, 158, 11, 0.1); border-color: #f59e0b; color: #b45309; font-weight: 600;">⚡ +1 دقيقة (تجربة)</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="setAdminActivateQuickDuration(10, 'minutes')" style="font-size: 0.82rem;">⏱️ +10 دقائق</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="setAdminActivateQuickDuration(1, 'hours')" style="font-size: 0.82rem;">⏳ +ساعة واحدة</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="setAdminActivateQuickDuration(24, 'hours')" style="font-size: 0.82rem;">📅 +24 ساعة (يوم)</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="setAdminActivateQuickDuration(7, 'days')" style="font-size: 0.82rem;">🗓️ +أسبوع كامل</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="setAdminActivateQuickDuration(30, 'days')" style="font-size: 0.82rem;">🗓️ +30 يوم (شهر)</button>
        </div>

        <!-- Custom Datetime Option -->
        <div class="form-group mb-3">
            <label for="adminActivateExpiryInput" style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 6px;">أو حدد تاريخ وساعة معينة بدقة:</label>
            <input 
                type="datetime-local" 
                id="adminActivateExpiryInput" 
                class="form-control" 
                dir="ltr"
                min="<?= date('Y-m-d\TH:i') ?>"
                style="direction: ltr; text-align: right;"
            >
            <small class="text-muted" style="display: block; margin-top: 4px;">إذا تُرِك فارغاً، سيتم التفعيل تلقائياً لمدة 7 أيام من الآن.</small>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeAdminActivateModal()">إلغاء</button>
            <button type="button" class="btn btn-success btn-sm" id="confirmAdminActivateBtn" onclick="submitAdminActivateJob()">
                تأكيد التفعيل والعرض ✅
            </button>
        </div>
    </div>
</div>

<!-- Remove / Reject Modal -->
<div class="modal-overlay" id="removeJobModal" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-card" style="max-width: 500px; width: 92%; text-align: right; border-radius: var(--radius-lg); padding: 26px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div class="modal-icon" style="color: var(--danger); font-size: 1.4rem; margin: 0;">🗑️</div>
                <h2 style="font-size: 1.25rem; margin: 0;">تعطيل / رفض إعلان الوظيفة</h2>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeAdminRemoveModal()" style="font-size: 1.3rem; padding: 0 8px; cursor: pointer;">&times;</button>
        </div>
        <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 12px;">
            سيتم إيقاف ظهور هذه الوظيفة، وإشعار صاحب العمل بالسبب، مع إتاحة إمكانية تعديلها وإعادة إرسالها:
        </p>
        <div id="removeJobTargetTitle" style="font-weight: 700; margin-bottom: 14px; background: rgba(239, 68, 68, 0.08); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(239, 68, 68, 0.2); color: var(--danger); font-size: 0.95rem;">
            <!-- Target Job Title -->
        </div>
        <input type="hidden" id="removeJobTargetId" value="">
        <div class="form-group mb-3">
            <label for="removeReason" style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 6px;">سبب التعطيل / الرفض (يظهر لصاحب العمل لتصحيحه) *</label>
            <textarea id="removeReason" class="form-control form-textarea" rows="3" placeholder="اكتب سبب التعطيل بالتفصيل (مثلاً: الإعلان مخالف لشروط وسياسات النشر، أو يحتوي على بيانات اتصال مباشرة...)" style="font-size: 0.88rem;"></textarea>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeAdminRemoveModal()">إلغاء</button>
            <button type="button" class="btn btn-danger btn-sm" id="confirmRemoveBtn" onclick="submitAdminRemoveJob()">
                تأكيد التعطيل
            </button>
        </div>
    </div>
</div>

<!-- Admin Job Review Details Modal -->
<div class="modal-overlay" id="adminJobReviewModal" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-card" style="max-width: 680px; width: 94%; max-height: 90vh; overflow-y: auto; text-align: right; border-radius: var(--radius-lg); padding: 26px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.4rem;">📋</span>
                <h2 style="font-size: 1.25rem; margin: 0;">فحص ومراجعة إعلان الوظيفة</h2>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeAdminReviewModal()" style="font-size: 1.3rem; padding: 0 8px; cursor: pointer;">&times;</button>
        </div>

        <div id="reviewModalLoading" style="text-align: center; padding: 30px;">
            <span class="spinner" style="display:inline-block;width:24px;height:24px;border:3px solid var(--accent);border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span>
            <p class="text-muted mt-2" style="font-size: 0.9rem;">جاري تحميل تفاصيل الوظيفة...</p>
        </div>

        <div id="reviewModalContent" style="display: none;">
            <div style="background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 8px; padding: 14px; margin-bottom: 14px;">
                <h3 id="revTitle" style="font-size: 1.15rem; margin: 0 0 6px 0; color: var(--accent);"></h3>
                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; gap: 12px; flex-wrap: wrap;">
                    <span>🏢 الشركة: <strong id="revCompany" style="color: var(--text);"></strong></span>
                    <span>👤 صاحب العمل: <span id="revEmployer"></span></span>
                    <span>📍 المدينة: <span id="revCity"></span></span>
                    <span>💼 النوع: <span id="revType"></span></span>
                    <span>📊 الخبرة: <span id="revLevel"></span></span>
                </div>
            </div>

            <!-- Notes from Employer if any -->
            <div id="revEmployerNotesBox" style="display: none; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                <strong style="color: #2563eb; font-size: 0.88rem; display: block; margin-bottom: 4px;">💬 رسالة وتوضيح من صاحب العمل حول التعديلات:</strong>
                <p id="revEmployerNotes" style="margin: 0; font-size: 0.88rem; line-height: 1.5; color: var(--text);"></p>
            </div>

            <!-- Previous Remove Reason if any -->
            <div id="revPrevReasonBox" style="display: none; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                <strong style="color: #dc2626; font-size: 0.85rem; display: block; margin-bottom: 4px;">🚫 سبب التعطيل السابق من الإدارة:</strong>
                <p id="revPrevReason" style="margin: 0; font-size: 0.85rem; color: #b91c1c;"></p>
            </div>

            <!-- Requested Expiry -->
            <div style="font-size: 0.85rem; margin-bottom: 14px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 6px; padding: 8px 12px;">
                ⏱️ الموعد النهائي المقترح للتقديم: <strong id="revExpiry" style="color: #b45309;"></strong>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 14px;">
                <strong style="font-size: 0.9rem; display: block; margin-bottom: 4px;">وصف الوظيفة:</strong>
                <div id="revDescription" style="font-size: 0.88rem; line-height: 1.6; background: rgba(0,0,0,0.03); padding: 10px; border-radius: 6px; max-height: 160px; overflow-y: auto; white-space: pre-wrap;"></div>
            </div>

            <!-- Requirements -->
            <div style="margin-bottom: 14px;">
                <strong style="font-size: 0.9rem; display: block; margin-bottom: 4px;">المتطلبات:</strong>
                <div id="revRequirements" style="font-size: 0.88rem; line-height: 1.6; background: rgba(0,0,0,0.03); padding: 10px; border-radius: 6px; max-height: 140px; overflow-y: auto; white-space: pre-wrap;"></div>
            </div>

            <!-- Skills -->
            <div style="margin-bottom: 18px;" id="revSkillsRow">
                <strong style="font-size: 0.9rem; display: block; margin-bottom: 4px;">المهارات المطلوبة:</strong>
                <div id="revSkills" style="display: flex; gap: 6px; flex-wrap: wrap;"></div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 14px; margin-top: 14px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeAdminReviewModal()">إغلاق</button>
                <button type="button" class="btn btn-danger btn-sm" id="revRejectBtn">❌ رفض مجدداً</button>
                <button type="button" class="btn btn-success btn-sm" id="revApproveBtn">✅ تفعيل وتحديد المدة</button>
            </div>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
