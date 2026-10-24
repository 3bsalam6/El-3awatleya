<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - تحسين السيرة الذاتية">
    <title>العواطلية - تحسين الـ CV</title>
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
            <a href="/my-applications" class="nav-link">طلباتي</a>
            <a href="/enhancements" class="nav-link active">تحسين الـ CV</a>
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
        <div class="container" style="max-width: 900px;">

            <div class="page-header">
                <h1 class="page-title">تحسين الـ CV بتاعك</h1>
                <p class="page-subtitle">اطلب تحسينات على الـ CV وهنديك اقتراحات تخليه أقوى</p>
            </div>

            <!-- Ask for Enhancements -->
            <div class="card mb-3">
                <h2 class="card-title">اطلب تحسينات جديدة</h2>
                <p class="card-desc">ارفع الـ CV واكتب إيه اللي عاوز تحسنه</p>

                <form id="enhancementForm">
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" id="cvFile" name="cv" accept=".pdf,.doc,.docx">
                        <span class="upload-icon">📄</span>
                        <h3>ارفع الـ CV بتاعك</h3>
                        <p>PDF, DOC, DOCX — أقصى حجم 5MB</p>
                    </div>

                    <div class="file-info" id="fileInfo">
                        <span>✅</span>
                        <span id="fileName">filename.pdf</span>
                        <span id="fileSize" class="text-muted">2.3 MB</span>
                        <button type="button" class="remove-file" id="removeFile">x حذف</button>
                    </div>

                    <div class="form-group mt-3">
                        <label for="enhancementMsg">إيه اللي عاوز تحسنه؟</label>
                        <textarea 
                            id="enhancementMsg" 
                            name="message" 
                            class="form-control form-textarea"
                            placeholder="مثلاً: عاوز أحسن قسم المهارات، أو عاوز أعرف إيه الناقص للوظيفة الفلانية..."
                            rows="4"
                        ></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2" id="requestEnhBtn" disabled>
                        اطلب تحسينات
                    </button>
                </form>
            </div>

            <!-- Previous Enhancement Results -->
            <div class="card">
                <h2 class="card-title">التحسينات السابقة</h2>
                <p class="card-desc">الاقتراحات اللي جاتلك قبل كده</p>

                <?php if (!empty($enhancements)): ?>
                    <?php foreach ($enhancements as $enh): ?>
                        <div class="enhancement-result">
                            <div class="enhancement-header">
                                <span class="enhancement-date"><?= date('d/m/Y', strtotime($enh['RequestedAt'])) ?><?= !empty($enh['CVFileName']) ? ' · ' . htmlspecialchars($enh['CVFileName']) : '' ?></span>
                                <?php if ($enh['Status'] === 'completed'): ?>
                                    <span class="status-badge status-completed">✅ مكتملة</span>
                                <?php elseif ($enh['Status'] === 'no_result'): ?>
                                    <span class="status-badge status-no-result">⚠️ مفيش تحسينات</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">⏳ قيد المعالجة</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($enh['RequestMessage'])): ?>
                                <p class="text-muted mb-2" style="font-size: 0.85rem;"><strong>طلبك:</strong> <?= htmlspecialchars($enh['RequestMessage']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($enh['results'])): ?>
                                <div class="enhancement-items">
                                    <?php foreach ($enh['results'] as $res): ?>
                                        <?php
                                        $pClass = 'priority-medium';
                                        $pTagClass = 'medium';
                                        $pTagText = 'أولوية متوسطة';
                                        if ($res['Priority'] === 'high') {
                                            $pClass = 'priority-high';
                                            $pTagClass = 'high';
                                            $pTagText = 'أولوية عالية';
                                        } elseif ($res['Priority'] === 'low') {
                                            $pClass = 'priority-low';
                                            $pTagClass = 'low';
                                            $pTagText = 'أولوية منخفضة';
                                        }
                                        ?>
                                        <div class="enhancement-item <?= $pClass ?>">
                                            <span class="enhancement-category">📌 <?= htmlspecialchars($res['Category']) ?></span>
                                            <p><?= htmlspecialchars($res['Suggestion']) ?></p>
                                            <span class="priority-tag <?= $pTagClass ?>"><?= $pTagText ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted" style="font-size: 0.85rem; padding: 12px 0;">
                                    الـ CV بتاعك سليم ومفيش ملاحظات جوهرية مقترحة.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card text-center" style="padding: 48px 16px; text-align: center; border: none; box-shadow: none;">
                        <span style="font-size: 3rem;">📄</span>
                        <h3 class="mt-2">لا توجد طلبات تحسين سابقة</h3>
                        <p class="text-muted mt-1">ارفع الـ CV بتاعك في النموذج أعلاه واطلب تحسينات لتظهر الاقتراحات هنا فوراً</p>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
