<?php
$isEdit = !empty($application['InterviewID']);
$interviewDateVal = '';
if (!empty($application['InterviewDate'])) {
    $interviewDateVal = date('Y-m-d\TH:i', strtotime($application['InterviewDate']));
}
$locType = $application['InterviewLocationType'] ?? 'onsite';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - <?= $isEdit ? 'تعديل موعد المقابلة' : 'جدولة مقابلة' ?>">
    <title>العواطلية - <?= $isEdit ? 'تعديل موعد المقابلة' : 'حدد مقابلة' ?></title>
    <link rel="stylesheet" href="/public/css/style.css?v=<?= time() ?>">
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
                <div class="user-avatar employer-avatar">
                    <?php if (!empty($user['CompanyLogo']) || !empty($user['ProfilePicture'])): ?>
                        <img src="<?= htmlspecialchars($user['CompanyLogo'] ?? $user['ProfilePicture']) ?>" alt="">
                    <?php else: ?>
                        🏢
                    <?php endif; ?>
                </div>
                <span><?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? 'صاحب عمل') ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 700px;">

            <div class="page-header">
                <h1 class="page-title"><?= $isEdit ? '✏️ تعديل موعد وتفاصيل المقابلة' : '📅 حدد مقابلة' ?></h1>
                <p class="page-subtitle"><?= $isEdit ? 'عدّل موعد أو مكان أو طريقة المقابلة وسيصل بريد إلكتروني فوري للمتقدم بالتفاصيل الجديدة' : 'حدد ميعاد ومكان المقابلة مع المتقدم وسيتم إرسال بريد إلكتروني بكافة التفاصيل' ?></p>
            </div>

            <!-- Candidate Info -->
            <?php if (!empty($application)): ?>
                <div class="card mb-3">
                    <div class="applicant-info" style="display: flex; align-items: center; gap: 16px;">
                        <div class="user-avatar" style="width: 56px; height: 56px; font-size: 1.3rem;" <?= !empty($application['ProfilePicture']) ? 'title="اضغط لتكبير الصورة"' : '' ?>>
                            <?php if (!empty($application['ProfilePicture'])): ?>
                                <img src="<?= htmlspecialchars($application['ProfilePicture']) ?>" alt="<?= htmlspecialchars($application['FullName'] ?? '') ?>" onerror="this.onerror=null; this.parentElement.innerHTML='<?= mb_substr($application['FullName'] ?? 'م', 0, 1) ?>';">
                            <?php else: ?>
                                <?= mb_substr($application['FullName'] ?? 'م', 0, 1) ?>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1;">
                            <h3 style="margin-bottom: 2px;"><?= htmlspecialchars($application['FullName'] ?? 'متقدم') ?></h3>
                            <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0;">تقدم على: <?= htmlspecialchars($application['JobTitle'] ?? '') ?></p>
                            <?php if (!empty($application['Email'])): ?>
                                <p class="text-muted" style="font-size: 0.8rem; margin: 2px 0 0;" dir="ltr"><?= htmlspecialchars($application['Email']) ?> ✉️</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($isEdit && !empty($application['InterviewDate'])): ?>
                        <div style="margin-top: 14px; padding: 12px 16px; background: rgba(196, 113, 43, 0.12); border: 1px solid rgba(196, 113, 43, 0.35); border-radius: 8px;">
                            <span style="color: #c4712b; font-weight: bold; font-size: 0.95rem;">📌 الموعد المحدد حالياً:</span>
                            <div style="font-weight: 700; color: #fff; margin-top: 4px; font-size: 1rem;">
                                <?= Mailer::formatArabicDateTime($application['InterviewDate']) ?>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 4px;">
                                نوع المقابلة: <?= ($locType === 'online') ? '🌐 أونلاين' : (($locType === 'hybrid') ? '🔄 هايبرد' : '🏢 حضوري') ?>
                                <?php if (!empty($application['InterviewLocation'])): ?>
                                    — المقر: <?= htmlspecialchars($application['InterviewLocation']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Interview Form -->
                <div class="card">
                    <h2 class="card-title"><?= $isEdit ? 'تعديل بيانات المقابلة' : 'تفاصيل المقابلة' ?></h2>
                    <p class="card-desc"><?= $isEdit ? 'قم بإجراء التعديلات المطلوبة واضغط حفظ لإشعار المتقدم فوراً' : 'ادخل ميعاد ومكان المقابلة وسيتم إشعار المتقدم فوراً' ?></p>

                    <form id="interviewForm">
                        <input type="hidden" name="application_id" value="<?= htmlspecialchars($application['ApplicationID']) ?>">
                        <input type="hidden" name="is_edit" value="<?= $isEdit ? '1' : '0' ?>">

                        <!-- Date & Time -->
                        <div class="form-group">
                            <label for="interviewDateTime"><?= $isEdit ? 'تاريخ وساعة المقابلة الجديدة *' : 'تاريخ وساعة المقابلة *' ?></label>
                            <input 
                                type="datetime-local" 
                                id="interviewDateTime" 
                                name="interview_datetime" 
                                class="form-control"
                                value="<?= htmlspecialchars($interviewDateVal) ?>"
                                required
                                dir="ltr"
                                min="<?= date('Y-m-d\TH:i') ?>"
                            >
                            <small class="text-muted">اختر اليوم والساعة بدقة — لا يمكن اختيار موعد قد مضى</small>
                        </div>

                        <!-- Location Type -->
                        <div class="form-group">
                            <label for="locationType">نوع المقابلة *</label>
                            <div class="select-wrapper">
                                <select id="locationType" name="location_type" class="form-select" required>
                                    <option value="" disabled <?= empty($application['InterviewLocationType']) ? 'selected' : '' ?>>اختار...</option>
                                    <option value="onsite" <?= ($locType === 'onsite') ? 'selected' : '' ?>>🏢 حضوري (في مقر الشركة)</option>
                                    <option value="online" <?= ($locType === 'online') ? 'selected' : '' ?>>🌐 أونلاين عبر الإنترنت</option>
                                    <option value="hybrid" <?= ($locType === 'hybrid') ? 'selected' : '' ?>>🔄 هايبرد (حضوري وأونلاين)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Physical Location -->
                        <div class="form-group" id="locationGroup" style="<?= ($locType === 'online') ? 'display: none;' : '' ?>">
                            <label for="location" id="locationLabel">العنوان / مقر المقابلة *</label>
                            <input 
                                type="text" 
                                id="location" 
                                name="location" 
                                class="form-control" 
                                value="<?= htmlspecialchars(($locType === 'online') ? '' : ($application['InterviewLocation'] ?? '')) ?>"
                                placeholder="مثلاً: مدينة نصر - شارع مصطفى النحاس - مبنى 5 الدور 3"
                                <?= ($locType !== 'online') ? 'required' : '' ?>
                            >
                        </div>

                        <!-- Meeting Link (for online or hybrid) -->
                        <div class="form-group" id="meetingLinkGroup" style="<?= in_array($locType, ['online', 'hybrid']) ? 'display: block;' : 'display: none;' ?>">
                            <label for="meetingLink" id="meetingLinkLabel">رابط المقابلة / الاجتماع أونلاين <?= in_array($locType, ['online', 'hybrid']) ? '*' : '' ?></label>
                            <input 
                                type="url" 
                                id="meetingLink" 
                                name="meeting_link" 
                                class="form-control" 
                                value="<?= htmlspecialchars($application['MeetingLink'] ?? '') ?>"
                                placeholder="https://meet.google.com/... أو https://zoom.us/..."
                                dir="ltr"
                                <?= in_array($locType, ['online', 'hybrid']) ? 'required' : '' ?>
                            >
                            <small class="text-muted">رابط الاجتماع إلزامي ومطلوب عند اختيار مقابلة أونلاين أو هايبرد</small>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="interviewNotes">ملاحظات وتوجيهات للمتقدم (اختياري)</label>
                            <textarea 
                                id="interviewNotes" 
                                name="notes" 
                                class="form-control form-textarea"
                                placeholder="مثلاً: يرجى إحضار اللابتوب، أو الحضور قبل الموعد بـ 10 دقائق..."
                                rows="3"
                            ><?= htmlspecialchars($application['InterviewNotes'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2" id="scheduleBtn" data-original-text="<?= $isEdit ? '✏️ حفظ وإرسال التعديلات للمتقدم' : '📅 حدد المقابلة' ?>">
                            <?= $isEdit ? '✏️ حفظ وإرسال التعديلات للمتقدم' : '📅 حدد المقابلة' ?>
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
        <h2 id="successModalTitle"><?= $isEdit ? 'تم تحديث المقابلة بنجاح!' : 'تم تحديد المقابلة!' ?></h2>
        <p id="successModalDesc"><?= $isEdit ? 'تم حفظ التعديلات وإرسال بريد إلكتروني فوري للمتقدم بالتفاصيل الجديدة.' : 'تم حفظ الموعد وإرسال تفاصيل المقابلة للمتقدم عبر البريد الإلكتروني.' ?></p>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
            <a href="/employer/applicants" class="btn btn-primary">رجوع للمتقدمين</a>
            <a href="/employer/dashboard" class="btn btn-outline">لوحة التحكم</a>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>