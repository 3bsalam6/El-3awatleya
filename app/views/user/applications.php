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
                <div class="user-avatar">
                    <?php if (!empty($user['ProfilePicture'])): ?>
                        <img src="<?= htmlspecialchars($user['ProfilePicture']) ?>" alt="">
                    <?php else: ?>
                        <?= mb_substr($user['FullName'] ?? 'ع', 0, 1) ?>
                    <?php endif; ?>
                </div>
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

            <?php
            $userUpcomingInterviews = array_filter($applications, fn($a) => !empty($a['InterviewDate']) && strtotime($a['InterviewDate']) > time() && in_array($a['Status'], ['interview', 'accepted']));
            ?>
            <?php if (!empty($userUpcomingInterviews)): ?>
                <div class="card mb-4" style="background: linear-gradient(135deg, rgba(74, 140, 92, 0.16), rgba(74, 140, 92, 0.05)); border: 1.5px solid rgba(74, 140, 92, 0.4); border-radius: 12px; padding: 18px 22px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <span style="font-size: 2.2rem;">🎉</span>
                        <div>
                            <h3 style="margin: 0; color: #4a8c5c; font-size: 1.15rem;">تهانينا! تم قبول طلبك وتحديد موعد مقابلة لـ <?= count($userUpcomingInterviews) ?> من طلباتك</h3>
                            <p class="text-muted" style="margin: 4px 0 0 0; font-size: 0.88rem;">جميع تفاصيل المقابلة (الموعد، الرابط الأونلاين أو العنوان، ورسالة وتوجيهات الشركة) موضحة بالكامل داخل بطاقة كل طلب بالأسفل.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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

                            <?php
                            $hasInterview = !empty($app['InterviewID']) || !empty($app['InterviewDate']) || in_array($st, ['interview', 'accepted']);
                            ?>
                            <?php if ($hasInterview && !empty($app['InterviewDate'])): ?>
                                <?php
                                $ivTs = strtotime($app['InterviewDate']);
                                $arabicDays = [
                                    'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء',
                                    'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت'
                                ];
                                $arabicMonths = [
                                    '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
                                    '05' => 'مايو', '06' => 'يونيو', '07' => 'يوليو', '08' => 'أغسطس',
                                    '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر'
                                ];
                                $dayName = $arabicDays[date('l', $ivTs)] ?? date('l', $ivTs);
                                $monthName = $arabicMonths[date('m', $ivTs)] ?? date('m', $ivTs);
                                $formattedDate = $dayName . '، ' . date('j', $ivTs) . ' ' . $monthName . ' ' . date('Y', $ivTs);
                                $timeFormatted = date('g:i', $ivTs) . (date('A', $ivTs) === 'AM' ? ' صباحاً' : ' مساءً');

                                $isFuture = ($ivTs > time());
                                $isToday = (date('Y-m-d', $ivTs) === date('Y-m-d'));
                                $locType = $app['InterviewLocationType'] ?? 'onsite';

                                // Calendar Link
                                $calTitle = urlencode('مقابلة عمل: ' . ($app['JobTitle'] ?? '') . ' - ' . ($app['CompanyName'] ?? ''));
                                $calStart = gmdate('Ymd\THis\Z', $ivTs);
                                $calEnd = gmdate('Ymd\THis\Z', $ivTs + 3600);
                                $calLoc = urlencode(($locType === 'online') ? ($app['MeetingLink'] ?: $app['InterviewLocation'] ?? '') : ($app['InterviewLocation'] ?? ''));
                                $calDetails = urlencode("تفاصيل المقابلة الشخصية لوظيفة: " . ($app['JobTitle'] ?? '') . "\n" . ($app['InterviewNotes'] ?? ''));
                                $calUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calTitle}&dates={$calStart}/{$calEnd}&details={$calDetails}&location={$calLoc}";
                                ?>

                                <div class="interview-details-box mt-3" style="background: rgba(74, 140, 92, 0.08); border: 1.5px solid rgba(74, 140, 92, 0.35); border-radius: 12px; padding: 18px 20px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid rgba(74, 140, 92, 0.2); padding-bottom: 10px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 1.3rem;">🎉</span>
                                            <strong style="color: #4a8c5c; font-size: 1.05rem;">تفاصيل وموعد المقابلة الشخصية (تم قبولك مبدئياً)</strong>
                                        </div>
                                        <div>
                                            <?php if ($isToday): ?>
                                                <span class="badge" style="background: #e02424; color: #fff; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.8rem;">🔔 المقابلة اليوم!</span>
                                            <?php elseif ($isFuture): ?>
                                                <?php $diffDays = ceil(($ivTs - time()) / 86400); ?>
                                                <span class="badge" style="background: #4a8c5c; color: #fff; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.8rem;">⏳ متبقي <?= $diffDays ?> يوم</span>
                                            <?php else: ?>
                                                <span class="badge" style="background: var(--text-muted); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">✓ موعد المقابلة مضى</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Info Grid -->
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 14px;">
                                        <!-- Date -->
                                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); padding: 10px 14px; border-radius: 8px;">
                                            <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">📅 تاريخ المقابلة</span>
                                            <strong style="font-size: 0.95rem;"><?= htmlspecialchars($formattedDate) ?></strong>
                                        </div>

                                        <!-- Time -->
                                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); padding: 10px 14px; border-radius: 8px;">
                                            <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">⏰ ساعة وتوقيت المقابلة</span>
                                            <strong style="font-size: 0.95rem;" dir="ltr"><?= htmlspecialchars($timeFormatted) ?></strong>
                                        </div>

                                        <!-- Type -->
                                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); padding: 10px 14px; border-radius: 8px;">
                                            <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">📍 نوع المقابلة</span>
                                            <strong style="font-size: 0.95rem;">
                                                <?php if ($locType === 'online'): ?>
                                                    🌐 أونلاين عبر الإنترنت
                                                <?php elseif ($locType === 'hybrid'): ?>
                                                    🔄 هجينة (حضوري وأونلاين)
                                                <?php else: ?>
                                                    🏢 حضورية بمقر الشركة
                                                <?php endif; ?>
                                            </strong>
                                        </div>
                                    </div>

                                    <!-- Online Meeting Link Details -->
                                    <?php if ($locType === 'online' || !empty($app['MeetingLink'])): ?>
                                        <div style="background: rgba(74, 140, 92, 0.12); border: 1px dashed #4a8c5c; padding: 12px 16px; border-radius: 8px; margin-bottom: 14px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                                <div>
                                                    <strong style="display: flex; align-items: center; gap: 6px; color: #4a8c5c;">
                                                        <span>🌐</span> رابط الانضمام للاجتماع الأونلاين:
                                                    </strong>
                                                    <div style="margin-top: 4px; font-family: monospace; font-size: 0.9rem; word-break: break-all;">
                                                        <?php if (!empty($app['MeetingLink'])): ?>
                                                            <a href="<?= htmlspecialchars($app['MeetingLink']) ?>" target="_blank" rel="noopener noreferrer" style="color: #4a8c5c; text-decoration: underline; font-weight: 600;">
                                                                <?= htmlspecialchars($app['MeetingLink']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span><?= htmlspecialchars($app['InterviewLocation'] ?: 'أونلاين عبر الإنترنت') ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($app['MeetingLink'])): ?>
                                                    <a href="<?= htmlspecialchars($app['MeetingLink']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm" style="background: #4a8c5c; border-color: #4a8c5c; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                                        🚀 انضم للمقابلة الآن
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Onsite Address Details -->
                                    <?php if ($locType === 'onsite' || (!empty($app['InterviewLocation']) && $locType !== 'online')): ?>
                                        <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border); padding: 12px 16px; border-radius: 8px; margin-bottom: 14px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                                <div>
                                                    <strong style="display: flex; align-items: center; gap: 6px;">
                                                        <span>🏢</span> مقر وعنوان المقابلة بالتفصيل:
                                                    </strong>
                                                    <p style="margin: 4px 0 0 0; font-size: 0.92rem; color: var(--text-primary);">
                                                        📍 <?= htmlspecialchars($app['InterviewLocation'] ?? 'مقر الشركة') ?>
                                                    </p>
                                                </div>
                                                <?php if (!empty($app['InterviewLocation'])): ?>
                                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($app['InterviewLocation']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                                                        🗺️ عرض العنوان على الخريطة
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Notes / Message -->
                                    <?php $interviewNotes = trim($app['InterviewNotes'] ?? ($app['EmployerNotes'] ?? '')); ?>
                                    <?php if (!empty($interviewNotes)): ?>
                                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); padding: 12px 16px; border-radius: 8px; margin-bottom: 14px;">
                                            <strong style="display: flex; align-items: center; gap: 6px; font-size: 0.88rem; margin-bottom: 6px; color: var(--text-secondary);">
                                                <span>💬</span> تفاصيل ورسالة القبول من الشركة:
                                            </strong>
                                            <p style="margin: 0; font-size: 0.92rem; line-height: 1.6; color: var(--text-primary); white-space: pre-wrap;"><?= htmlspecialchars($interviewNotes) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Employer Contact & Calendar Actions -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding-top: 10px; border-top: 1px solid rgba(74, 140, 92, 0.2); font-size: 0.85rem;">
                                        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                                            <?php if (!empty($app['EmployerEmail'])): ?>
                                                <span style="color: var(--text-secondary);">
                                                    ✉️ للتواصل: 
                                                    <a href="mailto:<?= htmlspecialchars($app['EmployerEmail']) ?>" style="color: inherit; text-decoration: underline;">
                                                        <?= htmlspecialchars($app['EmployerEmail']) ?>
                                                    </a>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($app['CompanyName'])): ?>
                                                <span style="color: var(--text-secondary);">
                                                    🏢 <?= htmlspecialchars($app['CompanyName']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= $calUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 6px;" title="إضافة موعد المقابلة إلى تقويم Google">
                                                📅 إضافة لتقويم Google
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif (!empty($app['EmployerNotes'])): ?>
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
