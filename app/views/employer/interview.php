<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - جدولة مقابلة">
    <title>العواطلية - حدد مقابلة</title>
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
        <div class="navbar-actions">
            <a href="/employer/applicants" class="btn btn-outline btn-sm">← المتقدمين</a>
            <a href="/employer/settings" class="user-pill employer-pill">
                <div class="user-avatar employer-avatar">🏢</div>
                <span><?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? 'صاحب عمل') ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 700px;">

            <div class="page-header">
                <h1 class="page-title">📅 حدد مقابلة</h1>
                <p class="page-subtitle">حدد ميعاد ومكان المقابلة مع المتقدم</p>
            </div>

            <!-- Candidate Info -->
            <?php if (!empty($application)): ?>
                <div class="card mb-3">
                    <div class="applicant-info" style="display: flex; align-items: center; gap: 16px;">
                        <div class="user-avatar" style="width: 56px; height: 56px; font-size: 1.3rem;">
                            <?= mb_substr($application['FullName'] ?? 'م', 0, 1) ?>
                        </div>
                        <div>
                            <h3 style="margin-bottom: 2px;"><?= htmlspecialchars($application['FullName'] ?? 'متقدم') ?></h3>
                            <p class="text-muted" style="font-size: 0.85rem;">تقدم على: <?= htmlspecialchars($application['JobTitle'] ?? '') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Interview Form -->
                <div class="card">
                    <h2 class="card-title">تفاصيل المقابلة</h2>
                    <p class="card-desc">ادخل ميعاد ومكان المقابلة</p>

                    <form id="interviewForm">
                        <input type="hidden" name="application_id" value="<?= htmlspecialchars($application['ApplicationID']) ?>">

                    <!-- Date & Time -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="interviewDate">تاريخ المقابلة *</label>
                            <input 
                                type="date" 
                                id="interviewDate" 
                                name="interview_date" 
                                class="form-control"
                                required
                                dir="ltr"
                            >
                        </div>
                        <div class="form-group">
                            <label for="interviewTime">الساعة *</label>
                            <input 
                                type="time" 
                                id="interviewTime" 
                                name="interview_time" 
                                class="form-control"
                                required
                                dir="ltr"
                            >
                        </div>
                    </div>

                    <!-- Location Type -->
                    <div class="form-group">
                        <label for="locationType">نوع المقابلة *</label>
                        <div class="select-wrapper">
                            <select id="locationType" name="location_type" class="form-select" required>
                                <option value="" disabled selected>اختار...</option>
                                <option value="onsite">حضوري (في المكتب)</option>
                                <option value="online">أونلاين</option>
                                <option value="hybrid">هايبرد</option>
                            </select>
                        </div>
                    </div>

                    <!-- Physical Location -->
                    <div class="form-group" id="locationGroup">
                        <label for="location">العنوان / المكان *</label>
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            class="form-control" 
                            placeholder="مثلاً: مدينة نصر - شارع مصطفى النحاس - مبنى 5 الدور 3"
                            required
                        >
                    </div>

                    <!-- Meeting Link (for online) -->
                    <div class="form-group" id="meetingLinkGroup" style="display: none;">
                        <label for="meetingLink">لينك الاجتماع</label>
                        <input 
                            type="url" 
                            id="meetingLink" 
                            name="meeting_link" 
                            class="form-control" 
                            placeholder="https://meet.google.com/..."
                            dir="ltr"
                        >
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="interviewNotes">ملاحظات (اختياري)</label>
                        <textarea 
                            id="interviewNotes" 
                            name="notes" 
                            class="form-control form-textarea"
                            placeholder="مثلاً: هات معاك اللابتوب عشان هيكون في live coding..."
                            rows="3"
                        ></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2" id="scheduleBtn">
                        📅 حدد المقابلة
                    </button>
                </form>
            </div>
            <?php else: ?>
                <div class="card text-center" style="padding: 48px 16px; text-align: center;">
                    <span style="font-size: 3rem;">⚠️</span>
                    <h3 class="mt-2">لم يتم تحديد طلب متقدم</h3>
                    <p class="text-muted mt-1">برجاء اختيار متقدم من قائمة المتقدمين لتحديد موعد مقابلة معه</p>
                    <a href="/employer/applicants" class="btn btn-primary mt-3" style="display: inline-block;">عرض قائمة المتقدمين</a>
                </div>
            <?php endif; ?>

        </div>
    </main>

</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon success">✅</div>
        <h2>تم تحديد المقابلة!</h2>
        <p>هيتم إبلاغ المتقدم بميعاد ومكان المقابلة.</p>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
            <a href="/employer/applicants" class="btn btn-primary">رجوع للمتقدمين</a>
            <a href="/employer/dashboard" class="btn btn-outline">لوحة التحكم</a>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
