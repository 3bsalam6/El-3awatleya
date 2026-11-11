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
        $pendingRequests = $this->userModel->getPendingRequestsForUser(Auth::id());
        $pendingNameChange = $pendingRequests['FullName'] ?? null;
        require __DIR__ . '/../views/user/settings.php';
    }

    /**
     * Update profile POST
     */
    public function updateProfile()
    {
        Auth::requireRole('user');
        header('Content-Type: application/json');

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $rawYearsExp = $_POST['years_exp'] ?? 0;
        $linkedin = trim($_POST['linkedin'] ?? '');
        $github = trim($_POST['github'] ?? '');
        $portfolio = trim($_POST['portfolio'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'الاسم مطلوب']);
            return;
        }

        // Point 7: Experience cannot be negative
        if ((int)$rawYearsExp < 0) {
            echo json_encode(['success' => false, 'message' => 'سنوات الخبرة لا يمكن أن تكون بالسالب']);
            return;
        }
        $yearsExp = max(0, min(60, (int)$rawYearsExp));

        // Point 8: Bio character limit (max 500 characters)
        if (mb_strlen($bio) > 500) {
            echo json_encode(['success' => false, 'message' => 'نبذة عنك يجب ألا تتجاوز 500 حرف (المكتوب حالياً: ' . mb_strlen($bio) . ' حرف)']);
            return;
        }

        // Point 9: GitHub and LinkedIn are REQUIRED, portfolio optional
        if (empty($linkedin)) {
            echo json_encode(['success' => false, 'message' => 'رابط حساب LinkedIn مطلوب وإجباري']);
            return;
        }
        if (!filter_var($linkedin, FILTER_VALIDATE_URL) || stripos($linkedin, 'linkedin.com') === false) {
            echo json_encode(['success' => false, 'message' => 'يرجى إدخال رابط LinkedIn صحيح (مثال: https://linkedin.com/in/username)']);
            return;
        }

        if (empty($github)) {
            echo json_encode(['success' => false, 'message' => 'رابط حساب GitHub مطلوب وإجباري']);
            return;
        }
        if (!filter_var($github, FILTER_VALIDATE_URL) || stripos($github, 'github.com') === false) {
            echo json_encode(['success' => false, 'message' => 'يرجى إدخال رابط GitHub صحيح (مثال: https://github.com/username)']);
            return;
        }

        if (!empty($portfolio) && !filter_var($portfolio, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'يرجى إدخال رابط معرض أعمال (Portfolio) صحيح يبدأ بـ https://']);
            return;
        }

        // Validate Egyptian phone format if provided
        if (!empty($phone) && !preg_match('/^01[0125][0-9]{8}$/', $phone)) {
            echo json_encode(['success' => false, 'message' => 'رقم الموبايل يجب أن يكون رقماً مصرياً صحيحاً مكوناً من 11 رقماً ويبدأ بـ 01']);
            return;
        }

        // Handle avatar upload if included in profile form
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $this->processAvatarUpload(Auth::id());
        }

        $currentUser = $this->userModel->findById(Auth::id());
        $currentProfile = $this->userModel->getProfile(Auth::id());
        $currentPhone = $currentProfile['Phone'] ?? '';

        $isNameChanged = ($name !== $currentUser['FullName']);
        $isPhoneChanged = (!empty($phone) && $phone !== $currentPhone);

        // Update non-sensitive profile fields immediately (preserves Phone in DB)
        $this->userModel->updateUserProfileNonSensitive(Auth::id(), [
            'city'      => $city,
            'bio'       => $bio,
            'years_exp' => $yearsExp,
            'linkedin'  => $linkedin,
            'github'    => $github,
            'portfolio' => $portfolio,
        ]);

        $pendingParts = [];

        // Point 5: FullName and Phone require admin approval
        if ($isNameChanged) {
            $this->userModel->requestNameChange(Auth::id(), 'FullName', $currentUser['FullName'], $name);
            $pendingParts[] = 'الاسم إلى "' . $name . '"';
        }

        if ($isPhoneChanged) {
            $this->userModel->requestNameChange(Auth::id(), 'Phone', $currentPhone, $phone);
            $pendingParts[] = 'رقم الموبايل إلى "' . $phone . '"';
        }

        if (!empty($pendingParts)) {
            echo json_encode([
                'success' => true,
                'pending' => true,
                'message' => 'تم حفظ البيانات! طلب تعديل (' . implode(' و ', $pendingParts) . ') تم إرساله للإدارة للموافقة عليه ⏳'
            ]);
        } else {
            echo json_encode(['success' => true, 'message' => 'تم حفظ التغييرات بنجاح ✅']);
        }
    }

    /**
     * Upload user avatar via AJAX
     */
    public function uploadAvatar()
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $res = $this->processAvatarUpload(Auth::id());
        echo json_encode($res);
    }

    /**
     * Process avatar upload helper
     */
    private function processAvatarUpload($userId)
    {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'لم يتم تحديد أي صورة صالحة للرفع'];
        }

        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            return ['success' => false, 'message' => 'نوع الملف غير مدعوم. المسموح: JPG, PNG, WEBP, GIF'];
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'حجم الصورة كبير جداً (الحد الأقصى: 5 ميجابايت)'];
        }

        $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'message' => 'فشل في حفظ الصورة على الخادم'];
        }

        $webPath = '/public/uploads/avatars/' . $fileName;
        $this->userModel->updateProfilePicture($userId, $webPath);

        // If employer role, sync company logo as well
        $user = $this->userModel->findById($userId);
        if ($user && $user['Role'] === 'employer') {
            $this->userModel->updateCompanyLogo($userId, $webPath);
        }

        return [
            'success' => true,
            'avatar_url' => $webPath,
            'message' => 'تم رفع وتحديث الصورة بنجاح! 📸'
        ];
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar()
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId = Auth::id();
        $this->userModel->deleteProfilePicture($userId);

        $user = $this->userModel->findById($userId);
        if ($user && $user['Role'] === 'employer') {
            $this->userModel->deleteCompanyLogo($userId);
        }

        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الصورة واستعادة الرمز الافتراضي بنجاح'
        ]);
    }

    /**
     * Change email POST (requires Admin approval)
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

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'صيغة البريد الإلكتروني غير صحيحة']);
            return;
        }

        $user = $this->userModel->findById(Auth::id());
        if (!password_verify($password, $user['PasswordHash'])) {
            echo json_encode(['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة']);
            return;
        }

        if (strtolower($newEmail) === strtolower($user['Email'])) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني الجديد هو نفس بريدك الحالي']);
            return;
        }

        // Check if new email already taken
        $existing = $this->userModel->findByEmail($newEmail);
        if ($existing && $existing['UserID'] != Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني ده مستخدم بالفعل بحساب آخر']);
            return;
        }

        // Point 5: Changing email requires admin approval!
        $this->userModel->requestNameChange(Auth::id(), 'Email', $user['Email'], $newEmail);

        echo json_encode([
            'success' => true,
            'email_pending' => true,
            'message' => 'تم إرسال طلب تغيير البريد الإلكتروني إلى "' . $newEmail . '" لإدارة المنصة للمراجعة والموافقة عليه ⏳'
        ]);
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

        if (strlen($newPassword) < 6 || strlen($newPassword) > 30) {
            echo json_encode(['success' => false, 'message' => 'الباسورد لازم يكون بين 6 و 30 حرفاً']);
            return;
        }

        // Point 10: New password must be different from current password
        if ($currentPassword === $newPassword) {
            echo json_encode(['success' => false, 'message' => 'كلمة المرور الجديدة يجب أن تكون مختلفة عن كلمة المرور الحالية']);
            return;
        }

        $user = $this->userModel->findById(Auth::id());
        if (!password_verify($currentPassword, $user['PasswordHash'])) {
            echo json_encode(['success' => false, 'message' => 'الباسورد الحالي غلط']);
            return;
        }

        if (password_verify($newPassword, $user['PasswordHash'])) {
            echo json_encode(['success' => false, 'message' => 'كلمة المرور الجديدة يجب أن تكون مختلفة عن كلمة المرور الحالية']);
            return;
        }

        $this->userModel->updatePassword(Auth::id(), $newPassword);
        echo json_encode(['success' => true, 'message' => 'تم تغيير الباسورد بنجاح ✅']);
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
