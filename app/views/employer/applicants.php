<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - عرض المتقدمين على الوظائف">
    <title>العواطلية - المتقدمين</title>
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
            <a href="/employer/applicants" class="nav-link active">المتقدمين</a>
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
                <h1 class="page-title">المتقدمين على الوظائف</h1>
                <p class="page-subtitle">شوف مين قدم على وظائفك واختار المناسب</p>
            </div>

            <!-- Job Filter -->
            <div class="filters-bar mb-3">
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select id="filterJob" class="form-select form-select-sm" onchange="if(this.value){location.href='/employer/applicants?job_id='+this.value;}else{location.href='/employer/applicants';}">
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
            </div>

            <!-- Applicants List -->
            <div class="applicants-list">

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
                        <div class="applicant-card" data-app-id="<?= $a['ApplicationID'] ?>">
                            <div class="applicant-header">
                                <div class="applicant-info">
                                    <div class="user-avatar" style="width: 48px; height: 48px; font-size: 1.1rem;">
                                        <?= mb_substr($a['FullName'] ?? 'م', 0, 1) ?>
                                    </div>
                                    <div>
                                        <h3><?= htmlspecialchars($a['FullName'] ?? 'متقدم') ?></h3>
                                        <p class="text-muted">
                                            قدم على: <?= htmlspecialchars($a['JobTitle'] ?? ($job['Title'] ?? '')) ?> · <?= date('d/m/Y', strtotime($a['AppliedAt'])) ?>
                                        </p>
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
                                        <strong>Cover Letter:</strong>
                                        <p><?= nl2br(htmlspecialchars($a['CoverLetter'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="applicant-actions mt-3">
                                <button class="btn btn-success btn-sm" onclick="updateApplicationStatus(<?= $a['ApplicationID'] ?>, 'shortlisted', this)">⭐ قائمة قصيرة</button>
                                <a href="/employer/interview?application_id=<?= $a['ApplicationID'] ?>" class="btn btn-primary btn-sm">📅 حدد مقابلة</a>
                                <button class="btn btn-ghost btn-sm" onclick="updateApplicationStatus(<?= $a['ApplicationID'] ?>, 'rejected', this)" style="color: var(--danger);">❌ رفض</button>
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

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
