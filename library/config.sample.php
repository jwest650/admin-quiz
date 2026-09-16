<?php
/*
 * Copy this file to library/config.php and fill in the real values.
 *
 * library/config.php is git ignored on purpose - it holds live credentials and
 * must never be committed. Create it on each server as part of deployment, or
 * set the equivalent environment variables (which take precedence):
 *
 *   QUIZ_DB_HOST  QUIZ_DB_USER  QUIZ_DB_PASS  QUIZ_DB_NAME
 *   QUIZ_DOMAIN_URL  QUIZ_JWT_SECRET
 */

return array(
    // Used when the request is served from localhost / 127.0.0.1, or from CLI.
    'local' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'admin_quiz',
    ),

    // Used everywhere else.
    'production' => array(
        'host' => 'localhost',
        'user' => '',
        'pass' => '',
        'name' => '',
    ),

    'domain_url' => 'https://example.com/',
    'jwt_secret' => '', // long random string, e.g. from `openssl rand -hex 32`

    // Email for teacher invitations and meeting invites (uquiz/includes/mailer.php).
    // The environment variables UQUIZ_SMTP_HOST, UQUIZ_SMTP_PORT, UQUIZ_SMTP_USER,
    // UQUIZ_SMTP_PASS, UQUIZ_SMTP_TLS, UQUIZ_SMTP_FROM and UQUIZ_MAIL_FALLBACK take
    // precedence. Without an SMTP host, teacher invitations are shown as links only.
    'mail' => array(
        'smtp_host' => '',        // e.g. smtp.gmail.com, smtp.sendgrid.net
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_tls' => true,       // STARTTLS
        'from' => '',             // sender address; default no-reply@<your domain>
        'php_mail_fallback' => false, // true = use PHP mail() when no SMTP host is set
    ),
);
