<?php
/*
 * Reads library/.env into the process environment, so every getenv() call in the
 * application picks the values up: database credentials (library/crud.php), email
 * settings (uquiz/includes/mailer.php) and anything else added later.
 *
 * A real environment variable always wins over this file, so a host that sets them
 * properly (Plesk PHP-FPM "env[...]" directives, Docker, CI) needs no .env at all.
 * library/config.php remains the last fallback.
 *
 * See library/.env.sample for the format and the keys. This file is tracked in git;
 * .env itself never is.
 */

function quiz_load_env()
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $file = __DIR__ . '/.env';
    if (!is_file($file) || !is_readable($file)) {
        return;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';') {
            continue;
        }
        // "export KEY=value" is accepted, so a shell env file can be reused as is.
        if (stripos($line, 'export ') === 0) {
            $line = trim(substr($line, 7));
        }
        $split = strpos($line, '=');
        if ($split === false) {
            continue;
        }
        $key = trim(substr($line, 0, $split));
        $value = trim(substr($line, $split + 1));
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            continue;
        }
        // Quotes let a value keep spaces or a # that would otherwise read as a comment.
        $length = strlen($value);
        if ($length >= 2
            && (($value[0] === '"' && $value[$length - 1] === '"')
                || ($value[0] === "'" && $value[$length - 1] === "'"))) {
            $value = substr($value, 1, -1);
        }

        $existing = getenv($key);
        if ($existing !== false && $existing !== '') {
            continue;
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

quiz_load_env();
