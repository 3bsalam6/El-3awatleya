<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - إدارة المستخدمين">
    <title>العواطلية - إدارة المستخدمين والموافقات</title>
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
                <h1 class="page-title">👥 إدارة المستخدمين وطلبات التسجيل</h1>
                <p class="page-subtitle">الموافقة على الحسابات الجديدة أو رفضها لحظر الحسابات الوهمية وإدارة الحظر</p>
            </div>

            <!-- Pending Approvals Banner -->
            <?php 
                $pendingCount = $userStats['PendingUsers'] ?? 0;
            ?>
            <?php if ($pendingCount > 0): ?>
                <div class="alert alert-warning mb-3" style="display: flex; justify-content: space-between; align-items: center; background: rgba(234, 179, 8, 0.12); border: 1.5px solid rgba(234, 179, 8, 0.35); border-radius: 12px; padding: 14px 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 1.6rem;">⏳</span>
                        <div>
                            <strong style="color: #eab308; font-size: 1.05rem;">يوجد <?= $pendingCount ?> حساب جديد في انتظار الموافقة أو الرفض!</strong>
                            <p style="margin: 2px 0 0; color: var(--text-muted); font-size: 0.85rem;">لحماية المنصة، لا يمكن للمستخدم تسجيل الدخول إلا بعد موافقة الإدارة. سيتم إرسال بريد إلكتروني فوري للمستخدم بالقرار.</p>
                        </div>
                    </div>
                    <button class="btn btn-sm" style="background: #eab308; color: #111; font-weight: 700; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;" onclick="filterTableByStatus('pending')">
                        عرض طلبات الانتظار فقط
                    </button>
                </div>
            <?php endif; ?>

            <!-- Pending Sensitive Data Change Requests -->
            <?php if (!empty($profileRequests)): ?>
                <div class="card mb-3" style="border: 1.5px solid rgba(59, 130, 246, 0.4); background: rgba(59, 130, 246, 0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 1.5rem;">📝</span>
                            <div>
                                <h3 style="margin: 0; font-size: 1.15rem; color: #3b82f6;">طلبات تعديل البيانات الحساسة المعلقة</h3>
                                <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--text-muted);">
                                    تعديل الاسم أو اسم الشركة يتطلب مراجعة وقرار الإدارة قبل تطبيقه على الحساب
                                </p>
                            </div>
                        </div>
                        <span class="status-badge" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.85rem; padding: 6px 14px;">
                            ⏳ <?= count($profileRequests) ?> طلب بحاجة لمراجعة
                        </span>
                    </div>

                    <div class="admin-table-wrapper">
                        <table class="admin-table" id="profileRequestsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>صاحب الحساب</th>
                                    <th>الدور</th>
                                    <th>البيان المطلوب تعديله</th>
                                    <th>التعديل المقترح</th>
                                    <th>تاريخ الطلب</th>
                                    <th>القرار</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($profileRequests as $pReq): ?>
                                    <tr id="profile-req-row-<?= $pReq['RequestID'] ?>">
                                        <td><?= $pReq['RequestID'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($pReq['FullName']) ?></strong>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); direction: ltr; text-align: right;"><?= htmlspecialchars($pReq['Email']) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($pReq['Role'] === 'employer'): ?>
                                                <span class="role-badge role-employer">صاحب عمل</span>
                                            <?php else: ?>
                                                <span class="role-badge role-user">باحث عن عمل</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600;"><?= ($pReq['FieldChanged'] === 'CompanyName') ? '🏢 اسم الشركة' : '👤 الاسم الكامل' ?></span>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($pReq['OldValue'] ?? '-') ?></span>
                                                <span style="color: var(--text-muted);">➡️</span>
                                                <strong style="color: #10b981; font-size: 0.95rem;"><?= htmlspecialchars($pReq['NewValue']) ?></strong>
                                            </div>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($pReq['RequestedAt'])) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 6px; align-items: center;">
                                                <button class="btn btn-success btn-sm" onclick="approveProfileRequest(<?= $pReq['RequestID'] ?>, this)" title="قبول التعديل وتحديث الحساب فوراً">
                                                    ✅ قبول
                                                </button>
                                                <button class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.4);" onclick="rejectProfileRequest(<?= $pReq['RequestID'] ?>, this)" title="رفض الطلب مع إرسال إشعار للمستخدم">
                                                    ❌ رفض
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters-bar mb-3">
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select id="filterRole" class="form-select form-select-sm" onchange="applyUserFilters()">
                            <option value="">كل الأدوار</option>
                            <option value="user" <?= (($role ?? '') === 'user') ? 'selected' : '' ?>>باحث عن عمل</option>
                            <option value="employer" <?= (($role ?? '') === 'employer') ? 'selected' : '' ?>>صاحب عمل</option>
                        </select>
                    </div>
                    <div class="select-wrapper">
                        <select id="filterStatus" class="form-select form-select-sm" onchange="applyUserFilters()">
                            <option value="">كل حالات الحساب</option>
                            <option value="pending" <?= (($status ?? '') === 'pending') ? 'selected' : '' ?>>⏳ في انتظار الموافقة</option>
                            <option value="approved" <?= (($status ?? '') === 'approved') ? 'selected' : '' ?>>✅ معتمد ونشط</option>
                            <option value="rejected" <?= (($status ?? '') === 'rejected') ? 'selected' : '' ?>>❌ مرفوض</option>
                            <option value="banned" <?= (($status ?? '') === 'banned') ? 'selected' : '' ?>>🚫 محظور</option>
                        </select>
                    </div>
                    <input type="text" class="form-control form-control-sm" id="searchUsers" placeholder="ابحث بالاسم أو الإيميل..." style="max-width: 250px;" oninput="applyUserFilters()">
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
                                <th>حالة الحساب</th>
                                <th>إجراءات الإدارة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                    <?php 
                                        $uStatus = $u['ApprovalStatus'] ?? 'approved';
                                        $isBanned = !empty($u['IsBanned']);
                                        $rowClass = '';
                                        if ($uStatus === 'pending') $rowClass = 'pending-row';
                                        elseif ($isBanned) $rowClass = 'banned-row';
                                    ?>
                                    <tr class="<?= $rowClass ?>" 
                                        data-role="<?= $u['Role'] ?>" 
                                        data-status="<?= $isBanned ? 'banned' : $uStatus ?>"
                                        data-search="<?= htmlspecialchars(strtolower($u['FullName'] . ' ' . $u['Email'])) ?>"
                                        style="<?= ($uStatus === 'pending') ? 'background: rgba(234, 179, 8, 0.05);' : '' ?>">
                                        <td><?= $u['UserID'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($u['FullName']) ?></strong>
                                            <?php if ($uStatus === 'pending'): ?>
                                                <span style="font-size: 0.7rem; color: #eab308; display: block;">(طلب جديد)</span>
                                            <?php endif; ?>
                                        </td>
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
                                            <?php if ($uStatus === 'pending'): ?>
                                                <span class="status-badge status-badge-pending">⏳ في انتظار الموافقة</span>
                                            <?php elseif ($uStatus === 'rejected'): ?>
                                                <span class="status-badge status-badge-rejected" title="<?= htmlspecialchars($u['RejectionReason'] ?? 'تم رفض الحساب') ?>">❌ مرفوض</span>
                                            <?php elseif ($isBanned): ?>
                                                <span class="status-badge status-badge-banned" title="<?= htmlspecialchars($u['BanReason'] ?? '') ?>">🚫 محظور</span>
                                            <?php else: ?>
                                                <span class="status-badge status-badge-approved">✅ معتمد ونشط</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                                <?php if ($uStatus === 'pending'): ?>
                                                    <!-- Approve Button -->
                                                    <button class="btn btn-success btn-sm" onclick="approveUser(<?= $u['UserID'] ?>, this)" title="الموافقة وتفعيل الحساب وإرسال إيميل">
                                                        ✅ قبول الحساب
                                                    </button>
                                                    <!-- Disapprove Button -->
                                                    <button class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.4);" onclick="openDisapproveModal(<?= $u['UserID'] ?>, '<?= htmlspecialchars(addslashes($u['FullName'])) ?>', '<?= htmlspecialchars(addslashes($u['Email'])) ?>')" title="رفض الحساب وإرسال إيميل">
                                                        ❌ رفض
                                                    </button>
                                                <?php elseif ($uStatus === 'rejected'): ?>
                                                    <button class="btn btn-success btn-sm" onclick="approveUser(<?= $u['UserID'] ?>, this)" title="إعادة قبول الحساب وتفعيله">
                                                        🔄 قبول وتفعيل
                                                    </button>
                                                <?php else: ?>
                                                    <!-- Approved accounts: Ban / Unban -->
                                                    <?php if ($isBanned): ?>
                                                        <button class="btn btn-success btn-sm" onclick="unbanUser(<?= $u['UserID'] ?>, this)">✅ إلغاء الحظر</button>
                                                    <?php else: ?>
                                                        <button class="btn btn-danger btn-sm" onclick="banUser(<?= $u['UserID'] ?>, this)">🚫 حظر</button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
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
        <p style="color: var(--text-muted); font-size: 0.9rem;" id="banUserDesc">
            سيتم حظر هذا المستخدم ومنعه من الدخول وإرسال بريد إلكتروني له موضحاً فيه سبب الحظر.
        </p>
        <div class="form-group mt-2">
            <label for="banReason">سبب الحظر (سيتم إدراجه في الإيميل المرسل للمستخدم)</label>
            <textarea id="banReason" class="form-control form-textarea" rows="3" placeholder="مثال: انتهاك شروط الخدمة، سلوك غير لائق، بيانات غير حقيقية..."></textarea>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 16px;">
            <button class="btn btn-danger" id="confirmBanBtn">تأكيد الحظر وإرسال الإيميل</button>
            <button class="btn btn-outline" onclick="closeModal('banModal')">إلغاء</button>
        </div>
    </div>
</div>

<!-- Disapprove / Reject Account Modal -->
<div class="modal-overlay" id="disapproveModal" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon" style="color: var(--danger);">⚠️</div>
        <h2>رفض طلب إنشاء الحساب</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;" id="disapproveUserDesc">
            سيتم حظر تفعيل هذا الحساب وإرسال بريد إلكتروني رسمي للمستخدم بنتيجة الرفض.
        </p>
        <input type="hidden" id="disapproveUserId">
        <div class="form-group mt-2">
            <label for="disapproveReason">سبب الرفض (سيتم إدراجه في الإيميل المرسل للمستخدم)</label>
            <textarea id="disapproveReason" class="form-control form-textarea" rows="3" placeholder="مثال: بيانات الشركة غير مكتملة، أو تم الاشتباه في حساب وهمي..."></textarea>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 16px;">
            <button class="btn btn-danger" id="confirmDisapproveBtn" onclick="confirmDisapprove()">
                تأكيد الرفض وإرسال الإيميل
            </button>
            <button class="btn btn-outline" onclick="closeModal('disapproveModal')">إلغاء</button>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
<script>
function applyUserFilters() {
    var role = document.getElementById('filterRole').value.toLowerCase();
    var status = document.getElementById('filterStatus').value.toLowerCase();
    var search = document.getElementById('searchUsers').value.trim().toLowerCase();

    var rows = document.querySelectorAll('#usersTable tbody tr');
    rows.forEach(function (row) {
        var rowRole = (row.getAttribute('data-role') || '').toLowerCase();
        var rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
        var rowSearch = (row.getAttribute('data-search') || '').toLowerCase();

        var matchRole = !role || rowRole === role;
        var matchStatus = !status || rowStatus === status;
        var matchSearch = !search || rowSearch.indexOf(search) !== -1;

        if (matchRole && matchStatus && matchSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterTableByStatus(statusVal) {
    var select = document.getElementById('filterStatus');
    if (select) {
        select.value = statusVal;
        applyUserFilters();
    }
}
</script>
</body>
</html>
