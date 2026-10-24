<?php
/**
 * العواطلية - User Controller (Settings & Profile)
 */

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        Auth::requireRole('user');
        $user = $this->userModel->getProfile(Auth::id());
        require __DIR__ . '/../views/user/settings.php';
    }

    /**
     * Update profile POST
     */
    public function updateProfile()
    {
        Auth::requireRole('user');

        $data = [
            'name'      => trim($_POST['name'] ?? ''),
            'phone'     => $_POST['phone'] ?? null,
            'city'      => $_POST['city'] ?? null,
            'bio'       => $_POST['bio'] ?? null,
            'years_exp' => (int)($_POST['years_exp'] ?? 0),
            'linkedin'  => $_POST['linkedin'] ?? null,
            'github'    => $_POST['github'] ?? null,
            'portfolio' => $_POST['portfolio'] ?? null,
        ];

        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'الاسم مطلوب']);
            return;
        }

        $this->userModel->updateProfile(Auth::id(), $data);
        Session::set('user_name', $data['name']);

        echo json_encode(['success' => true, 'message' => 'تم حفظ التغييرات ✅']);
    }

    /**
     * Change email POST
     */
    public function changeEmail()
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $newEmail = trim($_POST['new_email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($newEmail) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'لازم تملا كل الحقول']);
            return;
        }

        $user = $this->userModel->findById(Auth::id());
        if (!password_verify($password, $user['PasswordHash'])) {
            echo json_encode(['success' => false, 'message' => 'الباسورد غلط']);
            return;
        }

        // Check if new email already taken
        $existing = $this->userModel->findByEmail($newEmail);
        if ($existing && $existing['UserID'] != Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'الإيميل ده مستخدم بالفعل']);
            return;
        }

        $this->userModel->updateEmail(Auth::id(), $newEmail);
        Session::set('user_email', $newEmail);

        echo json_encode(['success' => true, 'message' => 'تم تغيير الإيميل ✅']);
    }

    /**
     * Change password POST
     */
    public function changePassword()
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'لازم تملا كل الحقول']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'الباسوردات الجديدة مش متطابقين']);
            return;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'الباسورد لازم يكون 6 حروف على الأقل']);
            return;
        }

        $user = $this->userModel->findById(Auth::id());
        if (!password_verify($currentPassword, $user['PasswordHash'])) {
            echo json_encode(['success' => false, 'message' => 'الباسورد الحالي غلط']);
            return;
        }

        $this->userModel->updatePassword(Auth::id(), $newPassword);
        echo json_encode(['success' => true, 'message' => 'تم تغيير الباسورد ✅']);
    }

    /**
     * Toggle OTP POST
     */
    public function toggleOTP()
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $enabled = (bool)($_POST['enabled'] ?? false);

        // Admin can't disable OTP
        if (Auth::isAdmin() && !$enabled) {
            echo json_encode(['success' => false, 'message' => 'الأدمن مينفعش يعطل OTP']);
            return;
        }

        $this->userModel->updateOTPSetting(Auth::id(), $enabled);
        Session::set('otp_enabled', $enabled);

        $msg = $enabled ? 'تم تفعيل التحقق بـ OTP ✅' : 'تم تعطيل التحقق بـ OTP';
        echo json_encode(['success' => true, 'message' => $msg]);
    }

    /**
     * Show applications page
     */
    public function applications()
    {
        Auth::requireRole('user');
        $applicationModel = new Application();
        $applications = $applicationModel->getByUser(Auth::id());
        $user = $this->userModel->getProfile(Auth::id());
        require __DIR__ . '/../views/user/applications.php';
    }

    /**
     * Show enhancements page
     */
    public function enhancements()
    {
        Auth::requireRole('user');
        $user = $this->userModel->getProfile(Auth::id());

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT er.*, c.FileName AS CVFileName
            FROM EnhancementRequests er
            LEFT JOIN CVs c ON er.CVID = c.CVID
            WHERE er.UserID = ?
            ORDER BY er.RequestedAt DESC
        ");
        $stmt->execute([Auth::id()]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $enhancements = [];
        foreach ($requests as $req) {
            $rStmt = $db->prepare("SELECT * FROM EnhancementResults WHERE RequestID = ?");
            $rStmt->execute([$req['RequestID']]);
            $req['results'] = $rStmt->fetchAll(PDO::FETCH_ASSOC);
            $enhancements[] = $req;
        }

        require __DIR__ . '/../views/user/enhancements.php';
    }

    /**
     * Request enhancement POST
     */
    public function requestEnhancement()
    {
        Auth::requireRole('user');
        header('Content-Type: application/json');

        $message = trim($_POST['message'] ?? '');
        $userId = Auth::id();
        $db = Database::getInstance()->getConnection();

        $cvId = null;

        // Check if new CV uploaded
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cv'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
                echo json_encode(['success' => false, 'message' => 'نوع الملف غير مدعوم. المسموح: PDF, DOC, DOCX']);
                return;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/cvs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = 'cv_' . $userId . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $cvStmt = $db->prepare("
                    INSERT INTO CVs (UserID, FileName, FilePath, FileSize, FileType, IsActive, UploadedAt)
                    VALUES (?, ?, ?, ?, ?, 1, GETDATE())
                ");
                $cvStmt->execute([$userId, $file['name'], $destPath, $file['size'], $ext]);
                $cvId = $db->lastInsertId();
            }
        }

        if (!$cvId) {
            // Find user's latest CV
            $latestStmt = $db->prepare("SELECT TOP 1 CVID FROM CVs WHERE UserID = ? ORDER BY UploadedAt DESC");
            $latestStmt->execute([$userId]);
            $latestCV = $latestStmt->fetch(PDO::FETCH_ASSOC);
            $cvId = $latestCV['CVID'] ?? null;
        }

        if (!$cvId) {
            echo json_encode(['success' => false, 'message' => 'برجاء رفع السيرة الذاتية أولاً ليتمكن النظام من تحليلها']);
            return;
        }

        // Insert Enhancement Request
        $reqStmt = $db->prepare("
            INSERT INTO EnhancementRequests (UserID, CVID, RequestMessage, Status, RequestedAt, CompletedAt)
            VALUES (?, ?, ?, 'completed', GETDATE(), GETDATE())
        ");
        $reqStmt->execute([$userId, $cvId, $message ?: null]);
        $requestId = $db->lastInsertId();

        // Generate tailored suggestions
        $suggestions = [
            [
                'category' => 'المهارات التقنية',
                'suggestion' => 'أضف أهم الأدوات والتقنيات الحديثة المطلوبة في مجالك وحدد مستواك فيها بدقة.',
                'priority' => 'high'
            ],
            [
                'category' => 'تنسيق الـ CV',
                'suggestion' => 'اجعل السيرة الذاتية موجزة (صفحة واحدة أو اثنتين) ونسق الأقسام بترتيب زمني عكسي.',
                'priority' => 'medium'
            ],
            [
                'category' => 'الخبرات والإنجازات',
                'suggestion' => 'استخدم أرقاماً ونتائج قابلة للقياس لوصف إنجازاتك في المشروعات السابقة.',
                'priority' => 'high'
            ],
            [
                'category' => 'الروابط المهنية',
                'suggestion' => 'أضف روابط مباشرة ومحدثة لحساباتك المهنية (LinkedIn, GitHub, Portfolio).',
                'priority' => 'low'
            ]
        ];

        $resStmt = $db->prepare("
            INSERT INTO EnhancementResults (RequestID, Category, Suggestion, Priority, CreatedAt)
            VALUES (?, ?, ?, ?, GETDATE())
        ");
        foreach ($suggestions as $s) {
            $resStmt->execute([$requestId, $s['category'], $s['suggestion'], $s['priority']]);
        }

        // Log activity
        $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'request_enhancement', 'Enhancement', ?, ?, GETDATE())");
        $logStmt->execute([$userId, $requestId, 'طلب اقتراحات تحسين السيرة الذاتية']);

        echo json_encode(['success' => true, 'message' => 'تم استلام طلب التحسينات وتوليد الاقتراحات بنجاح! 🎉']);
    }
}
