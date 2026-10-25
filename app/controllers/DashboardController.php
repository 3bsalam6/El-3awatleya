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
            'accepted' => count(array_filter($applications, fn($a) => in_array($a['Status'], ['accepted', 'interview']))),
            'rejected' => count(array_filter($applications, fn($a) => $a['Status'] === 'rejected')),
        ];

        // Upcoming interviews for the candidate
        $upcomingInterviews = array_values(array_filter($applications, function($a) {
            return !empty($a['InterviewDate']) && strtotime($a['InterviewDate']) > time() && in_array($a['Status'], ['interview', 'accepted']);
        }));

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
