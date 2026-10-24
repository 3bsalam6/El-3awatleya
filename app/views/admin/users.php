<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - إدارة المستخدمين">
    <title>العواطلية - إدارة المستخدمين</title>
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
            <a href="/admin/users" class="nav-link active">المستخدمين</a>
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
                <h1 class="page-title">👥 إدارة المستخدمين</h1>
                <p class="page-subtitle">حظر أو إلغاء حظر المستخدمين</p>
            </div>

            <!-- Filters -->
            <div class="filters-bar mb-3">
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select id="filterRole" class="form-select form-select-sm">
                            <option value="">كل الأدوار</option>
                            <option value="user">مشتغل</option>
                            <option value="employer">صاحب عمل</option>
                        </select>
                    </div>
                    <div class="select-wrapper">
                        <select id="filterBanned" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            <option value="active">نشط</option>
                            <option value="banned">محظور</option>
                        </select>
                    </div>
                    <input type="text" class="form-control form-control-sm" id="searchUsers" placeholder="ابحث بالاسم أو الإيميل..." style="max-width: 250px;">
                </div>
            </div>

            <!-- Users Table -->
            <div class="card card-full">
                <div class="admin-table-wrapper">
                    <table class="admin-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الإيميل</th>
                                <th>الدور</th>
                                <th>تاريخ التسجيل</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                    <tr class="<?= !empty($u['IsBanned']) ? 'banned-row' : '' ?>">
                                        <td><?= $u['UserID'] ?></td>
                                        <td><strong><?= htmlspecialchars($u['FullName']) ?></strong></td>
                                        <td dir="ltr"><?= htmlspecialchars($u['Email']) ?></td>
                                        <td>
                                            <?php if ($u['Role'] === 'employer'): ?>
                                                <span class="role-badge role-employer">صاحب عمل</span>
                                            <?php else: ?>
                                                <span class="role-badge role-user">باحث عن عمل</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($u['CreatedAt'])) ?></td>
                                        <td>
                                            <?php if (!empty($u['IsBanned'])): ?>
                                                <span class="status-dot banned"></span> محظور
                                            <?php else: ?>
                                                <span class="status-dot active"></span> نشط
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($u['IsBanned'])): ?>
                                                <button class="btn btn-success btn-sm" onclick="unbanUser(<?= $u['UserID'] ?>, this)">✅ إلغاء الحظر</button>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm" onclick="banUser(<?= $u['UserID'] ?>, this)">🚫 حظر</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 24px;">لا يوجد مستخدمين مسجلين حتى الآن</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</div>

<!-- Ban Reason Modal -->
<div class="modal-overlay" id="banModal" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon" style="color: var(--danger);">🚫</div>
        <h2>حظر المستخدم</h2>
        <div class="form-group mt-2">
            <label for="banReason">سبب الحظر</label>
            <textarea id="banReason" class="form-control form-textarea" rows="3" placeholder="اكتب سبب الحظر..."></textarea>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 16px;">
            <button class="btn btn-danger" id="confirmBanBtn">تأكيد الحظر</button>
            <button class="btn btn-outline" onclick="closeModal('banModal')">إلغاء</button>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
