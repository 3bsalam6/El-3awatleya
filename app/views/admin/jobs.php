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

            <!-- Filters -->
            <div class="filters-bar mb-3">
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select id="filterJobStatus" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            <option value="active">نشط</option>
                            <option value="removed">محذوف</option>
                        </select>
                    </div>
                    <input type="text" class="form-control form-control-sm" id="searchAdminJobs" placeholder="ابحث عن وظيفة..." style="max-width: 250px;">
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
                                <th>الشركة</th>
                                <th>النوع</th>
                                <th>المتقدمين</th>
                                <th>تاريخ النشر</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jobs)): ?>
                                <?php foreach ($jobs as $j): ?>
                                    <tr class="<?= empty($j['IsActive']) ? 'removed-row' : '' ?>">
                                        <td><?= $j['JobID'] ?></td>
                                        <td><strong><?= htmlspecialchars($j['Title']) ?></strong></td>
                                        <td><?= htmlspecialchars($j['CompanyName'] ?? $j['EmployerName'] ?? 'شركة') ?></td>
                                        <td><?= htmlspecialchars($j['JobType'] ?? '') ?></td>
                                        <td><?= $j['ApplicationsCount'] ?? 0 ?></td>
                                        <td><?= date('d/m/Y', strtotime($j['PostedAt'])) ?></td>
                                        <td>
                                            <?php if (!empty($j['IsActive'])): ?>
                                                <span class="status-dot active"></span> نشط
                                            <?php else: ?>
                                                <span class="status-dot banned"></span> معطل
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($j['IsActive'])): ?>
                                                <button class="btn btn-danger btn-sm" onclick="removeJob(<?= $j['JobID'] ?>, this)">🗑️ تعطيل</button>
                                            <?php else: ?>
                                                <button class="btn btn-success btn-sm" onclick="restoreJob(<?= $j['JobID'] ?>, this)">♻️ تفعيل</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center" style="padding: 24px;">لا توجد أي وظائف معروضة حتى الآن</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</div>

<!-- Remove Reason Modal -->
<div class="modal-overlay" id="removeJobModal" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon" style="color: var(--danger);">🗑️</div>
        <h2>إزالة إعلان الوظيفة</h2>
        <div class="form-group mt-2">
            <label for="removeReason">سبب الإزالة</label>
            <textarea id="removeReason" class="form-control form-textarea" rows="3" placeholder="اكتب سبب إزالة الإعلان..."></textarea>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 16px;">
            <button class="btn btn-danger" id="confirmRemoveBtn">تأكيد الإزالة</button>
            <button class="btn btn-outline" onclick="closeModal('removeJobModal')">إلغاء</button>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
