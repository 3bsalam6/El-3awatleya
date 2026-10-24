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
        $users = $this->userModel->getAll($role, $search);
        $user = $this->userModel->findById(Auth::id());

        require __DIR__ . '/../views/admin/users.php';
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

        $this->userModel->ban($userId, $reason);
        echo json_encode(['success' => true, 'message' => 'تم حظر المستخدم ✅']);
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

        $this->userModel->unban($userId);
        echo json_encode(['success' => true, 'message' => 'تم رفع الحظر ✅']);
    }

    /**
     * Manage jobs
     */
    public function jobs()
    {
        Auth::requireRole('admin');

        $search = $_GET['search'] ?? null;
        $jobs = $this->jobModel->getAll($search);
        $user = $this->userModel->findById(Auth::id());

        require __DIR__ . '/../views/admin/jobs.php';
    }

    /**
     * Toggle job status POST
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

        $this->jobModel->toggleActive($jobId);
        echo json_encode(['success' => true, 'message' => 'تم تحديث حالة الوظيفة ✅']);
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
}
