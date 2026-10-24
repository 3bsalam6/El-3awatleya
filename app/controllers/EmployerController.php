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
            'active_jobs' => count(array_filter($jobs, fn($j) => $j['IsActive'])),
            'total_applications' => count($applications),
            'pending' => count(array_filter($applications, fn($a) => $a['Status'] === 'pending')),
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
            'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
        ];

        if (empty($data['title']) || empty($data['description'])) {
            echo json_encode(['success' => false, 'message' => 'لازم تملا الحقول المطلوبة (المسمى الوظيفي والوصف)']);
            return;
        }

        $jobId = $this->jobModel->create($data);
        echo json_encode(['success' => true, 'message' => 'تم نشر الوظيفة بنجاح! 🎉', 'job_id' => $jobId]);
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
            'name' => trim($_POST['company_name'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? ''),
            'industry' => $_POST['industry'] ?? null,
            'company_size' => $_POST['company_size'] ?? null,
            'city' => $_POST['city'] ?? null,
            'website' => $_POST['website'] ?? null,
            'address' => $_POST['address'] ?? null,
            'description' => $_POST['description'] ?? null,
        ];

        $this->userModel->updateProfile(Auth::id(), $data);
        Session::set('user_name', $data['name']);

        echo json_encode(['success' => true, 'message' => 'تم حفظ التغييرات ✅']);
    }

    /**
     * Schedule interview POST
     */
    public function scheduleInterview()
    {
        Auth::requireRole('employer');
        header('Content-Type: application/json');

        $appId = $_POST['application_id'] ?? null;
        $date = $_POST['interview_date'] ?? '';
        $time = $_POST['interview_time'] ?? '';
        $locationType = $_POST['location_type'] ?? 'onsite';
        $location = trim($_POST['location'] ?? '');
        $meetingLink = trim($_POST['meeting_link'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$appId || empty($date) || empty($time)) {
            echo json_encode(['success' => false, 'message' => 'برجاء ملء جميع الحقول الإلزامية']);
            return;
        }

        $appModel = new Application();
        $application = $appModel->findById($appId);

        if (!$application || $application['EmployerID'] != Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'غير مصرح لك بجدولة هذه المقابلة']);
            return;
        }

        $interviewDateTime = $date . ' ' . $time;
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

        // Log activity
        $logStmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, CreatedAt) VALUES (?, 'schedule_interview', 'Interview', ?, ?, GETDATE())");
        $logStmt->execute([Auth::id(), $appId, 'تم تحديد موعد مقابلة للمتقدم ' . ($application['FullName'] ?? '') . ' على وظيفة ' . ($application['JobTitle'] ?? '')]);

        echo json_encode(['success' => true, 'message' => 'تم تحديد المقابلة بنجاح! 🎉']);
    }
}
