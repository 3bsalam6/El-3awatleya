<?php
/**
 * العواطلية - Simple Autoloader (replaces Composer autoload)
 */

spl_autoload_register(function ($class) {
    // PHPMailer namespace
    $prefix = 'PHPMailer\\PHPMailer\\';
    $baseDir = __DIR__ . '/phpmailer/phpmailer/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});
