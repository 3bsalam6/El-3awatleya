<?php
/**
 * العواطلية - Auth Helper
 */

class Auth
{
    /**
     * Log in a user — set session data
     */
    public static function login($user)
    {
        Session::set('user_id', $user['UserID']);
        Session::set('user_name', $user['FullName']);
        Session::set('user_email', $user['Email']);
        Session::set('user_role', $user['Role']);
        Session::set('otp_enabled', $user['IsOTPEnabled']);
        Session::set('logged_in', true);
    }

    /**
     * Log out — destroy session + remove remember cookie
     */
    public static function logout()
    {
        Session::destroy();

        if (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
            $token = $_COOKIE[REMEMBER_COOKIE_NAME];

            // Delete from DB
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM RememberTokens WHERE Token = ?");
            $stmt->execute([$token]);

            // Clear cookie
            setcookie(REMEMBER_COOKIE_NAME, '', time() - 3600, '/', '', false, true);
        }
    }

    /**
     * Check if user is logged in
     */
    public static function check()
    {
        return Session::get('logged_in', false) === true;
    }

    /**
     * Get current user ID
     */
    public static function id()
    {
        return Session::get('user_id');
    }

    /**
     * Get current user's role
     */
    public static function role()
    {
        return Session::get('user_role');
    }

    /**
     * Get current user's name
     */
    public static function name()
    {
        return Session::get('user_name');
    }

    /**
     * Get current user's email
     */
    public static function email()
    {
        return Session::get('user_email');
    }

    /**
     * Check if user has a specific role
     */
    public static function isRole($role)
    {
        return self::role() === $role;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        return self::isRole('admin');
    }

    /**
     * Check if user is employer
     */
    public static function isEmployer()
    {
        return self::isRole('employer');
    }

    /**
     * Check if user is normal user
     */
    public static function isUser()
    {
        return self::isRole('user');
    }

    /**
     * Require login — redirect if not logged in
     */
    public static function requireLogin()
    {
        if (!self::check()) {
            // Try remember me cookie
            if (self::tryRememberMe()) {
                return true;
            }

            Session::flash('error', 'لازم تسجل دخول الأول');
            header('Location: /login');
            exit;
        }
        return true;
    }

    /**
     * Require a specific role
     */
    public static function requireRole($role)
    {
        self::requireLogin();

        if (!self::isRole($role)) {
            Session::flash('error', 'مش مسموحلك تدخل هنا');
            // Redirect to appropriate dashboard
            switch (self::role()) {
                case 'admin':
                    header('Location: /admin/dashboard');
                    break;
                case 'employer':
                    header('Location: /employer/dashboard');
                    break;
                default:
                    header('Location: /dashboard');
            }
            exit;
        }
    }

    /**
     * Set remember me cookie
     */
    public static function setRememberToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + (REMEMBER_COOKIE_DAYS * 86400));

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO RememberTokens (UserID, Token, ExpiresAt) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $hashedToken, $expiresAt]);

        setcookie(
            REMEMBER_COOKIE_NAME,
            $token,
            time() + (REMEMBER_COOKIE_DAYS * 86400),
            '/',
            '',
            false,
            true
        );
    }

    /**
     * Try to auto-login from remember me cookie
     */
    public static function tryRememberMe()
    {
        if (!isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
            return false;
        }

        $token = $_COOKIE[REMEMBER_COOKIE_NAME];
        $hashedToken = hash('sha256', $token);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT u.* FROM Users u
            JOIN RememberTokens rt ON u.UserID = rt.UserID
            WHERE rt.Token = ? AND rt.ExpiresAt > GETDATE() AND u.IsBanned = 0
        ");
        $stmt->execute([$hashedToken]);
        $user = $stmt->fetch();

        if ($user) {
            self::login($user);
            return true;
        }

        // Invalid/expired token — clean up
        setcookie(REMEMBER_COOKIE_NAME, '', time() - 3600, '/', '', false, true);
        return false;
    }

    /**
     * Get full user data from DB
     */
    public static function user()
    {
        if (!self::check())
            return null;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Users WHERE UserID = ?");
        $stmt->execute([self::id()]);
        return $stmt->fetch();
    }

    /**
     * Generate OTP code
     */
    public static function generateOTP()
    {
        return str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
    }
}
