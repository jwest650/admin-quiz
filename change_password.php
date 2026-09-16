<?php
/*
 * Two endpoints in one, both called by password.php over AJAX:
 *   POST old_password                -> "True" / "False" (live check on blur)
 *   POST old_password + new_password -> change the password
 */

session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo "Not authorised";
    exit();
}

include 'library/crud.php';
include 'library/auth.php';

$db = new Database();
if (!$db->connect()) {
    error_log('change_password: unable to connect to the database');
    http_response_code(500);
    echo "Something wrong";
    exit();
}

$conn          = $db->getConnection();
$auth_username = (string) $_SESSION['username'];

function current_password_matches($conn, $auth_username, $password)
{
    $stmt = $conn->prepare("SELECT auth_pass FROM `authenticate` WHERE BINARY auth_username = ? LIMIT 1");
    if (!$stmt) {
        error_log('change_password: prepare failed - ' . $conn->error);
        return false;
    }
    $stmt->bind_param('s', $auth_username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ? verify_admin_password($conn, $row['auth_pass'], $password) : false;
}

$old_password = isset($_POST['old_password']) ? (string) $_POST['old_password'] : null;
$new_password = isset($_POST['new_password']) ? (string) $_POST['new_password'] : null;

// --- live check of the current password ---
if ($old_password !== null && $new_password === null) {
    echo current_password_matches($conn, $auth_username, $old_password) ? "True" : "False";
    exit();
}

// --- change the password ---
if ($old_password !== null && $new_password !== null) {
    // Re-verify server side. The old code updated on the client's say-so, so
    // anyone posting here could set a new password without knowing the old one.
    if (!current_password_matches($conn, $auth_username, $old_password)) {
        echo "Current password is incorrect";
        exit();
    }

    if (strlen($new_password) < 6) {
        echo "New password must be at least 6 characters";
        exit();
    }

    echo store_admin_password($conn, $auth_username, $new_password)
        ? "password change successfully!"
        : "Something wrong";
    exit();
}

http_response_code(400);
echo "Something wrong";
