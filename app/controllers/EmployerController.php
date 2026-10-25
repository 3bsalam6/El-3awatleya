<?php
/**
 * العواطلية - Employer Controller
 */

class EmployerController
{
    private $userModel;
    private $jobModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jobModel = new Job();
    }

    /**
     * Employer dashboard
     */
    public function dashboard()
    {
        Auth::requireRole('employer');

        $user = $this->userModel->getProfile(Auth::id());
        $jobs = $this->jobModel->getByEmployer(Auth::id());
        $appModel = new Application();
        $applications = $appModel->getByEmployer(Auth::id());

        $stats = [
            'total_jobs' => count($jobs),
            'active_jobs' => count(array_filter($jobs, fn($j) => $j['IsActive'] && empty($j['IsRemovedByAdmin']) && (empty($j['ExpiresAt']) || strtotime($j['ExpiresAt']) > time()))),
            'total_applications' => count($applications),
            'pending' => count(array_filter($applications, fn($a) => $a['Status'] === 'pending')),
            'shortlisted' => count(array_filter($applications, fn($a) => $a['Status'] === 'shortlisted')),
        ];

        require __DIR__ . '/../views/employer/dashboard.php';
    }

    /**
     * Post job form
     */
    public function showPostJob()
    {
        Auth::requireRole('employer');
        $user = $this->userModel->getProfile(Auth::id());
        require __DIR__ . '/../views/employer/post-job.php';
    }

    /**
     * Handle post job POST
     */
    public function postJob()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $data = [
            'employer_id' => Auth::id(),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'requirements' => trim($_POST['requirements'] ?? ''),
            'job_type' => $_POST['job_type'] ?? 'full-time',
            'experience_level' => $_POST['experience_level'] ?? 'entry',
            'city' => trim($_POST['city'] ?? ''),
            'salary_min' => !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null,
            'salary_max' => !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null,
            'skills' => trim($_POST['skills'] ?? ''),
            'expires_at' => null,
        ];

        $expiresAt = !empty($_POST['expires_at']) ? trim($_POST['expires_at']) : null;
        if ($expiresAt) {
            $expiresTimestamp = strtotime($expiresAt);
            if (!$expiresTimestamp || $expiresTimestamp <= time()) {
                echo json_encode(['success' => false, 'message' => 'تاريخ وساعة انتهاء الإعلان لازم يكون في وقت مستقبلي وغير منتهي']);
                return;
            }
            $data['expires_at'] = date('Y-m-d H:i:s', $expiresTimestamp);
        }

        if (empty($data['title']) || empty($data['description'])) {
            echo json_encode(['success' => false, 'message' => 'لازم تملا الحقول المطلوبة (المسمى الوظيفي والوصف)']);
            return;
        }

        $jobId = $this->jobModel->create($data);
        echo json_encode(['success' => true, 'message' => 'تم نشر الوظيفة بنجاح! 🎉', 'job_id' => $jobId]);
    }

    /**
     * Show edit job form
     */
    public function showEditJob()
    {
        Auth::requireRole('employer');
        $jobId = $_GET['id'] ?? null;
        if (!$jobId) {
            header('Location: /employer/dashboard');
            exit;
        }

        $job = $this->jobModel->findById($jobId);
        if (!$job || $job['EmployerID'] != Auth::id()) {
            Session::flash('error', 'الوظيفة غير موجودة أو غير مصرح لك بتعديلها');
            header('Location: /employer/dashboard');
            exit;
        }

        $user = $this->userModel->getProfile(Auth::id());
        require __DIR__ . '/../views/employer/edit-job.php';
    }

    /**
     * Handle edit job POST (resubmit for admin review)
     */
    public function editJob()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $jobId = $_POST['job_id'] ?? null;
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'رقم الوظيفة غير محدد']);
            return;
        }

        $job = $this->jobModel->findById($jobId);
        if (!$job || $job['EmployerID'] != Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'غير مصرح لك بتعديل هذه الوظيفة']);
            return;
        }

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'requirements' => trim($_POST['requirements'] ?? ''),
            'job_type' => $_POST['job_type'] ?? 'full-time',
            'experience_level' => $_POST['experience_level'] ?? 'entry',
            'city' => trim($_POST['city'] ?? ''),
            'salary_min' => !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null,
            'salary_max' => !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null,
            'skills' => trim($_POST['skills'] ?? ''),
            'employer_notes' => trim($_POST['employer_notes'] ?? ''),
            'expires_at' => null,
        ];

        $expiresAt = !empty($_POST['expires_at']) ? trim($_POST['expires_at']) : null;
        if (empty($expiresAt)) {
            echo json_encode(['success' => false, 'message' => 'يرجى تحديد مدة أو تاريخ وساعة انتهاء التقديم']);
            return;
        }

        $expiresTimestamp = strtotime($expiresAt);
        if (!$expiresTimestamp || $expiresTimestamp <= time()) {
            echo json_encode(['success' => false, 'message' => 'تاريخ وساعة انتهاء الإعلان لازم يكون في وقت مستقبلي وغير منتهي']);
            return;
        }
        $data['expires_at'] = date('Y-m-d H:i:s', $expiresTimestamp);

        if (empty($data['title']) || empty($data['description'])) {
            echo json_encode(['success' => false, 'message' => 'لازم تملا الحقول المطلوبة (المسمى الوظيفي والوصف)']);
            return;
        }

        // Resubmit for admin review
        $res = $this->jobModel->resubmitForReview($jobId, Auth::id(), $data);

        if ($res) {
            // Log activity
            try {
                $db = Database::getInstance()->getConnection();
                $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'resubmit_job_review', 'Job', ?, ?, GETDATE())");
                $logStmt->execute([Auth::id(), $jobId, "قام صاحب العمل بتعديل وظيفة (#{$jobId}: {$data['title']}) وإعادة إرسالها لمراجعة الإدارة"]);
            } catch (\Exception $e) {}

            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ التعديلات وإرسال الوظيفة لإدارة الموقع بنجاح! 🚀 ستتم مراجعتها واعتمادها قريباً.',
                'job_id' => $jobId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حفظ التعديلات، يرجى المحاولة لاحقاً']);
        }
    }

    /**
     * View applicants
     */
    public function applicants()
    {
        Auth::requireRole('employer');

        $user = $this->userModel->getProfile(Auth::id());
        $jobs = $this->jobModel->getByEmployer(Auth::id());
        $appModel = new Application();
        $jobId = $_GET['job_id'] ?? null;

        if ($jobId) {
            $applications = $appModel->getByJob($jobId);
            $job = $this->jobModel->findById($jobId);
        } else {
            $applications = $appModel->getByEmployer(Auth::id());
            $job = null;
        }

        require __DIR__ . '/../views/employer/applicants.php';
    }

    /**
     * Update application status POST
     */
    public function updateApplicationStatus()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $appId = $_POST['application_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$appId || !in_array($status, ['pending', 'reviewed', 'shortlisted', 'accepted', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'بيانات غلط']);
            return;
        }

        $appModel = new Application();
        $application = $appModel->findById($appId);

        // Verify the employer owns this job
        if (!$application || $application['EmployerID'] != Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'مش مسموحلك']);
            return;
        }

        $appModel->updateStatus($appId, $status);
        echo json_encode(['success' => true, 'message' => 'تم تحديث الحالة ✅']);
    }

    /**
     * Schedule interview page
     */
    public function showInterview()
    {
        Auth::requireRole('employer');
        $user = $this->userModel->getProfile(Auth::id());

        $appId = $_GET['application_id'] ?? null;
        $appModel = new Application();
        $application = $appId ? $appModel->findById($appId) : null;

        require __DIR__ . '/../views/employer/interview.php';
    }

    /**
     * Employer settings
     */
    public function settings()
    {
        Auth::requireRole('employer');
        $user = $this->userModel->getProfile(Auth::id());
        $pendingNameChange = $this->userModel->getPendingNameChange(Auth::id(), 'CompanyName');
        require __DIR__ . '/../views/employer/settings.php';
    }

    /**
     * Update employer profile POST
     */
    public function updateProfile()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $data = [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'industry'     => $_POST['industry'] ?? null,
            'company_size' => $_POST['company_size'] ?? null,
            'city'         => $_POST['city'] ?? null,
            'website'      => $_POST['website'] ?? null,
            'address'      => $_POST['address'] ?? null,
            'description'  => $_POST['description'] ?? null,
        ];

        if (empty($data['company_name'])) {
            echo json_encode(['success' => false, 'message' => 'اسم الشركة مطلوب']);
            return;
        }

        $currentProfile = $this->userModel->getProfile(Auth::id());
        $currentCompanyName = $currentProfile['CompanyName'] ?? $currentProfile['FullName'] ?? '';
        $isNameChanged = ($data['company_name'] !== $currentCompanyName);

        // Update non-sensitive employer fields immediately
        $this->userModel->updateEmployerProfileOnly(Auth::id(), $data);

        if ($isNameChanged) {
            $this->userModel->requestNameChange(Auth::id(), 'CompanyName', $currentCompanyName, $data['company_name']);
            echo json_encode([
                'success' => true,
                'name_pending' => true,
                'message' => 'تم حفظ البيانات! طلب تغيير اسم الشركة إلى "' . $data['company_name'] . '" تم إرساله للإدارة للموافقة عليه ⏳'
            ]);
        } else {
            echo json_encode(['success' => true, 'message' => 'تم حفظ التغييرات بنجاح ✅']);
        }
    }

    /**
     * Schedule interview POST
     */
    public function scheduleInterview()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $appId = $_POST['application_id'] ?? null;
        $datetime = trim($_POST['interview_datetime'] ?? '');
        $date = trim($_POST['interview_date'] ?? '');
        $time = trim($_POST['interview_time'] ?? '');
        if (empty($datetime) && !empty($date) && !empty($time)) {
            $datetime = $date . ' ' . $time;
        }

        $locationType = $_POST['location_type'] ?? 'onsite';
        $location = trim($_POST['location'] ?? '');
        $meetingLink = trim($_POST['meeting_link'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$appId || empty($datetime)) {
            echo json_encode(['success' => false, 'message' => 'برجاء تحديد تاريخ وساعة المقابلة']);
            return;
        }

        $interviewTimestamp = strtotime($datetime);
        if (!$interviewTimestamp || $interviewTimestamp <= time()) {
            echo json_encode(['success' => false, 'message' => 'موعد المقابلة يجب أن يكون في تاريخ وساعة مستقبلية ولا يمكن اختيار موعد قد مضى']);
            return;
        }

        $appModel = new Application();
        $application = $appModel->findById($appId);

        if (!$application || $application['EmployerID'] != Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'غير مصرح لك بجدولة هذه المقابلة']);
            return;
        }

        $interviewDateTime = date('Y-m-d H:i:s', $interviewTimestamp);
        if ($locationType === 'online' && empty($location)) {
            $location = 'أونلاين عبر الإنترنت';
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO Interviews (ApplicationID, JobID, EmployerID, CandidateID, InterviewDate, Location, LocationType, MeetingLink, Notes, Status, CreatedAt, UpdatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', GETDATE(), GETDATE())
        ");
        $stmt->execute([
            $appId,
            $application['JobID'],
            Auth::id(),
            $application['UserID'],
            $interviewDateTime,
            $location ?: 'مكتب الشركة',
            $locationType,
            $meetingLink ?: null,
            $notes ?: null
        ]);

        // Update application status to interview
        $appModel->updateStatus($appId, 'interview');

        // Send email invitation to candidate
        if (!empty($application['Email'])) {
            try {
                $employerUser = $this->userModel->getProfile(Auth::id());
                $companyName = $employerUser['CompanyName'] ?? $employerUser['FullName'] ?? 'الشركة';
                $locationOrLink = ($locationType === 'online') ? ($meetingLink ?: $location) : $location;
                Mailer::sendInterviewInvitation(
                    $application['Email'],
                    $application['FullName'],
                    $application['JobTitle'],
                    $companyName,
                    $interviewDateTime,
                    $locationType,
                    $locationOrLink,
                    $notes
                );
            } catch (\Exception $e) {
                error_log('Failed to send interview email: ' . $e->getMessage());
            }
        }

        // Log activity
        $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'schedule_interview', 'Interview', ?, ?, GETDATE())");
        $logStmt->execute([Auth::id(), $appId, 'تم تحديد موعد مقابلة للمتقدم ' . ($application['FullName'] ?? '') . ' على وظيفة ' . ($application['JobTitle'] ?? '')]);

        echo json_encode(['success' => true, 'message' => 'تم تحديد المقابلة بنجاح وإرسال التفاصيل للمتقدم! 🎉']);
    }

    /**
     * Bulk schedule interviews POST
     */
    public function bulkScheduleInterview()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $appIdsInput = $_POST['application_ids'] ?? [];
        $appIds = [];
        if (is_array($appIdsInput)) {
            $appIds = $appIdsInput;
        } elseif (is_string($appIdsInput)) {
            $decoded = json_decode($appIdsInput, true);
            if (is_array($decoded)) {
                $appIds = $decoded;
            } else {
                $appIds = array_filter(array_map('trim', explode(',', $appIdsInput)));
            }
        }

        if (empty($appIds)) {
            echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي متقدمين']);
            return;
        }

        $datetime = trim($_POST['interview_datetime'] ?? '');
        if (empty($datetime)) {
            echo json_encode(['success' => false, 'message' => 'برجاء تحديد موعد وتاريخ المقابلة']);
            return;
        }

        $interviewTimestamp = strtotime($datetime);
        if (!$interviewTimestamp || $interviewTimestamp <= time()) {
            echo json_encode(['success' => false, 'message' => 'موعد المقابلة يجب أن يكون في تاريخ وساعة مستقبلية']);
            return;
        }

        $locationType = $_POST['location_type'] ?? 'online';
        $location = trim($_POST['location'] ?? '');
        $meetingLink = trim($_POST['meeting_link'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($locationType === 'online') {
            if (empty($meetingLink) && empty($location)) {
                echo json_encode(['success' => false, 'message' => 'برجاء إدخال رابط المقابلة الأونلاين']);
                return;
            }
            if (empty($location)) {
                $location = 'أونلاين عبر الإنترنت';
            }
        } else {
            if (empty($location)) {
                echo json_encode(['success' => false, 'message' => 'برجاء إدخال عنوان ومقر المقابلة بالتفصيل']);
                return;
            }
        }

        $interviewDateTime = date('Y-m-d H:i:s', $interviewTimestamp);
        $appModel = new Application();
        $db = Database::getInstance()->getConnection();
        $employerUser = $this->userModel->getProfile(Auth::id());
        $companyName = $employerUser['CompanyName'] ?? $employerUser['FullName'] ?? 'الشركة';

        $processedCount = 0;
        $processedAppIds = [];

        foreach ($appIds as $appId) {
            $appId = (int)$appId;
            if (!$appId) continue;

            $application = $appModel->findById($appId);
            if (!$application || $application['EmployerID'] != Auth::id()) {
                continue;
            }

            // Insert into Interviews
            $stmt = $db->prepare("
                INSERT INTO Interviews (ApplicationID, JobID, EmployerID, CandidateID, InterviewDate, Location, LocationType, MeetingLink, Notes, Status, CreatedAt, UpdatedAt)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', GETDATE(), GETDATE())
            ");
            $stmt->execute([
                $appId,
                $application['JobID'],
                Auth::id(),
                $application['UserID'],
                $interviewDateTime,
                $location,
                $locationType,
                $meetingLink ?: null,
                $notes ?: null
            ]);

            // Update application status to interview
            $appModel->updateStatus($appId, 'interview');

            // Send notification email to candidate
            if (!empty($application['Email'])) {
                try {
                    $locationOrLink = ($locationType === 'online') ? ($meetingLink ?: $location) : $location;
                    Mailer::sendInterviewInvitation(
                        $application['Email'],
                        $application['FullName'],
                        $application['JobTitle'],
                        $companyName,
                        $interviewDateTime,
                        $locationType,
                        $locationOrLink,
                        $notes
                    );
                } catch (\Exception $e) {
                    error_log('Failed to send bulk interview email: ' . $e->getMessage());
                }
            }

            $processedCount++;
            $processedAppIds[] = $appId;
        }

        if ($processedCount === 0) {
            echo json_encode(['success' => false, 'message' => 'تعذر تحديد موعد لأي من المتقدمين المحددين']);
            return;
        }

        // Log activity
        try {
            $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'bulk_interview', 'Interview', ?, ?, GETDATE())");
            $logStmt->execute([Auth::id(), $processedAppIds[0] ?? null, "تم تحديد وإرسال تفاصيل المقابلة والقبول لـ {$processedCount} متقدم دفعة واحدة"]);
        } catch (\Exception $e) {}

        echo json_encode([
            'success' => true,
            'count' => $processedCount,
            'application_ids' => $processedAppIds,
            'message' => "تم إرسال تفاصيل القبول وتحديد المقابلة لـ {$processedCount} متقدم بنجاح! 🎉"
        ]);
    }

    /**
     * Toggle job active status (pause / resume / reopen with new deadline)
     */
    public function toggleJob()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $jobId = $_POST['job_id'] ?? null;
        $newExpiresAtInput = $_POST['new_expires_at'] ?? null;

        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'رقم الوظيفة غير محدد']);
            return;
        }

        $job = $this->jobModel->findById($jobId);
        if (!$job || $job['EmployerID'] != Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'غير مصرح لك بتعديل هذه الوظيفة']);
            return;
        }

        if (!empty($job['IsRemovedByAdmin'])) {
            $reason = !empty($job['AdminRemoveReason']) ? ' (السبب: ' . $job['AdminRemoveReason'] . ')' : '';
            echo json_encode([
                'success' => false,
                'message' => 'تم تعطيل هذه الوظيفة بقرار من إدارة المنصة' . $reason . '. لا يمكنك تعديل حالتها قبل التواصل مع الإدارة.'
            ]);
            return;
        }

        $isCurrentlyActive = !empty($job['IsActive']);
        $hasExpired = !empty($job['ExpiresAt']) && strtotime($job['ExpiresAt']) <= time();

        // Case 1: Currently active AND NOT expired, and no new deadline provided -> Employer wants to PAUSE and zero out timer
        if ($isCurrentlyActive && !$hasExpired && empty($newExpiresAtInput)) {
            $this->jobModel->stopApplications($jobId);
            $updatedJob = $this->jobModel->findById($jobId);

            // Log activity
            try {
                $db = Database::getInstance()->getConnection();
                $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'toggle_job', 'Job', ?, ?, GETDATE())");
                $logStmt->execute([Auth::id(), $jobId, "إيقاف استقبال الطلبات وتصفير العداد لوظيفة: " . ($job['Title'] ?? '')]);
            } catch (\Exception $e) {}

            echo json_encode([
                'success' => true,
                'is_active' => false,
                'is_expired' => true,
                'expires_at' => $updatedJob['ExpiresAt'] ?? date('Y-m-d H:i:s'),
                'expires_iso' => date('c'),
                'expires_formatted' => date('d/m/Y h:i A'),
                'message' => 'تم إيقاف التقديم وتصفير العداد بنجاح ⏸️'
            ]);
            return;
        }

        // Case 2: Employer provided a new deadline -> REOPEN with this new deadline
        if (!empty($newExpiresAtInput)) {
            $ts = strtotime($newExpiresAtInput);
            if (!$ts || $ts <= time()) {
                echo json_encode(['success' => false, 'message' => 'الموعد الجديد لازم يكون في وقت مستقبلي وغير منتهي']);
                return;
            }
            $newExpiresAt = date('Y-m-d H:i:s', $ts);

            $this->jobModel->reopen($jobId, $newExpiresAt);
            $updatedJob = $this->jobModel->findById($jobId);
            $expiresIso = $updatedJob['ExpiresAt'] ? date('c', strtotime($updatedJob['ExpiresAt'])) : null;
            $expiresFormatted = $updatedJob['ExpiresAt'] ? date('d/m/Y h:i A', strtotime($updatedJob['ExpiresAt'])) : null;

            // Log activity
            try {
                $db = Database::getInstance()->getConnection();
                $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'reopen_job', 'Job', ?, ?, GETDATE())");
                $logStmt->execute([Auth::id(), $jobId, "إعادة فتح التقديم على وظيفة: " . ($job['Title'] ?? '')]);
            } catch (\Exception $e) {}

            echo json_encode([
                'success' => true,
                'is_active' => true,
                'is_expired' => false,
                'expires_at' => $updatedJob['ExpiresAt'],
                'expires_iso' => $expiresIso,
                'expires_formatted' => $expiresFormatted,
                'message' => 'تم إعادة فتح واستئناف التقديم بنجاح! 🎉'
            ]);
            return;
        }

        // Case 3: Job is EXPIRED, and no new deadline was provided -> Require new deadline!
        if ($hasExpired) {
            echo json_encode([
                'success' => false,
                'require_new_deadline' => true,
                'job_id' => (int)$jobId,
                'job_title' => $job['Title'],
                'old_expires_at' => $job['ExpiresAt'] ? date('d/m/Y h:i A', strtotime($job['ExpiresAt'])) : '',
                'message' => 'الوظيفة انتهى موعد تقديمها السابق، اختر موعداً جديداً لإعادة فتح التقديم'
            ]);
            return;
        }

        // Case 4: Original deadline is still valid and job was paused -> Reopen with original time!
        $this->jobModel->reopen($jobId, $job['ExpiresAt']);
        $updatedJob = $this->jobModel->findById($jobId);
        $expiresIso = $updatedJob['ExpiresAt'] ? date('c', strtotime($updatedJob['ExpiresAt'])) : null;
        $expiresFormatted = $updatedJob['ExpiresAt'] ? date('d/m/Y h:i A', strtotime($updatedJob['ExpiresAt'])) : null;

        // Log activity
        try {
            $db = Database::getInstance()->getConnection();
            $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'reopen_job', 'Job', ?, ?, GETDATE())");
            $logStmt->execute([Auth::id(), $jobId, "استئناف التقديم بالموعد الأصلي على وظيفة: " . ($job['Title'] ?? '')]);
        } catch (\Exception $e) {}

        echo json_encode([
            'success' => true,
            'is_active' => true,
            'is_expired' => false,
            'expires_at' => $updatedJob['ExpiresAt'],
            'expires_iso' => $expiresIso,
            'expires_formatted' => $expiresFormatted,
            'message' => 'تم استئناف التقديم بالموعد الأصلي بنجاح ✅'
        ]);
    }
}
