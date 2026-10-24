<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - لوحة تحكم الأدمن">
    <title>العواطلية - لوحة تحكم الأدمن</title>
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
            <a href="/admin/dashboard" class="nav-link active">لوحة التحكم</a>
            <a href="/admin/users" class="nav-link">المستخدمين</a>
            <a href="/admin/jobs" class="nav-link">الوظائف</a>
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
                <h1 class="page-title">🔐 لوحة تحكم الأدمن</h1>
                <p class="page-subtitle">مراقبة النشاط وإدارة المنصة</p>
            </div>

            <!-- Stats -->
            <div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon">👤</div>
                    <div class="stat-value"><?= $stats['TotalUsers'] ?? 0 ?></div>
                    <div class="stat-label">مستخدمين (باحثين عن عمل)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-value"><?= $stats['TotalEmployers'] ?? 0 ?></div>
                    <div class="stat-label">أصحاب عمل</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= $stats['ActiveJobs'] ?? 0 ?></div>
                    <div class="stat-label">وظائف نشطة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?= $stats['TotalApplications'] ?? 0 ?></div>
                    <div class="stat-label">طلبات توظيف</div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="stats-row mb-3" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon">🚫</div>
                    <div class="stat-value" style="color: var(--danger);"><?= $stats['BannedUsers'] ?? 0 ?></div>
                    <div class="stat-label">محظورين</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value" style="color: var(--success);"><?= $stats['NewUsersThisWeek'] ?? 0 ?></div>
                    <div class="stat-label">مستخدمين جدد هذا الأسبوع</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💼</div>
                    <div class="stat-value" style="color: var(--accent);"><?= $stats['NewJobsThisWeek'] ?? 0 ?></div>
                    <div class="stat-label">وظائف جديدة هذا الأسبوع</div>
                </div>
            </div>

            <div class="dashboard-grid">

                <!-- Activity Log (Monitor Activity) -->
                <div class="card card-full">
                    <div class="card-header-row">
                        <h2 class="card-title">📊 سجل النشاط</h2>
                        <span class="text-muted" style="font-size: 0.8rem;">آخر 24 ساعة</span>
                    </div>
                    <p class="card-desc">مراقبة كل الأنشطة على المنصة</p>

                    <div class="activity-log">
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $act): ?>
                                <?php
                                $dotColor = 'dot-blue';
                                $actionName = strtolower($act['Action'] ?? '');
                                if (str_contains($actionName, 'register') || str_contains($actionName, 'apply')) {
                                    $dotColor = 'dot-green';
                                } elseif (str_contains($actionName, 'ban') || str_contains($actionName, 'delete')) {
                                    $dotColor = 'dot-red';
                                } elseif (str_contains($actionName, 'enhancement') || str_contains($actionName, 'cv')) {
                                    $dotColor = 'dot-orange';
                                }
                                ?>
                                <div class="activity-item">
                                    <div class="activity-dot <?= $dotColor ?>"></div>
                                    <div class="activity-content">
                                        <strong><?= htmlspecialchars($act['FullName'] ?? 'مستخدم') ?></strong>
                                        <?= htmlspecialchars($act['Details'] ?? $act['Action'] ?? '') ?>
                                        <span class="activity-time"><?= date('d/m/Y H:i', strtotime($act['CreatedAt'])) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: 32px 0; text-align: center;">
                                <span style="font-size: 2.2rem;">📋</span>
                                <p class="text-muted mt-2" style="font-size: 0.9rem;">لا توجد أنشطة مسجلة حتى الآن</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Admin Actions -->
                <div class="card">
                    <h2 class="card-title">إجراءات سريعة</h2>
                    <p class="card-desc">إدارة المنصة</p>
                    <div class="quick-actions">
                        <a href="/admin/users" class="quick-action-btn">
                            <span>👥</span> إدارة المستخدمين
                        </a>
                        <a href="/admin/jobs" class="quick-action-btn">
                            <span>📋</span> إدارة الوظائف
                        </a>
                        <button class="quick-action-btn" id="downloadBackupBtn" onclick="downloadBackup()">
                            <span>💾</span> تحميل نسخة احتياطية
                        </button>
                    </div>
                </div>

                <!-- Platform Health -->
                <div class="card">
                    <h2 class="card-title">صحة المنصة</h2>
                    <p class="card-desc">نظرة عامة</p>
                    <div class="health-metrics">
                        <div class="health-item">
                            <span class="health-label">الخادم</span>
                            <span class="health-status good">🟢 يعمل</span>
                        </div>
                        <div class="health-item">
                            <span class="health-label">قاعدة البيانات</span>
                            <span class="health-status good">🟢 متصل</span>
                        </div>
                        <div class="health-item">
                            <span class="health-label">التقييمات اليوم</span>
                            <span class="health-status">14</span>
                        </div>
                        <div class="health-item">
                            <span class="health-label">وقت التشغيل</span>
                            <span class="health-status">99.9%</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
