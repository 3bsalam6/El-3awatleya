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
        if (!$job || !$job['IsActive']) {
            Session::flash('error', 'الوظيفة مش موجودة');
            header('Location: /jobs');
            exit;
        }

        $appModel = new Application();
        $alreadyApplied = $appModel->hasApplied(Auth::id(), $jobId);
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

        $jobId = $_POST['job_id'] ?? null;
        $coverLetter = trim($_POST['cover_letter'] ?? '');

        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'الوظيفة مش محددة']);
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
                echo json_encode(['success' => false, 'message' => 'فشل رفع الـ CV']);
                return;
            }
        }

        $applicationId = $appModel->apply(Auth::id(), $jobId, $cvId, $coverLetter);

        if ($applicationId) {
            echo json_encode(['success' => true, 'message' => 'تم التقديم بنجاح! 🎉']);
        } else {
            echo json_encode(['success' => false, 'message' => 'حصل مشكلة، حاول تاني']);
        }
    }

    /**
     * Upload CV file
     */
    private function uploadCV($file)
    {
        if (!in_array($file['type'], ALLOWED_CV_TYPES)) {
            return null;
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/cvs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'cv_' . Auth::id() . '_' . time() . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO CVs (UserID, FileName, FilePath, FileType) VALUES (?, ?, ?, ?)");
            $stmt->execute([Auth::id(), $fileName, '/public/uploads/cvs/' . $fileName, $file['type']]);
            return $db->lastInsertId();
        }

        return null;
    }
}
