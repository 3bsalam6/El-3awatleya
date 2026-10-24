<?php
/**
 * العواطلية - Session Helper
 */

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Redirect — flush session to disk first so data persists
     */
    public static function redirect($url)
    {
        session_write_close();
        header('Location: ' . $url);
        exit;
    }

    /**
     * Flash messages — set once, read once
     */
    public static function flash($key, $message = null)
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return;
        }

        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function hasFlash($key)
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * OTP session handling
     */
    public static function setOTP($code, $userId, $purpose = 'login')
    {
        $_SESSION['otp'] = [
            'code' => $code,
            'user_id' => $userId,
            'purpose' => $purpose,
            'attempts' => 0,
            'expires_at' => time() + (OTP_EXPIRY_MINUTES * 60),
        ];
    }

    public static function verifyOTP($inputCode)
    {
        $otp = $_SESSION['otp'] ?? null;
        if (!$otp) return false;

        if (time() > $otp['expires_at']) {
            unset($_SESSION['otp']);
            return false;
        }

        if ($otp['attempts'] >= OTP_MAX_ATTEMPTS) {
            unset($_SESSION['otp']);
            return false;
        }

        if ($otp['code'] === $inputCode) {
            $userId = $otp['user_id'];
            $purpose = $otp['purpose'];
            unset($_SESSION['otp']);
            return ['user_id' => $userId, 'purpose' => $purpose];
        }

        $_SESSION['otp']['attempts']++;
        return false;
    }

    public static function getOTP()
    {
        return $_SESSION['otp'] ?? null;
    }
}
