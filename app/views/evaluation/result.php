<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - نتيجة تقييم سيرتك الذاتية">
    <title>العواطلية - نتيجة التقييم</title>
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
        <div class="navbar-actions">
            <a href="/dashboard" class="btn btn-outline btn-sm">← الداشبورد</a>
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

            <?php if (!empty($evaluation)): ?>
                <?php
                $score = (int)($evaluation['OverallScore'] ?? 0);
                $scoreClass = 'score-ok';
                $verdictClass = 'ok';
                $verdictText = 'مستواك جيد ومتقارب مع المتطلبات';

                if ($score >= 80) {
                    $scoreClass = 'score-good';
                    $verdictClass = 'good';
                    $verdictText = 'ممتاز! سيرتك الذاتية جاهزة للمنافسة بقوة';
                } elseif ($score < 50) {
                    $scoreClass = 'score-poor';
                    $verdictClass = 'poor';
                    $verdictText = 'تحتاج إلى تطوير وإضافة المزيد من المهارات';
                }
                ?>
                <!-- Score Header -->
                <div class="eval-header">
                    <div class="eval-score <?= $scoreClass ?>" id="scoreCircle">
                        <span class="score-number" id="scoreValue"><?= $score ?></span>
                        <span class="score-label">من 100</span>
                    </div>
                    <div class="eval-verdict <?= $verdictClass ?>" id="verdictText"><?= $verdictText ?></div>
                    <p class="eval-summary" id="summaryText">
                        <?= htmlspecialchars($evaluation['Summary'] ?? '') ?>
                        <?php if (!empty($evaluation['TargetRole'])): ?>
                            <br><strong>الوظيفة المستهدفة: <?= htmlspecialchars($evaluation['TargetRole']) ?></strong>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="dashboard-grid">

                    <!-- Skills Breakdown -->
                    <div class="card">
                        <h2 class="card-title">تحليل المهارات</h2>
                        <p class="card-desc">المهارات المطلوبة ومستواك المقدر فيها</p>
                        
                        <div class="skills-section">
                            <?php if (!empty($skills)): ?>
                                <?php foreach ($skills as $sk): ?>
                                    <?php
                                    $lvl = $sk['Level'] ?? 'mid';
                                    $pct = (int)($sk['ScorePercent'] ?? 50);
                                    ?>
                                    <div class="skill-item">
                                        <span class="skill-name"><?= htmlspecialchars($sk['SkillName']) ?></span>
                                        <div class="skill-bar-wrapper">
                                            <div class="skill-bar <?= $lvl ?>" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                        <span class="skill-percent"><?= $pct ?>%</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted" style="padding: 16px 0;">لا تتوفر مهارات تفصيلية لهذا التقييم</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recommendations -->
                    <div class="card">
                        <h2 class="card-title">إيه اللي لازم تعمله</h2>
                        <p class="card-desc">خطوات عملية لرفع فرص قبولك</p>

                        <ul class="rec-list">
                            <li class="rec-item">
                                <span class="rec-icon">1.</span>
                                <div class="rec-content">
                                    <h4>ركز على المهارات الأساسية</h4>
                                    <p>اطّلع على المهارات التي حصلت فيها على تقييم منخفض وحاول تدعيمها بتطبيقات عملية.</p>
                                </div>
                            </li>

                            <li class="rec-item">
                                <span class="rec-icon">2.</span>
                                <div class="rec-content">
                                    <h4>أضف مشاريع قابلة للتجربة</h4>
                                    <p>أصحاب الأعمال يفضلون رؤية أكواد حية ومستودعات GitHub وروابط لمشاريعك.</p>
                                </div>
                            </li>

                            <li class="rec-item">
                                <span class="rec-icon">3.</span>
                                <div class="rec-content">
                                    <h4>استخدم لغة الأرقام</h4>
                                    <p>وضح أثر عملك بالأرقام ونسب التحسين بدل الاكتفاء بالوصف العام.</p>
                                </div>
                            </li>

                            <li class="rec-item">
                                <span class="rec-icon">4.</span>
                                <div class="rec-content">
                                    <h4>طلب تحسينات مخصصة</h4>
                                    <p>يمكنك استخدام قسم تحسين الـ CV للحصول على اقتراحات دقيقة ومفصلة.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Recommended Courses -->
                    <?php if (!empty($courses)): ?>
                        <div class="card card-full courses-section" id="coursesSection">
                            <div class="courses-header">
                                <h2 class="card-title">📚 كورسات مقترحة</h2>
                                <span class="tag tag-warning">موصى بها لتقوية ملفك</span>
                            </div>
                            <p class="card-desc">دورات وكورسات تساعدك على إتقان المهارات المطلوبة</p>

                            <div class="courses-grid">
                                <?php foreach ($courses as $c): ?>
                                    <div class="course-card">
                                        <div class="course-provider">
                                            <span class="course-provider-icon">🎓</span>
                                            <span><?= htmlspecialchars($c['Provider'] ?? 'منصة تعليمية') ?></span>
                                            <span class="course-free-badge <?= !empty($c['IsFree']) ? 'free' : '' ?>"><?= !empty($c['IsFree']) ? 'مجاني' : 'مدفوع' ?></span>
                                        </div>
                                        <h4 class="course-name"><?= htmlspecialchars($c['CourseName']) ?></h4>
                                        <p class="course-skill">المهارة المستهدفة: <strong><?= htmlspecialchars($c['SkillTargeted']) ?></strong></p>
                                        <?php if (!empty($c['Description'])): ?>
                                            <p class="course-desc"><?= htmlspecialchars($c['Description']) ?></p>
                                        <?php endif; ?>
                                        <a href="<?= htmlspecialchars($c['CourseURL'] ?? '#') ?>" class="btn btn-outline btn-sm mt-1" target="_blank" rel="noopener">افتح الكورس ↗</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="card card-full" style="text-align: center; padding: 24px;">
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 16px;">
                            عاوز تجرب تاني بعد ما تحسّن الـ CV؟
                        </p>
                        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                            <a href="/dashboard" class="btn btn-primary">ارفع CV جديد</a>
                            <a href="/enhancements" class="btn btn-outline">اطلب تحسينات</a>
                            <button class="btn btn-outline" onclick="window.print();">اطبع النتيجة</button>
                        </div>
                    </div>

                </div>
            <?php else: ?>
                <div class="card text-center" style="padding: 60px 16px; text-align: center; max-width: 650px; margin: 40px auto;">
                    <span style="font-size: 3.5rem;">📄</span>
                    <h2 class="mt-2">لم يتم تقييم سيرة ذاتية بعد</h2>
                    <p class="text-muted mt-2">قم برفع سيرتك الذاتية من خلال لوحة التحكم لتحصل على تقييم دقيق وشامل لمهاراتك وتوصيات مخصصة للوظيفة المطلوبة.</p>
                    <a href="/dashboard" class="btn btn-primary mt-3" style="display: inline-block;">الانتقال إلى الداشبورد لرفع الـ CV</a>
                </div>
            <?php endif; ?>

        </div>
    </main>

</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
<script>
    // Animate skill bars on load
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.skill-bar');
        bars.forEach(function(bar) {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(function() {
                bar.style.width = width;
            }, 300);
        });
    });
</script>
</body>
</html>
