<?php
/**
 * العواطلية - Job Controller
 */

class JobController
{
    private $jobModel;

    public function __construct()
    {
        $this->jobModel = new Job();
    }

    /**
     * List jobs
     */
    public function index()
    {
        Auth::requireRole('user');

        $search = $_GET['search'] ?? null;
        $category = $_GET['category'] ?? null;
        $jobType = $_GET['type'] ?? null;

        $jobs = $this->jobModel->getActive($search, $category, $jobType);
        $user = (new User())->getProfile(Auth::id());

        require __DIR__ . '/../views/jobs/index.php';
    }

    /**
     * Show apply form
     */
    public function showApply()
    {
        Auth::requireRole('user');

        $jobId = $_GET['id'] ?? null;
        if (!$jobId) {
            header('Location: /jobs');
            exit;
        }

        $job = $this->jobModel->findById($jobId);
        if (!$job) {
            Session::flash('error', 'الوظيفة مش موجودة');
            header('Location: /jobs');
            exit;
        }

        $isRemovedAdmin = !empty($job['IsRemovedByAdmin']);
        $isJobClosed = empty($job['IsActive']) || $isRemovedAdmin;
        $appModel = new Application();
        $alreadyApplied = $appModel->hasApplied(Auth::id(), $jobId);
        $isExpired = !empty($job['ExpiresAt']) && strtotime($job['ExpiresAt']) <= time();
        $user = (new User())->getProfile(Auth::id());

        require __DIR__ . '/../views/jobs/apply.php';
    }

    /**
     * Handle apply POST
     */
    public function apply()
    {
        Auth::requireRole('user');
        header('Content-Type: application/json');

        try {
            $jobId = $_POST['job_id'] ?? null;
            $coverLetter = trim($_POST['cover_letter'] ?? '');

            if (!$jobId) {
                echo json_encode(['success' => false, 'message' => 'الوظيفة مش محددة']);
                return;
            }

            if (empty($coverLetter)) {
                echo json_encode(['success' => false, 'message' => 'لازم توضح ليه الوظيفة دي مناسبة ليك']);
                return;
            }

            $job = $this->jobModel->findById($jobId);
            if (!$job || empty($job['IsActive']) || !empty($job['IsRemovedByAdmin'])) {
                echo json_encode(['success' => false, 'message' => 'هذه الوظيفة غير متاحة حالياً للتقديم']);
                return;
            }

            if (!empty($job['ExpiresAt']) && strtotime($job['ExpiresAt']) <= time()) {
                echo json_encode(['success' => false, 'message' => 'عذراً، لقد انتهى موعد التقديم على هذه الوظيفة!']);
                return;
            }

            $appModel = new Application();

            if ($appModel->hasApplied(Auth::id(), $jobId)) {
                echo json_encode(['success' => false, 'message' => 'إنت قدمت على الوظيفة دي قبل كده']);
                return;
            }

            // Handle CV upload
            $cvId = null;
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
                $cvId = $this->uploadCV($_FILES['cv']);
                if (!$cvId) {
                    echo json_encode(['success' => false, 'message' => 'فشل رفع الـ CV، تأكد أن الملف بصيغة PDF أو Word وحجمه أقل من 5MB']);
                    return;
                }
            } else {
                // Check if user has an existing active CV
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT TOP 1 CVID FROM CVs WHERE UserID = ? AND IsActive = 1 ORDER BY UploadedAt DESC");
                $stmt->execute([Auth::id()]);
                $cvId = $stmt->fetchColumn();
            }

            if (!$cvId) {
                echo json_encode(['success' => false, 'message' => 'لازم ترفع الـ CV بتاعك (PDF أو Word) للتقديم']);
                return;
            }

            $applicationId = $appModel->apply(Auth::id(), $jobId, $cvId, $coverLetter);

            if ($applicationId) {
                // Log activity
                try {
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("INSERT INTO ActivityLogs (UserID, Action, Details) VALUES (?, 'apply', ?)");
                    $stmt->execute([Auth::id(), "تقديم على وظيفة: " . ($job['Title'] ?? 'وظيفة')]);
                } catch (\Exception $e) {}

                echo json_encode(['success' => true, 'message' => 'تم التقديم بنجاح! 🎉']);
            } else {
                echo json_encode(['success' => false, 'message' => 'حصلت مشكلة أثناء التقديم، حاول تاني']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    /**
     * Upload CV file
     */
    private function uploadCV($file)
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            return null;
        }

        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > MAX_FILE_SIZE) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/cvs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'cv_' . Auth::id() . '_' . time() . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        $moved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $filePath)
            : copy($file['tmp_name'], $filePath);

        if ($moved) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO CVs (UserID, FileName, FilePath, FileSize, FileType, IsActive)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([Auth::id(), $fileName, '/public/uploads/cvs/' . $fileName, $size, $ext]);
            return $db->lastInsertId();
        }

        return null;
    }
}
