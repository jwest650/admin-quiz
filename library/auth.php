<?php
/*
 * Shared password handling for the admin panel.
 *
 * History: auth_pass rows hold unsalted MD5 hashes, and the two callers hashed
 * differently mangled versions of what the user typed - check_login.php ran
 * htmlspecialchars(stripslashes($p)) and change_password.php ran
 * real_escape_string($p). Both mangled anything containing & < > " ' or \.
 *
 * verify_admin_password() accepts the clean password first, then those historic
 * forms, so no existing account is locked out. Callers should rehash with
 * admin_password_hash() on a successful login so the mangled value is retired.
 */

if (!function_exists('is_legacy_md5_hash')) {

    function is_legacy_md5_hash($hash)
    {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }

    /*
     * A bcrypt hash needs 60 chars; the shipped schema declares auth_pass as
     * varchar(50). Only emit a modern hash once the column has been widened,
     * otherwise MySQL would truncate it and lock the account out.
     */
    function auth_pass_column_fits($conn)
    {
        $res = $conn->query("SHOW COLUMNS FROM `authenticate` LIKE 'auth_pass'");
        if (!$res) {
            return false;
        }
        $col = $res->fetch_assoc();
        $res->free();
        if (!$col || !isset($col['Type'])) {
            return false;
        }
        if (preg_match('/^varchar\((\d+)\)/i', $col['Type'], $m)) {
            return (int) $m[1] >= 255;
        }
        return (bool) preg_match('/text|blob/i', $col['Type']);
    }

    function admin_password_hash($conn, $password)
    {
        return auth_pass_column_fits($conn)
            ? password_hash($password, PASSWORD_DEFAULT)
            : md5($password);
    }

    /* The mangled forms the old code could have stored. */
    function legacy_password_variants($conn, $password)
    {
        return array(
            htmlspecialchars(stripslashes($password), ENT_QUOTES),
            htmlspecialchars(stripslashes($password), ENT_COMPAT),
            $conn->real_escape_string($password),
            $conn->real_escape_string(htmlspecialchars(stripslashes($password), ENT_QUOTES)),
        );
    }

    /*
     * Returns true when $password matches $stored. Sets $needs_rehash when the
     * match came from a legacy hash and the row should be rewritten.
     */
    function verify_admin_password($conn, $stored, $password, &$needs_rehash = false)
    {
        $stored = (string) $stored;
        $needs_rehash = false;

        if (!is_legacy_md5_hash($stored)) {
            if (password_verify($password, $stored)) {
                $needs_rehash = password_needs_rehash($stored, PASSWORD_DEFAULT);
                return true;
            }
            return false;
        }

        if (hash_equals($stored, md5($password))) {
            $needs_rehash = true; // MD5 -> modern hash
            return true;
        }

        foreach (legacy_password_variants($conn, $password) as $variant) {
            if ($variant !== $password && hash_equals($stored, md5($variant))) {
                $needs_rehash = true; // also retires the mangled value
                return true;
            }
        }

        return false;
    }

    function store_admin_password($conn, $auth_username, $password)
    {
        $hash = admin_password_hash($conn, $password);
        $stmt = $conn->prepare("UPDATE `authenticate` SET auth_pass = ? WHERE BINARY auth_username = ?");
        if (!$stmt) {
            error_log('store_admin_password: prepare failed - ' . $conn->error);
            return false;
        }
        $stmt->bind_param('ss', $hash, $auth_username);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
