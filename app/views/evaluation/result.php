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
            <div class="user-pill">
                <div class="user-avatar">أ</div>
                <span>أحمد</span>
            </div>
            <a href="/login" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container">

            <!-- Score Header -->
            <div class="eval-header">
                <div class="eval-score score-ok" id="scoreCircle">
                    <span class="score-number" id="scoreValue">62</span>
                    <span class="score-label">من 100</span>
                </div>
                <div class="eval-verdict ok" id="verdictText">لسه محتاج شوية شغل</div>
                <p class="eval-summary" id="summaryText">
                    الـ CV بتاعك كويس في بعض الحاجات بس فيه مهارات ناقصة عشان تبقى 
                    <strong>Frontend Developer</strong> جاهز لسوق العمل.
                </p>
            </div>

            <div class="dashboard-grid">

                <!-- Skills Breakdown -->
                <div class="card">
                    <h2 class="card-title">تحليل المهارات</h2>
                    <p class="card-desc">دي المهارات المطلوبة ومستواك فيها</p>
                    
                    <div class="skills-section">
                        <div class="skill-item">
                            <span class="skill-name">HTML/CSS</span>
                            <div class="skill-bar-wrapper">
                                <div class="skill-bar high" style="width: 85%;"></div>
                            </div>
                            <span class="skill-percent">85%</span>
                        </div>

                        <div class="skill-item">
                            <span class="skill-name">JavaScript</span>
                            <div class="skill-bar-wrapper">
                                <div class="skill-bar mid" style="width: 60%;"></div>
                            </div>
                            <span class="skill-percent">60%</span>
                        </div>

                        <div class="skill-item">
                            <span class="skill-name">React</span>
                            <div class="skill-bar-wrapper">
                                <div class="skill-bar mid" style="width: 45%;"></div>
                            </div>
                            <span class="skill-percent">45%</span>
                        </div>

                        <div class="skill-item">
                            <span class="skill-name">Git</span>
                            <div class="skill-bar-wrapper">
                                <div class="skill-bar high" style="width: 70%;"></div>
                            </div>
                            <span class="skill-percent">70%</span>
                        </div>

                        <div class="skill-item">
                            <span class="skill-name">TypeScript</span>
                            <div class="skill-bar-wrapper">
                                <div class="skill-bar low" style="width: 20%;"></div>
                            </div>
                            <span class="skill-percent">20%</span>
                        </div>

                        <div class="skill-item">
                            <span class="skill-name">Testing</span>
                            <div class="skill-bar-wrapper">
                                <div class="skill-bar low" style="width: 15%;"></div>
                            </div>
                            <span class="skill-percent">15%</span>
                        </div>

                        <div class="skill-item">
                            <span class="skill-name">Responsive Design</span>
                            <div class="skill-bar-wrapper">
                                <div class="skill-bar high" style="width: 75%;"></div>
                            </div>
                            <span class="skill-percent">75%</span>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="card">
                    <h2 class="card-title">إيه اللي لازم تعمله</h2>
                    <p class="card-desc">خطوات تخليك جاهز</p>

                    <ul class="rec-list">
                        <li class="rec-item">
                            <span class="rec-icon">1.</span>
                            <div class="rec-content">
                                <h4>اتعلم TypeScript</h4>
                                <p>معظم الشركات دلوقتي بتطلب TypeScript. ابدأ بكورس على Udemy أو المستندات الرسمية.</p>
                            </div>
                        </li>

                        <li class="rec-item">
                            <span class="rec-icon">2.</span>
                            <div class="rec-content">
                                <h4>اتعلم Testing</h4>
                                <p>Jest و React Testing Library مهمين جداً. حاول تعمل unit tests لمشاريعك.</p>
                            </div>
                        </li>

                        <li class="rec-item">
                            <span class="rec-icon">3.</span>
                            <div class="rec-content">
                                <h4>طور مستواك في React</h4>
                                <p>اعمل مشروعين تلاتة بـ React واستخدم hooks و context و routing.</p>
                            </div>
                        </li>

                        <li class="rec-item">
                            <span class="rec-icon">4.</span>
                            <div class="rec-content">
                                <h4>حط مشاريع في الـ CV</h4>
                                <p>أضف لينكات GitHub ومشاريع حقيقية. الشركات بتحب تشوف شغلك الفعلي.</p>
                            </div>
                        </li>

                        <li class="rec-item">
                            <span class="rec-icon">5.</span>
                            <div class="rec-content">
                                <h4>اعمل Portfolio</h4>
                                <p>موقع شخصي يعرض شغلك بيفرق جداً في الانترفيوهات.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="card card-full" style="text-align: center; padding: 24px;">
                    <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 16px;">
                        عاوز تجرب تاني بعد ما تحسّن الـ CV؟
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                        <a href="/dashboard" class="btn btn-primary">ارفع CV جديد</a>
                        <button class="btn btn-outline" onclick="window.print();">اطبع النتيجة</button>
                    </div>
                </div>

            </div>

        </div>
    </main>

</div>

<script src="/public/js/app.js"></script>
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
