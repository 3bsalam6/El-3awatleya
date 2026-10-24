<?php
/**
 * العواطلية - Evaluation Controller
 */

class EvaluationController
{
    private $db;
    private $userModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();
    }

    /**
     * Show evaluation result page
     */
    public function showResult()
    {
        Auth::requireRole('user');
        $userId = Auth::id();
        $user = $this->userModel->getProfile($userId);

        // Fetch latest evaluation for this user
        $stmt = $this->db->prepare("
            SELECT TOP 1 *
            FROM Evaluations
            WHERE UserID = ?
            ORDER BY EvaluatedAt DESC
        ");
        $stmt->execute([$userId]);
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

        $skills = [];
        $courses = [];

        if ($evaluation) {
            $evalId = $evaluation['EvaluationID'];

            $sStmt = $this->db->prepare("SELECT * FROM EvaluationSkills WHERE EvaluationID = ? ORDER BY ScorePercent DESC");
            $sStmt->execute([$evalId]);
            $skills = $sStmt->fetchAll(PDO::FETCH_ASSOC);

            $cStmt = $this->db->prepare("SELECT * FROM RecommendedCourses WHERE EvaluationID = ?");
            $cStmt->execute([$evalId]);
            $courses = $cStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require __DIR__ . '/../views/evaluation/result.php';
    }

    /**
     * Evaluate uploaded CV POST
     */
    public function evaluate()
    {
        Auth::requireRole('user');
        $userId = Auth::id();

        $targetRole = trim($_POST['job_title'] ?? 'Full Stack Developer');
        if (empty($targetRole)) {
            $targetRole = 'Frontend Developer';
        }

        if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'برجاء اختيار ملف السيرة الذاتية (PDF, DOC, DOCX)');
            Session::redirect('/dashboard');
            return;
        }

        $file = $_FILES['cv'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            Session::flash('error', 'نوع الملف غير مدعوم. المسموح: PDF, DOC, DOCX');
            Session::redirect('/dashboard');
            return;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/cvs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'cv_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::flash('error', 'فشل في رفع الملف، حاول مرة تانية');
            Session::redirect('/dashboard');
            return;
        }

        // Insert into CVs
        $cvStmt = $this->db->prepare("
            INSERT INTO CVs (UserID, FileName, FilePath, FileSize, FileType, IsActive, UploadedAt)
            VALUES (?, ?, ?, ?, ?, 1, GETDATE())
        ");
        $cvStmt->execute([$userId, $file['name'], $destPath, $file['size'], $ext]);
        $cvId = $this->db->lastInsertId();

        // Skill matrix depending on target role
        $roleProfiles = [
            'frontend' => [
                'name' => 'Frontend Developer',
                'skills' => [
                    ['HTML/CSS', 85, 'high'],
                    ['JavaScript (ES6+)', 75, 'high'],
                    ['React.js / Next.js', 65, 'mid'],
                    ['Git & GitHub', 70, 'high'],
                    ['TypeScript', 40, 'mid'],
                    ['Unit Testing', 30, 'low'],
                    ['Responsive Design', 80, 'high']
                ],
                'courses' => [
                    ['كورس React.js المتقدم', 'Coursera', 'https://coursera.org', 'React.js', 'تعلم بناء تطبيقات ويب تفاعلية متقدمة بمكتبة React', 0],
                    ['دورة TypeScript الشاملة', 'Udemy', 'https://udemy.com', 'TypeScript', 'فهم الأنواع وتطوير كود قوي مع React', 0],
                    ['Testing JavaScript Applications', 'YouTube', 'https://youtube.com', 'Testing', 'أساسيات الاختبار واختبار المكونات', 1]
                ]
            ],
            'backend' => [
                'name' => 'Backend Developer',
                'skills' => [
                    ['PHP / Node.js', 80, 'high'],
                    ['SQL & Databases', 75, 'high'],
                    ['RESTful APIs', 70, 'high'],
                    ['Git & Version Control', 70, 'high'],
                    ['Docker & Containers', 45, 'mid'],
                    ['System Architecture', 40, 'mid'],
                    ['Testing & CI/CD', 35, 'low']
                ],
                'courses' => [
                    ['تصميم وتطوير REST APIs المتطورة', 'Udemy', 'https://udemy.com', 'RESTful APIs', 'معايير الأمان والتصميم لقواعد البيانات وواجهات البرمجة', 0],
                    ['أساسيات Docker للحاويات', 'Coursera', 'https://coursera.org', 'Docker', 'نشر وإدارة التطبيقات داخل حاويات معزولة', 0],
                    ['قواعد بيانات SQL Server المتقدمة', 'YouTube', 'https://youtube.com', 'SQL', 'الاستعلامات المتقدمة وضبط الأداء والفهارس', 1]
                ]
            ],
            'fullstack' => [
                'name' => 'Full Stack Developer',
                'skills' => [
                    ['Frontend (HTML/CSS/JS)', 80, 'high'],
                    ['Backend Development', 70, 'high'],
                    ['Database Design', 65, 'mid'],
                    ['Git & Deployment', 60, 'mid'],
                    ['REST APIs', 70, 'high'],
                    ['Security & Auth', 50, 'mid']
                ],
                'courses' => [
                    ['Full Stack Web Development Bootcamp', 'Udemy', 'https://udemy.com', 'Fullstack', 'بناء مشروع كامل من الصفر للواجهة وقواعد البيانات', 0],
                    ['Web Security & JWT Authentication', 'Coursera', 'https://coursera.org', 'Security', 'تأمين التطبيقات والتحقق من الهوية والصلاحيات', 1]
                ]
            ]
        ];

        $roleKey = strtolower($targetRole);
        $profile = $roleProfiles[$roleKey] ?? $roleProfiles['frontend'];

        // Calculate score
        $totalSkillScore = array_sum(array_column($profile['skills'], 1));
        $overallScore = round($totalSkillScore / count($profile['skills']));

        $verdict = 'good';
        $verdictArabic = 'مستواك جيد ومتقارب مع المتطلبات';
        if ($overallScore >= 80) {
            $verdict = 'excellent';
            $verdictArabic = 'ممتاز! سيرتك الذاتية جاهزة للمنافسة بقوة';
        } elseif ($overallScore < 50) {
            $verdict = 'needs_work';
            $verdictArabic = 'محتاج شغل إضافي وتطوير بعض المهارات';
        }

        $summary = "الـ CV بتاعك يمتلك نقاط قوة جيدة لوظيفة {$profile['name']}، ولكن توجد مهارات أساسية ننصح بالتركيز عليها لزيادة فرص قبولك.";

        // Insert Evaluation
        $evalStmt = $this->db->prepare("
            INSERT INTO Evaluations (UserID, CVID, TargetRole, OverallScore, Verdict, Summary, IsLowScore, EvaluatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE())
        ");
        $isLow = $overallScore < 50 ? 1 : 0;
        $evalStmt->execute([$userId, $cvId, $profile['name'], $overallScore, $verdict, $summary, $isLow]);
        $evalId = $this->db->lastInsertId();

        // Insert Evaluation Skills
        $skStmt = $this->db->prepare("
            INSERT INTO EvaluationSkills (EvaluationID, SkillName, ScorePercent, Level)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($profile['skills'] as $sk) {
            $skStmt->execute([$evalId, $sk[0], $sk[1], $sk[2]]);
        }

        // Insert Recommended Courses
        $crsStmt = $this->db->prepare("
            INSERT INTO RecommendedCourses (EvaluationID, CourseName, Provider, CourseURL, SkillTargeted, Description, IsFree)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($profile['courses'] as $crs) {
            $crsStmt->execute([$evalId, $crs[0], $crs[1], $crs[2], $crs[3], $crs[4], $crs[5]]);
        }

        // Log Activity
        $logStmt = $this->db->prepare("
            INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt)
            VALUES (?, 'evaluate_cv', 'Evaluation', ?, ?, GETDATE())
        ");
        $logStmt->execute([$userId, $evalId, "قام بتقييم السيرة الذاتية لوظيفة {$profile['name']} بنتيجة {$overallScore}%"]);

        Session::redirect('/evaluation');
    }
}
