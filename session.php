<?php
// session.php - include at top of every page that needs session handling
// Usage: include 'session.php'; require_login();  (or require_login(false) for optional)

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookie settings
    $secure = false; // set true if using HTTPS
    $httponly = true;
    $samesite = 'Lax'; // or 'Strict' if you prefer stricter
    
    // PHP 7.3+ style cookie params - 7 days expiration
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 7,  // 7 days (604800 seconds)
        'path' => '/',
        'domain' => '',           // default to host
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
    session_start();

    // Add regenerate_session() function here
    function regenerate_session() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}

// session timeout + fingerprint settings
define('SESSION_TIMEOUT', 60 * 60 * 24 * 7); // 7 days inactivity (matches cookie expiration)

function is_logged_in() {
    return isset($_SESSION['username']) && empty($_SESSION['guest_mode']);
}

function require_login($redirect = true) {
    if (!is_logged_in()) {
        if ($redirect) {
            header("Location: login.php");
            exit();
        }
        return false;
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        if ($redirect) {
            header("Location: login.php?expired=1");
            exit();
        }
        return false;
    }

    $_SESSION['last_activity'] = time();

    $fingerprint = sha1(
        (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') .
        (isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'],0,7) : '')
    );

    if (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint'] !== $fingerprint) {
        session_unset();
        session_destroy();
        if ($redirect) {
            header("Location: login.php?expired=1");
            exit();
        }
        return false;
    }

    return true;
}

function login_user($username) {
    session_regenerate_id(true);
    $_SESSION['username'] = $username;
    unset($_SESSION['guest_mode']);
    $_SESSION['last_activity'] = time();
    $_SESSION['fingerprint'] = sha1(
        (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') .
        (isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'],0,7) : '')
    );
}

function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    unset($_SESSION['guest_mode']);
    session_destroy();
}
?>



