<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - لوحة التحكم، ارفع سيرتك الذاتية واعرف مستواك">
    <title>العواطلية - لوحة التحكم</title>
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
            <a href="/dashboard" class="nav-link active">لوحة التحكم</a>
            <a href="/jobs" class="nav-link">الوظائف</a>
            <a href="/my-applications" class="nav-link">طلباتي</a>
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
                <h1 class="page-title">أهلاً يا <?= htmlspecialchars(explode(' ', $user['FullName'] ?? 'عاطل')[0]) ?> 👋</h1>
                <p class="page-subtitle">ارفع الـ CV بتاعك وقولنا عاوز تشتغل إيه، وسيبنا نقيّمك</p>
            </div>

            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= $stats['total_applications'] ?? 0 ?></div>
                    <div class="stat-label">طلبات التوظيف</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?= $stats['pending'] ?? 0 ?></div>
                    <div class="stat-label">قيد المراجعة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎉</div>
                    <div class="stat-value"><?= $stats['accepted'] ?? 0 ?></div>
                    <div class="stat-label">طلبات مقبولة</div>
                </div>
            </div>

            <!-- Upload + Job Title -->
            <div class="dashboard-grid">
                
                <!-- Upload CV Card -->
                <div class="card card-full">
                    <h2 class="card-title">ارفع الـ CV بتاعك</h2>
                    <p class="card-desc">ارفع ملف PDF أو Word وهنحلله ليك</p>

                    <form id="cvForm" action="/evaluation" method="POST" enctype="multipart/form-data">
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" id="cvFile" name="cv" accept=".pdf,.doc,.docx">
                            <span class="upload-icon">📄</span>
                            <h3>اسحب الملف هنا أو اضغط لاختياره</h3>
                            <p>PDF, DOC, DOCX — أقصى حجم 5MB</p>
                        </div>

                        <div class="file-info" id="fileInfo">
                            <span>✅</span>
                            <span id="fileName">filename.pdf</span>
                            <span id="fileSize" class="text-muted">2.3 MB</span>
                            <button type="button" class="remove-file" id="removeFile">x حذف</button>
                        </div>

                        <div class="form-group mt-3">
                            <label for="jobTitle">المسمى الوظيفي اللي عاوزه</label>
                            <div class="select-wrapper">
                                <select id="jobTitle" name="job_title" class="form-select" required>
                                    <option value="" disabled selected>اختار الوظيفة...</option>
                                    <option value="frontend">Frontend Developer</option>
                                    <option value="backend">Backend Developer</option>
                                    <option value="fullstack">Full Stack Developer</option>
                                    <option value="mobile">Mobile Developer</option>
                                    <option value="data_scientist">Data Scientist</option>
                                    <option value="data_analyst">Data Analyst</option>
                                    <option value="ml_engineer">ML Engineer</option>
                                    <option value="devops">DevOps Engineer</option>
                                    <option value="ui_ux">UI/UX Designer</option>
                                    <option value="cyber_security">Cybersecurity Analyst</option>
                                    <option value="qa">QA Engineer</option>
                                    <option value="product_manager">Product Manager</option>
                                    <option value="graphic_designer">Graphic Designer</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2" id="analyzeBtn" disabled>
                            حلّل الـ CV بتاعي
                        </button>
                    </form>
                </div>

                <!-- Browse Jobs Card -->
                <div class="card">
                    <h2 class="card-title">🔍 دوّر على شغل</h2>
                    <p class="card-desc">تصفح الوظائف المتاحة وقدّم عليها</p>
                    <div style="text-align: center; padding: 16px 0;">
                        <a href="/jobs" class="btn btn-primary">تصفح الوظائف</a>
                    </div>
                    <div class="mini-job-list">
                        <?php if (!empty($recentJobs)): ?>
                            <?php foreach (array_slice($recentJobs, 0, 3) as $job): ?>
                                <a href="/jobs/apply?id=<?= $job['JobID'] ?>" class="mini-job-item" style="text-decoration:none; color:inherit; display:flex;">
                                    <span>🏢</span>
                                    <div>
                                        <strong><?= htmlspecialchars($job['Title']) ?></strong>
                                        <p class="text-muted" style="font-size: 0.78rem;">
                                            <?= htmlspecialchars($job['CompanyName'] ?? $job['EmployerName'] ?? 'شركة') ?> · <?= htmlspecialchars($job['City'] ?? 'عن بعد') ?>
                                        </p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center" style="font-size: 0.85rem; padding: 12px 0;">لا توجد وظائف معروضة حالياً</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="card">
                    <h2 class="card-title">نصايح عشان الـ CV يطلع حلو</h2>
                    <p class="card-desc">عشان تاخد أحسن تقييم</p>
                    <ul style="list-style: none; padding: 0; font-size: 0.85rem; color: var(--text-secondary);">
                        <li style="padding: 8px 0; border-bottom: 1px solid var(--border-light);">
                            💡 اكتب الـ skills بتاعتك بوضوح
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid var(--border-light);">
                            💡 حط مشاريع عملتها قبل كده
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid var(--border-light);">
                            💡 اكتب خبرتك بالسنين أو الشهور
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid var(--border-light);">
                            💡 حط links لـ GitHub أو Portfolio
                        </li>
                        <li style="padding: 8px 0;">
                            💡 خلي التنسيق بسيط ومرتب
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </main>

</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">بنحلل الـ CV بتاعك... استنى شوية</div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
