<?php
/**
 * العواطلية - Dashboard Controller
 */

class DashboardController
{
    public function index()
    {
        Auth::requireRole('user');

        $jobModel = new Job();
        $appModel = new Application();
        $userModel = new User();

        $user = $userModel->getProfile(Auth::id());
        $recentJobs = $jobModel->getActive();
        $applications = $appModel->getByUser(Auth::id());

        // Stats
        $stats = [
            'total_applications' => count($applications),
            'pending' => count(array_filter($applications, fn($a) => $a['Status'] === 'pending')),
            'accepted' => count(array_filter($applications, fn($a) => $a['Status'] === 'accepted')),
            'rejected' => count(array_filter($applications, fn($a) => $a['Status'] === 'rejected')),
        ];

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
