<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - تصفح الوظائف المتاحة">
    <title>العواطلية - الوظائف</title>
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
            <a href="/jobs" class="nav-link active">الوظائف</a>
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
                <h1 class="page-title">الوظائف المتاحة</h1>
                <p class="page-subtitle">دور على الشغل اللي يناسبك وقدّم عليه</p>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select id="filterType" class="form-select form-select-sm">
                            <option value="">كل الأنواع</option>
                            <option value="full-time">دوام كامل</option>
                            <option value="part-time">دوام جزئي</option>
                            <option value="remote">عن بعد</option>
                            <option value="internship">تدريب</option>
                            <option value="freelance">فريلانس</option>
                        </select>
                    </div>
                    <div class="select-wrapper">
                        <select id="filterLevel" class="form-select form-select-sm">
                            <option value="">كل المستويات</option>
                            <option value="entry">مبتدئ</option>
                            <option value="mid">متوسط</option>
                            <option value="senior">خبرة</option>
                            <option value="lead">قيادي</option>
                        </select>
                    </div>
                    <input type="text" class="form-control form-control-sm" id="searchJobs" placeholder="ابحث عن وظيفة..." style="max-width: 250px;">
                </div>
            </div>

            <!-- Jobs Grid -->
            <div class="jobs-grid" id="jobsGrid">

                <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card" data-type="<?= htmlspecialchars($job['JobType'] ?? '') ?>" data-level="<?= htmlspecialchars($job['ExperienceLevel'] ?? '') ?>">
                            <div class="job-card-header">
                                <div class="job-company-logo">🏢</div>
                                <div>
                                    <h3 class="job-title"><?= htmlspecialchars($job['Title']) ?></h3>
                                    <p class="job-company"><?= htmlspecialchars($job['CompanyName'] ?? $job['EmployerName'] ?? 'شركة') ?></p>
                                </div>
                            </div>
                            <div class="job-tags">
                                <span class="tag tag-type"><?= htmlspecialchars($job['JobType'] ?? 'دوام كامل') ?></span>
                                <span class="tag tag-level"><?= htmlspecialchars($job['ExperienceLevel'] ?? 'متوسط') ?></span>
                                <?php if (!empty($job['City'])): ?>
                                    <span class="tag tag-location"><?= htmlspecialchars($job['City']) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="job-desc"><?= htmlspecialchars(mb_substr($job['Description'], 0, 150)) ?><?= mb_strlen($job['Description']) > 150 ? '...' : '' ?></p>
                            <?php if (!empty($job['Skills'])): ?>
                                <div class="job-skills">
                                    <?php foreach (array_filter(array_map('trim', explode(',', $job['Skills']))) as $skill): ?>
                                        <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="job-footer">
                                <span class="job-salary">
                                    <?= !empty($job['SalaryMin']) ? number_format($job['SalaryMin']) . (!empty($job['SalaryMax']) ? ' - ' . number_format($job['SalaryMax']) : '') . ' ' . htmlspecialchars($job['SalaryCurrency'] ?? 'ج.م') : 'راتب تنافسي' ?>
                                </span>
                                <span class="job-date"><?= date('d/m/Y', strtotime($job['PostedAt'])) ?></span>
                            </div>
                            <a href="/jobs/apply?id=<?= $job['JobID'] ?>" class="btn btn-primary btn-sm mt-2">قدّم على الوظيفة</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px 16px;">
                        <span style="font-size: 3rem;">🔍</span>
                        <h3 class="mt-2">لا توجد وظائف متاحة حالياً</h3>
                        <p class="text-muted">جرب البحث بكلمات أخرى أو اختر تصنيفاً مختلفاً</p>
                    </div>
                <?php endif; ?>
            </div>


        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
