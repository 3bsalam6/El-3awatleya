<?php
/**
 * العواطلية - Admin Controller
 */

class AdminController
{
    private $userModel;
    private $jobModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jobModel = new Job();
    }

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        Auth::requireRole('admin');

        $userStats = $this->userModel->getStats();
        $jobStats = $this->jobModel->getStats();
        $appModel = new Application();
        $appStats = $appModel->getStats();

        $stats = array_merge($userStats, $jobStats, $appStats);
        $user = $this->userModel->findById(Auth::id());

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT TOP 20 al.*, u.FullName, u.Role
            FROM ActivityLogs al
            LEFT JOIN Users u ON al.UserID = u.UserID
            ORDER BY al.CreatedAt DESC
        ");
        $recentActivities = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    /**
     * Manage users
     */
    public function users()
    {
        Auth::requireRole('admin');

        $role = $_GET['role'] ?? null;
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $users = $this->userModel->getAll($role, $search, $status);
        $user = $this->userModel->findById(Auth::id());
        $userStats = $this->userModel->getStats();
        $profileRequests = $this->userModel->getAllPendingProfileRequests();

        require __DIR__ . '/../views/admin/users.php';
    }

    /**
     * Approve user account POST
     */
    public function approveUser()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'مفيش يوزر محدد']);
            return;
        }

        $targetUser = $this->userModel->findById($userId);
        if (!$targetUser) {
            echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
            return;
        }

        $this->userModel->approve($userId);

        // Send approval email
        $emailSent = Mailer::sendAccountApproval($targetUser['Email'], $targetUser['FullName'], $targetUser['Role']);

        // Log activity
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, Details) VALUES (?, 'account_approved', ?)");
            $stmt->execute([Auth::id(), "تمت الموافقة على حساب {$targetUser['FullName']} ({$targetUser['Email']}) كـ " . ($targetUser['Role'] === 'employer' ? 'صاحب عمل' : 'باحث عن عمل')]);
        } catch (\Exception $e) {}

        $msg = 'تمت الموافقة على الحساب وتفعيله بنجاح ✅';
        if ($emailSent) {
            $msg .= ' وتم إرسال بريد إلكتروني للمستخدم.';
        } else {
            $msg .= ' (تعذر إرسال الإيميل، برجاء مراجعة إعدادات البريد).';
        }

        echo json_encode(['success' => true, 'message' => $msg]);
    }

    /**
     * Disapprove / Reject user account POST
     */
    public function disapproveUser()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $userId = $_POST['user_id'] ?? null;
        $reason = trim($_POST['reason'] ?? '');

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'مفيش يوزر محدد']);
            return;
        }

        $targetUser = $this->userModel->findById($userId);
        if (!$targetUser) {
            echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
            return;
        }

        $this->userModel->disapprove($userId, $reason);

        // Send disapproval email
        $emailSent = Mailer::sendAccountDisapproval($targetUser['Email'], $targetUser['FullName'], $targetUser['Role'], $reason);

        // Log activity
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, Details) VALUES (?, 'account_disapproved', ?)");
            $stmt->execute([Auth::id(), "تم رفض طلب حساب {$targetUser['FullName']} ({$targetUser['Email']})"]);
        } catch (\Exception $e) {}

        $msg = 'تم رفض الحساب بنجاح ✅';
        if ($emailSent) {
            $msg .= ' وتم إرسال بريد إلكتروني للمستخدم بنتيجة الطلب.';
        } else {
            $msg .= ' (تعذر إرسال الإيميل، برجاء مراجعة إعدادات البريد).';
        }

        echo json_encode(['success' => true, 'message' => $msg]);
    }

    /**
     * Ban user POST
     */
    public function banUser()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $userId = $_POST['user_id'] ?? null;
        $reason = trim($_POST['reason'] ?? '');

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'مفيش يوزر محدد']);
            return;
        }

        $targetUser = $this->userModel->findById($userId);
        if (!$targetUser) {
            echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
            return;
        }

        $this->userModel->ban($userId, $reason);

        // Send ban email to user
        $emailSent = Mailer::sendAccountBanned($targetUser['Email'], $targetUser['FullName'], $targetUser['Role'], $reason);

        // Log activity
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, Details) VALUES (?, 'user_banned', ?)");
            $stmt->execute([Auth::id(), "تم حظر المستخدم {$targetUser['FullName']} ({$targetUser['Email']}) - السبب: " . ($reason ?: 'غير محدد')]);
        } catch (\Exception $e) {}

        $msg = 'تم حظر المستخدم بنجاح ✅';
        if ($emailSent) {
            $msg .= ' وتم إرسال بريد إلكتروني للمستخدم بسبب الحظر.';
        }

        echo json_encode(['success' => true, 'message' => $msg]);
    }

    /**
     * Unban user POST
     */
    public function unbanUser()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'مفيش يوزر محدد']);
            return;
        }

        $targetUser = $this->userModel->findById($userId);
        if (!$targetUser) {
            echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
            return;
        }

        $this->userModel->unban($userId);

        // Send unban email to user
        $emailSent = Mailer::sendAccountUnbanned($targetUser['Email'], $targetUser['FullName'], $targetUser['Role']);

        // Log activity
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, Details) VALUES (?, 'user_unbanned', ?)");
            $stmt->execute([Auth::id(), "تم رفع الحظر عن المستخدم {$targetUser['FullName']} ({$targetUser['Email']})"]);
        } catch (\Exception $e) {}

        $msg = 'تم رفع الحظر بنجاح ✅';
        if ($emailSent) {
            $msg .= ' وتم إرسال بريد إلكتروني للمستخدم بإلغاء الحظر وتفعيل حسابه.';
        }

        echo json_encode(['success' => true, 'message' => $msg]);
    }

    /**
     * Manage jobs
     */
    public function jobs()
    {
        Auth::requireRole('admin');

        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $jobs = $this->jobModel->getAll($search, $status);
        $user = $this->userModel->findById(Auth::id());
        $jobStats = $this->jobModel->getStats();

        require __DIR__ . '/../views/admin/jobs.php';
    }

    /**
     * Toggle job status POST (admin activate / deactivate)
     */
    public function toggleJob()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $jobId = $_POST['job_id'] ?? null;
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'مفيش وظيفة محددة']);
            return;
        }

        $job = $this->jobModel->findById($jobId);
        if (!$job) {
            echo json_encode(['success' => false, 'message' => 'الوظيفة غير موجودة']);
            return;
        }

        $action = $_POST['action'] ?? null;
        $reason = trim($_POST['reason'] ?? '');

        // Determine target action
        $isCurrentlyActive = (!empty($job['IsActive']) && empty($job['IsRemovedByAdmin']));
        $targetAction = $action ?: ($isCurrentlyActive ? 'deactivate' : 'activate');

        $db = Database::getInstance()->getConnection();

        if ($targetAction === 'deactivate') {
            $this->jobModel->adminDeactivate($jobId, $reason);

            // Log activity
            try {
                $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'admin_remove_job', 'Job', ?, ?, GETDATE())");
                $logDetails = "قام الأدمن بتعطيل وظيفة: " . ($job['Title'] ?? '') . ($reason ? " (السبب: {$reason})" : "");
                $logStmt->execute([Auth::id(), $jobId, $logDetails]);
            } catch (\Exception $e) {}

            // Send notification email to employer
            if (!empty($job['EmployerID'])) {
                try {
                    $employerUser = $this->userModel->findById($job['EmployerID']);
                    if ($employerUser && !empty($employerUser['Email'])) {
                        Mailer::sendJobDeactivatedToEmployer(
                            $employerUser['Email'],
                            $employerUser['FullName'],
                            $job['Title'],
                            $reason ?: 'مخالفة معايير وشروط النشر على المنصة'
                        );
                    }
                } catch (\Exception $e) {}
            }

            echo json_encode([
                'success' => true,
                'is_active' => false,
                'is_removed_by_admin' => true,
                'reason' => $reason,
                'message' => 'تم تعطيل إعلان الوظيفة بنجاح وإشعار صاحب العمل 🗑️'
            ]);
            return;
        } else {
            // Activate / Restore with custom duration if provided
            $expiresAtInput = $_POST['expires_at'] ?? null;
            $customExpiresAt = null;
            if (!empty($expiresAtInput)) {
                $ts = strtotime($expiresAtInput);
                if ($ts && $ts > time()) {
                    $customExpiresAt = date('Y-m-d H:i:s', $ts);
                }
            }

            $this->jobModel->adminActivate($jobId, $customExpiresAt);
            $updatedJob = $this->jobModel->findById($jobId);

            // Log activity
            try {
                $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'admin_restore_job', 'Job', ?, ?, GETDATE())");
                $logStmt->execute([Auth::id(), $jobId, "إعادة تفعيل وظيفة: " . ($job['Title'] ?? '') . " بواسطة الإدارة حتى " . ($updatedJob['ExpiresAt'] ?? 'بدون انتهاء')]);
            } catch (\Exception $e) {}

            // Send notification email to employer
            if (!empty($job['EmployerID'])) {
                try {
                    $employerUser = $this->userModel->findById($job['EmployerID']);
                    if ($employerUser && !empty($employerUser['Email'])) {
                        Mailer::sendJobActivatedToEmployer(
                            $employerUser['Email'],
                            $employerUser['FullName'],
                            $job['Title']
                        );
                    }
                } catch (\Exception $e) {}
            }

            $expiresFormatted = !empty($updatedJob['ExpiresAt']) ? date('d/m/Y h:i A', strtotime($updatedJob['ExpiresAt'])) : 'مفتوح';
            $expiresIso = !empty($updatedJob['ExpiresAt']) ? date('c', strtotime($updatedJob['ExpiresAt'])) : null;

            echo json_encode([
                'success' => true,
                'is_active' => true,
                'is_removed_by_admin' => false,
                'admin_review_status' => 'approved',
                'expires_at' => $updatedJob['ExpiresAt'] ?? null,
                'expires_formatted' => $expiresFormatted,
                'expires_iso' => $expiresIso,
                'message' => 'تم تفعيل إعلان الوظيفة وتحديد مدة العرض بنجاح! ♻️'
            ]);
            return;
        }
    }

    /**
     * Get job details for admin modal JSON
     */
    public function getJobDetails()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $jobId = $_GET['id'] ?? null;
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'رقم الوظيفة غير محدد']);
            return;
        }

        $job = $this->jobModel->findById($jobId);
        if (!$job) {
            echo json_encode(['success' => false, 'message' => 'الوظيفة غير موجودة']);
            return;
        }

        $employer = $this->userModel->getProfile($job['EmployerID']);
        $job['EmployerName'] = $employer['FullName'] ?? 'صاحب العمل';
        $job['CompanyName'] = $employer['CompanyName'] ?? $employer['FullName'] ?? 'الشركة';
        $job['EmployerEmail'] = $employer['Email'] ?? '';
        $job['EmployerPhone'] = $employer['Phone'] ?? '';

        $job['PostedAtFormatted'] = date('d/m/Y', strtotime($job['PostedAt']));
        $job['ExpiresAtFormatted'] = !empty($job['ExpiresAt']) ? date('d/m/Y h:i A', strtotime($job['ExpiresAt'])) : 'مفتوح';
        $job['ExpiresAtInput'] = !empty($job['ExpiresAt']) ? date('Y-m-d\TH:i', strtotime($job['ExpiresAt'])) : '';
        $job['ResubmittedAtFormatted'] = !empty($job['ResubmittedAt']) ? date('d/m/Y h:i A', strtotime($job['ResubmittedAt'])) : null;
        $job['UpdatedAtFormatted'] = !empty($job['UpdatedAt']) ? date('d/m/Y h:i A', strtotime($job['UpdatedAt'])) : null;

        echo json_encode(['success' => true, 'job' => $job]);
    }

    /**
     * Admin settings
     */
    public function settings()
    {
        Auth::requireRole('admin');
        $user = $this->userModel->findById(Auth::id());
        require __DIR__ . '/../views/admin/settings.php';
    }

    /**
     * Update admin profile POST
     */
    public function updateProfile()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $data = ['name' => trim($_POST['name'] ?? '')];

        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'الاسم مطلوب']);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE Users SET FullName = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
        $stmt->execute([$data['name'], Auth::id()]);
        Session::set('user_name', $data['name']);

        echo json_encode(['success' => true, 'message' => 'تم حفظ التغييرات ✅']);
    }

    /**
     * Approve sensitive profile change request POST
     */
    public function approveProfileRequest()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $requestId = (int)($_POST['request_id'] ?? 0);
        if (!$requestId) {
            echo json_encode(['success' => false, 'message' => 'رقم الطلب غير صحيح']);
            return;
        }

        $req = $this->userModel->approveProfileRequest($requestId, Auth::id());
        if (!$req) {
            echo json_encode(['success' => false, 'message' => 'الطلب غير موجود أو تمت معالجته بالفعل']);
            return;
        }

        $fieldName = ($req['FieldChanged'] === 'CompanyName') ? 'اسم الشركة' : 'الاسم بالكامل';
        Mailer::sendNameChangeApproved($req['Email'], $req['FullName'], $fieldName, $req['NewValue']);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, Details, CreatedAt) VALUES (?, ?, ?, GETDATE())");
        $stmt->execute([
            Auth::id(),
            'PROFILE_REQUEST_APPROVED',
            "تمت الموافقة على تعديل {$fieldName} للمستخدم {$req['Email']} إلى: {$req['NewValue']}"
        ]);

        echo json_encode(['success' => true, 'message' => 'تم قبول تعديل البيانات وتحديث الحساب بنجاح ✅']);
    }

    /**
     * Reject sensitive profile change request POST
     */
    public function rejectProfileRequest()
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $requestId = (int)($_POST['request_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$requestId) {
            echo json_encode(['success' => false, 'message' => 'رقم الطلب غير صحيح']);
            return;
        }

        $req = $this->userModel->rejectProfileRequest($requestId, Auth::id(), $reason);
        if (!$req) {
            echo json_encode(['success' => false, 'message' => 'الطلب غير موجود أو تمت معالجته بالفعل']);
            return;
        }

        $fieldName = ($req['FieldChanged'] === 'CompanyName') ? 'اسم الشركة' : 'الاسم بالكامل';
        Mailer::sendNameChangeRejected($req['Email'], $req['FullName'], $fieldName, $reason);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, Details, CreatedAt) VALUES (?, ?, ?, GETDATE())");
        $stmt->execute([
            Auth::id(),
            'PROFILE_REQUEST_REJECTED',
            "تم رفض تعديل {$fieldName} للمستخدم {$req['Email']}. السبب: " . ($reason ?: 'غير محدد')
        ]);

        echo json_encode(['success' => true, 'message' => 'تم رفض طلب التعديل وإخطار المستخدم بنجاح']);
    }
}
