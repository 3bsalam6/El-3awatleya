<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - تتبع طلبات التوظيف">
    <title>العواطلية - طلباتي</title>
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
            <a href="/my-applications" class="nav-link active">طلباتي</a>
            <a href="/enhancements" class="nav-link">تحسين الـ CV</a>
        </div>
        <div class="navbar-actions">
            <a href="/settings" class="user-pill">
                <div class="user-avatar"><?= mb_substr($user['FullName'] ?? 'ع', 0, 1) ?></div>
                <span><?= htmlspecialchars(explode(' ', $user['FullName'] ?? 'مستخدم')[0]) ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container">

            <div class="page-header">
                <h1 class="page-title">طلبات التوظيف</h1>
                <p class="page-subtitle">تتبع حالة طلباتك على الوظائف المختلفة</p>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= count($applications) ?></div>
                    <div class="stat-label">إجمالي الطلبات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?= count(array_filter($applications, fn($a) => $a['Status'] === 'pending')) ?></div>
                    <div class="stat-label">قيد المراجعة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📞</div>
                    <div class="stat-value"><?= count(array_filter($applications, fn($a) => in_array($a['Status'], ['interview', 'accepted', 'shortlisted']))) ?></div>
                    <div class="stat-label">مقبول / مقابلة</div>
                </div>
            </div>

            <!-- Applications List -->
            <div class="applications-list">

                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $app): ?>
                        <div class="application-card">
                            <div class="application-header">
                                <div>
                                    <h3 class="application-title"><?= htmlspecialchars($app['JobTitle'] ?? 'وظيفة') ?></h3>
                                    <p class="application-company"><?= htmlspecialchars($app['CompanyName'] ?? 'شركة') ?></p>
                                </div>
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
                                $st = $app['Status'] ?? 'pending';
                                ?>
                                <span class="status-badge <?= $statusClasses[$st] ?? 'status-pending' ?>">
                                    <?= $statusLabels[$st] ?? $st ?>
                                </span>
                            </div>
                            <div class="application-meta">
                                <span>📅 قدمت: <?= date('d/m/Y', strtotime($app['AppliedAt'])) ?></span>
                                <?php if (!empty($app['City'])): ?>
                                    <span>📍 <?= htmlspecialchars($app['City']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($app['JobType'])): ?>
                                    <span>💼 <?= htmlspecialchars($app['JobType']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($app['EmployerNotes'])): ?>
                                <div class="interview-info mt-2">
                                    <div class="alert alert-success">
                                        <span>💬</span>
                                        <div>
                                            <strong>ملاحظة من الشركة:</strong><br>
                                            <?= nl2br(htmlspecialchars($app['EmployerNotes'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card text-center" style="padding: 48px 16px; text-align: center;">
                        <span style="font-size: 3rem;">📭</span>
                        <h3 class="mt-2">لسة مقدّمتش على أي وظيفة</h3>
                        <p class="text-muted mt-1">تصفح الوظائف المتاحة وقدّم على اللي يناسبك دلوقتي</p>
                        <a href="/jobs" class="btn btn-primary mt-3" style="display: inline-block;">تصفح الوظائف المتاحة</a>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
