<?php
/**
 * العواطلية - App Configuration
 */

define('APP_NAME', 'العواطلية');
define('APP_URL', 'http://localhost:8000');

// Session
define('SESSION_NAME', 'el3awatleya_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// Remember me cookie
define('REMEMBER_COOKIE_NAME', 'el3awatleya_remember');
define('REMEMBER_COOKIE_DAYS', 30);

// OTP
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 10);
define('OTP_MAX_ATTEMPTS', 3);

// File uploads
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_CV_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// FastAPI AI Service
define('FASTAPI_URL', 'http://localhost:5000');
