<?php
/*
 * Admin login handler.
 *
 * Responds to the AJAX POST from index.php:
 *   "1"              -> success, the browser redirects to home.php
 *   any other output -> an HTML alert shown under the form
 */

// Buffer everything. A stray notice or warning printed before the response
// would otherwise be prepended to the body and break the JSON the client parses.
ob_start();

session_start();

include 'library/crud.php';
include 'library/auth.php';

/*
 * Single exit point. Always emits a clean JSON body:
 *   {"success":true}
 *   {"success":false,"message":"..."}
 */
function login_respond($success, $message = '', $http_status = 200)
{
    if (ob_get_length() !== false) {
        ob_clean();
    }
    http_response_code($http_status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $payload = array('success' => (bool) $success);
    if ($message !== '') {
        $payload['message'] = $message;
    }
    echo json_encode($payload);
    exit();
}

/*
 * Whatever else goes wrong, the client must still receive JSON - otherwise it
 * cannot show a meaningful message and the form's loader has nothing to react
 * to. Never leak the underlying error text to the browser; it goes to the log.
 */
set_exception_handler(function ($e) {
    error_log('check_login: uncaught ' . get_class($e) . ' - ' . $e->getMessage());
    login_respond(false, 'Login is temporarily unavailable. Please try again in a moment.', 500);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error === null || !in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
        return;
    }
    error_log('check_login: fatal - ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
    if (!headers_sent()) {
        login_respond(false, 'Login is temporarily unavailable. Please try again in a moment.', 500);
    }
});

// Diagnostics belong in the log, not in the response body.
ini_set('display_errors', '0');

/* ---------------------------------------------------------------------------
 * Brute force throttle, keyed on (username + client IP) and file backed so it
 * survives an attacker simply dropping their session cookie.
 * ------------------------------------------------------------------------ */
define('LOGIN_MAX_ATTEMPTS', 8);
define('LOGIN_LOCKOUT_SECONDS', 900); // 15 minutes

function login_throttle_file($username)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli';
    return sys_get_temp_dir() . '/quiz_login_' . sha1(strtolower($username) . '|' . $ip) . '.lock';
}

function login_attempts($username)
{
    $file = login_throttle_file($username);
    if (!is_file($file)) {
        return 0;
    }
    if (time() - filemtime($file) > LOGIN_LOCKOUT_SECONDS) {
        @unlink($file);
        return 0;
    }
    return (int) @file_get_contents($file);
}

function login_record_failure($username)
{
    @file_put_contents(login_throttle_file($username), (string) (login_attempts($username) + 1), LOCK_EX);
}

function login_clear_failures($username)
{
    @unlink(login_throttle_file($username));
}

/* ------------------------------------------------------------------------ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    login_respond(false, 'Invalid request.', 405);
}

// Do not mangle the credentials here: the old htmlspecialchars()/stripslashes()
// pass corrupted any password containing & < > " ' or a backslash.
$username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
$password = isset($_POST['password']) ? (string) $_POST['password'] : '';

if ($username === '' || $password === '') {
    login_respond(false, 'Please enter both your username and password.');
}

if (login_attempts($username) >= LOGIN_MAX_ATTEMPTS) {
    login_respond(
        false,
        'Too many failed attempts. Please try again in ' . (LOGIN_LOCKOUT_SECONDS / 60) . ' minutes.',
        429
    );
}

$db = new Database();
if (!$db->connect()) {
    error_log('check_login: unable to connect to the database');
    login_respond(false, 'Login is temporarily unavailable. Please try again in a moment.', 503);
}

$conn = $db->getConnection();

// BINARY keeps the username comparison case sensitive, which is what the old
// strcmp() check was there for.
$stmt = $conn->prepare(
    "SELECT auth_username, auth_pass, role, status FROM `authenticate` WHERE BINARY auth_username = ? LIMIT 1"
);
if (!$stmt) {
    error_log('check_login: prepare failed - ' . $conn->error);
    login_respond(false, 'Login is temporarily unavailable. Please try again in a moment.', 503);
}

$stmt->bind_param('s', $username);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$authenticated = false;
$needs_rehash  = false;

if ($row) {
    $authenticated = verify_admin_password($conn, $row['auth_pass'], $password, $needs_rehash);
}

if (!$authenticated) {
    login_record_failure($username);
    // One generic message: saying which field was wrong tells an attacker
    // which usernames exist.
    login_respond(false, 'Username or password is incorrect.', 401);
}

// Disabled accounts must not get a session, but only say so after the password
// checked out - otherwise this becomes a username oracle.
if ((int) $row['status'] !== 1) {
    error_log('check_login: correct password for disabled account ' . $row['auth_username']);
    login_respond(false, 'This account is disabled. Please contact an administrator.', 403);
}

if ($needs_rehash) {
    store_admin_password($conn, $row['auth_username'], $password);
}

login_clear_failures($username);

// Fresh session id on privilege change, so a fixated session id is useless.
session_regenerate_id(true);

$_SESSION['id']           = $row['auth_username'];
$_SESSION['username']     = $row['auth_username'];
$_SESSION['role']         = isset($row['role']) ? $row['role'] : '';
$_SESSION['company_name'] = 'UQuiz';
$_SESSION['login_time']   = time();

login_respond(true);
