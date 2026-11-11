<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - عرض المتقدمين على الوظائف">
    <title>العواطلية - المتقدمين</title>
    <link rel="stylesheet" href="/public/css/style.css?v=<?= time() ?>">
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
            <a href="/employer/applicants" class="nav-link active">المتقدمين</a>
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
        <div class="container">

            <div class="page-header">
                <h1 class="page-title">المتقدمين على الوظائف</h1>
                <p class="page-subtitle">شوف مين قدم على وظائفك واختار المناسب</p>
            </div>

            <?php
            $currentStatus = $_GET['status'] ?? 'all';
            $countAll = count($applications ?? []);
            $countShortlisted = count(array_filter($applications ?? [], fn($x) => ($x['Status'] ?? '') === 'shortlisted'));
            $countPending = count(array_filter($applications ?? [], fn($x) => ($x['Status'] ?? '') === 'pending'));
            $countInterview = count(array_filter($applications ?? [], fn($x) => in_array($x['Status'] ?? '', ['interview', 'accepted'])));
            $countRejected = count(array_filter($applications ?? [], fn($x) => ($x['Status'] ?? '') === 'rejected'));
            ?>

            <!-- Filters Bar -->
            <div class="filters-bar mb-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select id="filterJob" class="form-select form-select-sm" onchange="var s = new URLSearchParams(window.location.search); if(this.value){ s.set('job_id', this.value); } else { s.delete('job_id'); } location.href = '/employer/applicants?' + s.toString();">
                            <option value="">كل الوظائف</option>
                            <?php if (!empty($jobs)): ?>
                                <?php foreach ($jobs as $jb): ?>
                                    <option value="<?= $jb['JobID'] ?>" <?= ($jobId == $jb['JobID']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($jb['Title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Status Filter Buttons (Shortlist, etc.) -->
                <div class="applicants-status-tabs" style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm status-filter-btn <?= $currentStatus === 'all' ? 'btn-primary' : 'btn-outline' ?>" data-filter="all">
                        الكل (<span id="count-all"><?= $countAll ?></span>)
                    </button>
                    <button type="button" class="btn btn-sm status-filter-btn <?= $currentStatus === 'shortlisted' ? 'btn-success' : 'btn-outline' ?>" data-filter="shortlisted" id="shortlistFilterBtn" style="border-color: #f59e0b; color: <?= $currentStatus === 'shortlisted' ? '#fff' : '#f59e0b' ?>; background: <?= $currentStatus === 'shortlisted' ? '#f59e0b' : 'transparent' ?>; font-weight: 700;">
                        ⭐ القائمة القصيرة (<span id="count-shortlisted"><?= $countShortlisted ?></span>)
                    </button>
                    <button type="button" class="btn btn-sm status-filter-btn <?= $currentStatus === 'pending' ? 'btn-primary' : 'btn-outline' ?>" data-filter="pending">
                        ⏳ قيد المراجعة (<span id="count-pending"><?= $countPending ?></span>)
                    </button>
                    <button type="button" class="btn btn-sm status-filter-btn <?= $currentStatus === 'interview' ? 'btn-primary' : 'btn-outline' ?>" data-filter="interview">
                        📞 المقابلات (<span id="count-interview"><?= $countInterview ?></span>)
                    </button>
                    <button type="button" class="btn btn-sm status-filter-btn <?= $currentStatus === 'rejected' ? 'btn-primary' : 'btn-outline' ?>" data-filter="rejected">
                        ❌ المرفوضين (<span id="count-rejected"><?= $countRejected ?></span>)
                    </button>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div id="bulkActionsToolbar" class="card mb-3" style="display: none; padding: 14px 20px; background: rgba(196, 113, 43, 0.08); border: 1px solid rgba(196, 113, 43, 0.3); border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer; margin: 0;">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllApplicants(this)">
                            <span>تحديد الكل</span>
                        </label>
                        <span class="badge" style="background: #c4712b; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem;">
                            تم تحديد <strong id="selectedApplicantsCount">0</strong> متقدم
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openBulkInterviewModal()" style="background: #c4712b; border-color: #c4712b; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                            📢 إرسال تفاصيل القبول والمقابلة للمحددين
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="clearSelectedApplicants()">
                            إلغاء التحديد
                        </button>
                    </div>
                </div>
            </div>

            <!-- Applicants List -->
            <div class="applicants-list">

                <!-- Filter Empty State -->
                <div id="noFilteredApplicants" class="card text-center" style="display: none; padding: 48px 16px; text-align: center;">
                    <span style="font-size: 3rem;">⭐</span>
                    <h3 class="mt-2" id="noFilteredTitle">لا يوجد متقدمين في هذا القسم</h3>
                    <p class="text-muted mt-1" id="noFilteredDesc">يمكنك إضافة متقدمين للقائمة القصيرة بالضغط على زر "⭐ قائمة قصيرة" على أي طلب متقدم.</p>
                </div>

                <?php if (!empty($applications)): ?>
                    <?php
                    $statusClasses = [
                        'pending' => 'status-pending',
                        'reviewed' => 'status-reviewed',
                        'shortlisted' => 'status-interview',
                        'interview' => 'status-interview',
                        'accepted' => 'status-interview',
                        'rejected' => 'status-rejected'
                    ];
                    $statusLabels = [
                        'pending' => '⏳ قيد المراجعة',
                        'reviewed' => '👁️ تمت المراجعة',
                        'shortlisted' => '⭐ القائمة القصيرة',
                        'interview' => '📞 مقابلة',
                        'accepted' => '🎉 مقبول',
                        'rejected' => '❌ مرفوض'
                    ];
                    ?>
                    <?php foreach ($applications as $a): ?>
                        <div class="applicant-card" id="applicant-card-<?= $a['ApplicationID'] ?>" data-app-id="<?= $a['ApplicationID'] ?>" data-status="<?= htmlspecialchars($a['Status'] ?? 'pending') ?>">
                            <div class="applicant-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <?php if (!in_array($a['Status'] ?? '', ['rejected', 'interview', 'accepted'])): ?>
                                        <label class="applicant-checkbox-label" style="display: flex; align-items: center; margin: 0; cursor: pointer;" title="تحديد لإرسال تفاصيل القبول والمقابلة">
                                            <input type="checkbox" class="applicant-checkbox" value="<?= $a['ApplicationID'] ?>" data-name="<?= htmlspecialchars($a['FullName'] ?? '') ?>" onchange="updateSelectedApplicants()">
                                        </label>
                                    <?php endif; ?>
                                    <div class="applicant-info">
                                        <div class="user-avatar" style="width: 48px; height: 48px; font-size: 1.1rem;" <?= !empty($a['ProfilePicture']) ? 'title="اضغط لتكبير الصورة"' : '' ?>>
                                            <?php if (!empty($a['ProfilePicture'])): ?>
                                                <img src="<?= htmlspecialchars($a['ProfilePicture']) ?>" alt="<?= htmlspecialchars($a['FullName'] ?? '') ?>" onerror="this.onerror=null; this.parentElement.innerHTML='<?= mb_substr($a['FullName'] ?? 'م', 0, 1) ?>';">
                                            <?php else: ?>
                                                <?= mb_substr($a['FullName'] ?? 'م', 0, 1) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h3><?= htmlspecialchars($a['FullName'] ?? 'متقدم') ?></h3>
                                            <p class="text-muted">
                                                قدم على: <?= htmlspecialchars($a['JobTitle'] ?? ($job['Title'] ?? '')) ?> · <?= date('d/m/Y', strtotime($a['AppliedAt'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <span class="status-badge <?= $statusClasses[$a['Status'] ?? 'pending'] ?? 'status-pending' ?>">
                                    <?= $statusLabels[$a['Status'] ?? 'pending'] ?? $a['Status'] ?>
                                </span>
                            </div>

                            <div class="applicant-details">
                                <?php if (!empty($a['CVFile'])): ?>
                                    <div class="applicant-cv-preview">
                                        <span>📄</span>
                                        <a href="/public/uploads/cvs/<?= htmlspecialchars($a['CVFile']) ?>" target="_blank" class="cv-download-link"><?= htmlspecialchars($a['CVFile']) ?></a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($a['CoverLetter'])): ?>
                                    <div class="applicant-cover-letter mt-2">
                                        <strong>ليه الوظيفة دي مناسبة ليه:</strong>
                                        <p><?= nl2br(htmlspecialchars($a['CoverLetter'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="applicant-actions mt-3" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <?php if (($a['Status'] ?? '') === 'rejected'): ?>
                                    <span class="action-status-label" style="color: var(--danger); font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">
                                        ❌ تم رفض هذا الطلب
                                    </span>
                                <?php elseif (in_array($a['Status'] ?? '', ['interview', 'accepted'])): ?>
                                    <span class="action-status-label" style="color: #4a8c5c; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">
                                        📅 تم قبول الطلب وتحديد المقابلة
                                    </span>
                                    <?php if (!empty($a['InterviewDate'])): ?>
                                        <span style="font-size: 0.82rem; color: #4a8c5c; background: rgba(74, 140, 92, 0.12); border: 1px solid rgba(74, 140, 92, 0.3); padding: 3px 9px; border-radius: 6px; font-weight: 600;">
                                            ⏰ <?= Mailer::formatArabicDateTime($a['InterviewDate']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <a href="/employer/interview?application_id=<?= $a['ApplicationID'] ?>" class="btn btn-outline btn-sm" style="font-size: 0.8rem;">
                                        ✏️ تعديل المقابلة
                                    </a>
                                <?php else: ?>
                                    <?php $isShortlisted = (($a['Status'] ?? '') === 'shortlisted'); ?>
                                    <?php if ($isShortlisted): ?>
                                        <button type="button" class="btn btn-sm shortlist-toggle-btn" onclick="toggleShortlist(<?= $a['ApplicationID'] ?>, this)" data-shortlisted="1" style="background: #f59e0b; border-color: #f59e0b; color: #fff; font-weight: 700;">
                                            ★ إزالة من القائمة القصيرة
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline btn-sm shortlist-toggle-btn" onclick="toggleShortlist(<?= $a['ApplicationID'] ?>, this)" data-shortlisted="0" style="border-color: #f59e0b; color: #f59e0b; font-weight: 700;">
                                            ⭐ إضافة للقائمة القصيرة
                                        </button>
                                    <?php endif; ?>
                                    <a href="/employer/interview?application_id=<?= $a['ApplicationID'] ?>" class="btn btn-primary btn-sm">📅 حدد مقابلة</a>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="rejectApplicant(<?= $a['ApplicationID'] ?>, this)" style="color: var(--danger);">❌ رفض</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card text-center" style="padding: 48px 16px; text-align: center;">
                        <span style="font-size: 3rem;">📭</span>
                        <h3 class="mt-2">لا يوجد متقدمين حتى الآن</h3>
                        <p class="text-muted mt-1">عندما يقدم أي باحث عن عمل على وظائفك ستظهر طلباتهم هنا فوراً</p>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </main>

</div>

<!-- Contact Modal -->
<div class="modal-overlay" id="contactModal" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon">📞</div>
        <h2>تواصل مع المتقدم</h2>
        <p class="text-muted mb-2">ابعت رسالة أو بيانات التواصل</p>
        <div class="form-group">
            <label for="contactMessage">رسالة</label>
            <textarea id="contactMessage" class="form-control form-textarea" rows="3" placeholder="اكتب رسالتك هنا..."></textarea>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 16px;">
            <button class="btn btn-primary" onclick="sendContact()">ابعت</button>
            <button class="btn btn-outline" onclick="closeModal('contactModal')">إلغاء</button>
        </div>
    </div>
</div>

<!-- Bulk Interview / Acceptance Modal -->
<div class="modal-overlay" id="bulkInterviewModal" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-card" style="max-width: 580px; width: 92%; text-align: right; border-radius: var(--radius-lg); padding: 26px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h2 style="font-size: 1.25rem; margin: 0;">📢 إرسال تفاصيل القبول والمقابلة للجميع</h2>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeBulkInterviewModal()" style="font-size: 1.3rem; padding: 0 8px; cursor: pointer;">&times;</button>
        </div>
        <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 14px;">
            سيتم إرسال نفس تفاصيل القبول والمقابلة وتحديث حالة جميع المتقدمين المحددين دفعة واحدة عبر البريد الإلكتروني والنظام:
        </p>

        <div id="bulkSelectedListSummary" style="background: rgba(255,255,255,0.04); border: 1px solid var(--border); padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 0.85rem; max-height: 80px; overflow-y: auto;">
            <!-- Filled dynamically -->
        </div>

        <form id="bulkInterviewForm" onsubmit="submitBulkInterview(event)">
            <div class="form-group mb-3">
                <label for="bulkInterviewDatetime" style="font-weight: 600; font-size: 0.85rem;">موعد المقابلة (تاريخ وساعة) *</label>
                <input 
                    type="datetime-local" 
                    id="bulkInterviewDatetime" 
                    class="form-control" 
                    dir="ltr"
                    min="<?= date('Y-m-d\TH:i') ?>"
                    required
                >
            </div>

            <div class="form-group mb-3">
                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 6px;">نوع المقابلة *</label>
                <div style="display: flex; gap: 16px;">
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="bulk_loc_type" value="online" checked onchange="toggleBulkLocationType('online')">
                        <span>🌐 أونلاين عبر الإنترنت</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="bulk_loc_type" value="onsite" onchange="toggleBulkLocationType('onsite')">
                        <span>🏢 حضورية بمقر الشركة</span>
                    </label>
                </div>
            </div>

            <div class="form-group mb-3" id="bulkOnlineField">
                <label for="bulkMeetingLink" style="font-weight: 600; font-size: 0.85rem;">رابط المقابلة (Google Meet / Zoom / Teams) *</label>
                <input 
                    type="url" 
                    id="bulkMeetingLink" 
                    class="form-control" 
                    placeholder="https://meet.google.com/xyz-abc-def" 
                    dir="ltr"
                >
            </div>

            <div class="form-group mb-3" id="bulkOnsiteField" style="display: none;">
                <label for="bulkLocation" style="font-weight: 600; font-size: 0.85rem;">عنوان ومقر المقابلة بالتفصيل *</label>
                <input 
                    type="text" 
                    id="bulkLocation" 
                    class="form-control" 
                    placeholder="مثلاً: القاهرة، التجمع الخامس، مبنى 12، الدور الثالث"
                >
            </div>

            <div class="form-group mb-3">
                <label for="bulkNotes" style="font-weight: 600; font-size: 0.85rem;">تفاصيل ورسالة القبول والمقابلة (تصل للمتقدمين جميعاً):</label>
                <textarea 
                    id="bulkNotes" 
                    class="form-control" 
                    rows="3" 
                    style="font-size: 0.88rem; line-height: 1.5;"
                    placeholder="اكتب هنا أي تفاصيل تخص المقابلة وما يجب تحضيره..."
                >يسعدنا إبلاغكم بقبول طلباتكم المبدئية لحضور المقابلة الشخصية. نرجو التواجد أو الانضمام في الموعد المحدد ومراجعة متطلبات الوظيفة. نتطلع للقائكم!</textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeBulkInterviewModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary btn-sm" id="submitBulkInterviewBtn" style="background: #c4712b; border-color: #c4712b;">
                    🚀 إرسال وتحديد المقابلة للجميع
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
