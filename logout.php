<?php
session_start();

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session juga (kalo ada)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Ancurin session
session_destroy();

// Hapus token Google (kalo pake)
if (isset($_COOKIE['google_token'])) {
    unset($_COOKIE['google_token']);
    setcookie('google_token', '', time() - 3600, '/');
}

// Redirect ke halaman login
header("Location: index.php");
exit();
?>
