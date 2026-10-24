<?php
/**
 * العواطلية - Route Definitions
 */

$router->get('/', function () {
    header('Location: /login');
    exit;
});

// ===========================
// Auth Routes
// ===========================
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/otp', [AuthController::class, 'showOTP']);
$router->post('/otp', [AuthController::class, 'verifyOTP']);
$router->post('/otp/resend', [AuthController::class, 'resendOTP']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/logout', [AuthController::class, 'logout']);

// ===========================
// Normal User Routes
// ===========================
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/jobs', [JobController::class, 'index']);
$router->get('/jobs/apply', [JobController::class, 'showApply']);
$router->post('/jobs/apply', [JobController::class, 'apply']);
$router->get('/my-applications', [UserController::class, 'applications']);
$router->get('/enhancements', [UserController::class, 'enhancements']);
$router->post('/enhancements', [UserController::class, 'requestEnhancement']);
$router->get('/settings', [UserController::class, 'settings']);
$router->post('/settings/profile', [UserController::class, 'updateProfile']);
$router->post('/settings/email', [UserController::class, 'changeEmail']);
$router->post('/settings/password', [UserController::class, 'changePassword']);
$router->post('/settings/otp', [UserController::class, 'toggleOTP']);
$router->get('/evaluation', [EvaluationController::class, 'showResult']);
$router->post('/evaluation', [EvaluationController::class, 'evaluate']);

// ===========================
// Employer Routes
// ===========================
$router->get('/employer/dashboard', [EmployerController::class, 'dashboard']);
$router->get('/employer/post-job', [EmployerController::class, 'showPostJob']);
$router->post('/employer/post-job', [EmployerController::class, 'postJob']);
$router->get('/employer/applicants', [EmployerController::class, 'applicants']);
$router->post('/employer/applicants/status', [EmployerController::class, 'updateApplicationStatus']);
$router->get('/employer/interview', [EmployerController::class, 'showInterview']);
$router->post('/employer/interview', [EmployerController::class, 'scheduleInterview']);
$router->get('/employer/settings', [EmployerController::class, 'settings']);
$router->post('/employer/settings/profile', [EmployerController::class, 'updateProfile']);
$router->post('/employer/settings/email', [UserController::class, 'changeEmail']);
$router->post('/employer/settings/password', [UserController::class, 'changePassword']);
$router->post('/employer/settings/otp', [UserController::class, 'toggleOTP']);

// ===========================
// Admin Routes
// ===========================
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users/ban', [AdminController::class, 'banUser']);
$router->post('/admin/users/unban', [AdminController::class, 'unbanUser']);
$router->get('/admin/jobs', [AdminController::class, 'jobs']);
$router->post('/admin/jobs/toggle', [AdminController::class, 'toggleJob']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings/profile', [AdminController::class, 'updateProfile']);
$router->post('/admin/settings/email', [UserController::class, 'changeEmail']);
$router->post('/admin/settings/password', [UserController::class, 'changePassword']);
