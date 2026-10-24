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
                                <div class="employer-job-item">
                                    <div class="employer-job-info">
                                        <h4><?= htmlspecialchars($j['Title']) ?></h4>
                                        <div class="employer-job-meta">
                                            <span><?= htmlspecialchars($j['JobType'] ?? '') ?></span> · 
                                            <span><?= htmlspecialchars($j['City'] ?? 'عن بعد') ?></span> · 
                                            <span><?= date('d/m/Y', strtotime($j['PostedAt'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="employer-job-stats">
                                        <span class="applicant-count"><?= $j['ApplicationsCount'] ?? 0 ?> متقدم</span>
                                        <a href="/employer/applicants?job_id=<?= $j['JobID'] ?>" class="btn btn-outline btn-sm">عرض المتقدمين</a>
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
                            <span>👥</span> عرض كل المتقدمين
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

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
