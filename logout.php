<?php
session_start();

// Clear the data, then the cookie, then the session itself. session_destroy()
// on its own leaves the session cookie in the browser.
$_SESSION = array();

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();
header('location:index.php');
exit();
