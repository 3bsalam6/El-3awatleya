<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="العواطلية - نشر وظيفة جديدة">
    <title>العواطلية - نشر وظيفة</title>
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
        <div class="navbar-links">
            <a href="/employer/dashboard" class="nav-link">لوحة التحكم</a>
            <a href="/employer/post-job" class="nav-link active">نشر وظيفة</a>
            <a href="/employer/applicants" class="nav-link">المتقدمين</a>
        </div>
        <div class="navbar-actions">
            <a href="/employer/settings" class="user-pill employer-pill">
                <div class="user-avatar employer-avatar">🏢</div>
                <span><?= htmlspecialchars($user['CompanyName'] ?? $user['FullName'] ?? 'صاحب عمل') ?></span>
            </a>
            <a href="/logout" class="btn btn-ghost btn-sm">خروج</a>
        </div>
    </nav>

    <!-- Main -->
    <main class="main-content">
        <div class="container" style="max-width: 800px;">

            <div class="page-header">
                <h1 class="page-title">نشر وظيفة جديدة</h1>
                <p class="page-subtitle">ادخل تفاصيل الوظيفة وهتتنشر على المنصة</p>
            </div>

            <div class="card">
                <form id="postJobForm">

                    <!-- Job Title -->
                    <div class="form-group">
                        <label for="jobTitle">المسمى الوظيفي *</label>
                        <input 
                            type="text" 
                            id="jobTitle" 
                            name="title" 
                            class="form-control" 
                            placeholder="مثلاً: Frontend Developer"
                            required
                        >
                    </div>

                    <!-- Job Description -->
                    <div class="form-group">
                        <label for="jobDesc">وصف الوظيفة *</label>
                        <textarea 
                            id="jobDesc" 
                            name="description" 
                            class="form-control form-textarea"
                            placeholder="اكتب وصف تفصيلي للوظيفة..."
                            rows="5"
                            required
                        ></textarea>
                    </div>

                    <!-- Requirements -->
                    <div class="form-group">
                        <label for="jobReqs">المتطلبات</label>
                        <textarea 
                            id="jobReqs" 
                            name="requirements" 
                            class="form-control form-textarea"
                            placeholder="اكتب كل متطلب في سطر..."
                            rows="4"
                        ></textarea>
                    </div>

                    <!-- Two columns -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jobType">نوع الوظيفة *</label>
                            <div class="select-wrapper">
                                <select id="jobType" name="job_type" class="form-select" required>
                                    <option value="" disabled selected>اختار...</option>
                                    <option value="full-time">دوام كامل</option>
                                    <option value="part-time">دوام جزئي</option>
                                    <option value="remote">عن بعد</option>
                                    <option value="internship">تدريب</option>
                                    <option value="freelance">فريلانس</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="expLevel">مستوى الخبرة *</label>
                            <div class="select-wrapper">
                                <select id="expLevel" name="experience_level" class="form-select" required>
                                    <option value="" disabled selected>اختار...</option>
                                    <option value="entry">مبتدئ (0-1 سنة)</option>
                                    <option value="mid">متوسط (2-4 سنوات)</option>
                                    <option value="senior">خبرة (5+ سنوات)</option>
                                    <option value="lead">قيادي</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="form-group">
                        <label for="jobCity">المدينة</label>
                        <input 
                            type="text" 
                            id="jobCity" 
                            name="city" 
                            class="form-control" 
                            placeholder="مثلاً: القاهرة"
                        >
                    </div>

                    <!-- Salary Range -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="salaryMin">الحد الأدنى للمرتب (ج.م)</label>
                            <input 
                                type="number" 
                                id="salaryMin" 
                                name="salary_min" 
                                class="form-control" 
                                placeholder="5000"
                                dir="ltr"
                            >
                        </div>
                        <div class="form-group">
                            <label for="salaryMax">الحد الأقصى للمرتب (ج.م)</label>
                            <input 
                                type="number" 
                                id="salaryMax" 
                                name="salary_max" 
                                class="form-control" 
                                placeholder="15000"
                                dir="ltr"
                            >
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="form-group">
                        <label for="jobSkills">المهارات المطلوبة</label>
                        <input 
                            type="text" 
                            id="jobSkills" 
                            name="skills" 
                            class="form-control" 
                            placeholder="مثلاً: React, TypeScript, Git (فصل بفاصلة)"
                        >
                        <small class="text-muted">افصل بين كل مهارة بفاصلة</small>
                    </div>

                    <!-- Expiry -->
                    <div class="form-group">
                        <label for="expiryDate">تاريخ انتهاء الإعلان</label>
                        <input 
                            type="date" 
                            id="expiryDate" 
                            name="expires_at" 
                            class="form-control"
                            dir="ltr"
                        >
                    </div>

                    <div class="alert alert-danger" id="postJobError" style="display: none;">
                        <span>❌</span>
                        <span id="postJobErrorText"></span>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2" id="publishJobBtn">
                        انشر الوظيفة
                    </button>
                </form>
            </div>

        </div>
    </main>

</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal" style="display: none;">
    <div class="modal-card">
        <div class="modal-icon success">✅</div>
        <h2>تم نشر الوظيفة!</h2>
        <p>الوظيفة بقت متاحة على المنصة والمتقدمين يقدروا يقدموا عليها.</p>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
            <a href="/employer/dashboard" class="btn btn-primary">لوحة التحكم</a>
            <a href="/employer/post-job" class="btn btn-outline">نشر وظيفة تانية</a>
        </div>
    </div>
</div>

<script src="/public/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
