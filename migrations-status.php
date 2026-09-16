<?php
/*
 * TEMPORARY check: which database migrations has this server had?
 *
 * Upload to the admin-quiz folder, open it in a browser, read the list, then
 * DELETE THE FILE. It connects with the application's own settings, so it always
 * reports on the database the site actually uses, and it prints no credentials.
 *
 * It only reads: nothing is created, changed or deleted.
 */

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/library/crud.php';

$db = new Database();
if (!$db->connect()) {
    echo "FAILED: could not connect using the application's settings.\n";
    echo "Create admin-quiz/library/config.php from library/config.sample.php first.\n";
    exit;
}
$conn = $db->getConnection();

function scalar($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) {
        return null;
    }
    $row = $result->fetch_row();
    return $row ? $row[0] : null;
}

function has_table($conn, $table)
{
    return (int) scalar($conn, "SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'") > 0;
}

function has_column($conn, $table, $column)
{
    return (int) scalar($conn, "SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'
          AND COLUMN_NAME = '" . $conn->real_escape_string($column) . "'") > 0;
}

function column_type($conn, $table, $column)
{
    return (string) scalar($conn, "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'
          AND COLUMN_NAME = '" . $conn->real_escape_string($column) . "'");
}

function has_index($conn, $table, $index)
{
    return (int) scalar($conn, "SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'
          AND INDEX_NAME = '" . $conn->real_escape_string($index) . "'") > 0;
}

echo "Database: ", scalar($conn, 'SELECT DATABASE()'), "\n";
echo "MySQL:    ", scalar($conn, 'SELECT VERSION()'), "\n";
echo "PHP:      ", PHP_VERSION, "\n\n";

/*
 * Each migration is recognised by what it leaves behind. 'check' returns true when
 * that change is already in place; 'needs' names a table that must exist for the
 * question to make sense at all.
 */
$migrations = array(
    '001_auth_hardening' => array(
        'needs' => 'authenticate',
        'what' => 'longer password column and unique usernames',
        'check' => function ($conn) {
            return stripos(column_type($conn, 'authenticate', 'auth_pass'), 'varchar(255)') === 0
                && has_index($conn, 'authenticate', 'uniq_auth_username');
        },
    ),
    '002_school_district_portal' => array(
        'what' => 'districts, schools and school_members tables',
        'check' => function ($conn) {
            return has_table($conn, 'districts') && has_table($conn, 'schools')
                && has_table($conn, 'school_members') && has_column($conn, 'teacher_classes', 'school_id');
        },
    ),
    '003_school_class_management' => array(
        'needs' => 'teacher_classes',
        'what' => 'one class name per school',
        'check' => function ($conn) {
            return has_index($conn, 'teacher_classes', 'uniq_teacher_classes_school_name');
        },
    ),
    '004_teacher_invitations' => array(
        'what' => 'teacher_invitations table',
        'check' => function ($conn) {
            return has_table($conn, 'teacher_invitations');
        },
    ),
    '005_school_portal_audit' => array(
        'what' => 'school_portal_audit_logs table',
        'check' => function ($conn) {
            return has_table($conn, 'school_portal_audit_logs');
        },
    ),
    '006_school_member_multiple_roles' => array(
        'needs' => 'school_members',
        'what' => 'several roles per person at one school',
        'check' => function ($conn) {
            return has_index($conn, 'school_members', 'uniq_school_member_role');
        },
    ),
    '007_district_members' => array(
        'what' => 'district_members table (district admins)',
        'check' => function ($conn) {
            return has_table($conn, 'district_members');
        },
    ),
    '008_teacher_classes_fields' => array(
        'needs' => 'teacher_classes',
        'what' => 'longer class names, optional guardian',
        'check' => function ($conn) {
            return stripos(column_type($conn, 'teacher_classes', 'name'), 'varchar(160)') === 0;
        },
    ),
    '009_teacher_class_students_fields' => array(
        'needs' => 'teacher_class_students',
        'what' => 'longer student names, optional guardian',
        'check' => function ($conn) {
            return stripos(column_type($conn, 'teacher_class_students', 'name'), 'varchar(128)') === 0;
        },
    ),
    '010_school_join_codes' => array(
        'needs' => 'schools',
        'what' => 'school join codes',
        'check' => function ($conn) {
            return has_column($conn, 'schools', 'join_code');
        },
    ),
    '011_common_assessments' => array(
        'what' => 'district common assessments and class grades',
        'check' => function ($conn) {
            return has_table($conn, 'common_assessments') && has_table($conn, 'common_assessment_schools')
                && has_table($conn, 'common_assessment_attempts') && has_table($conn, 'common_assessment_responses')
                && has_column($conn, 'teacher_classes', 'grade');
        },
    ),
);

$to_run = array();
foreach ($migrations as $name => $migration) {
    if (isset($migration['needs']) && !has_table($conn, $migration['needs'])) {
        printf("  %-36s SKIP     (no %s table on this database)\n", $name, $migration['needs']);
        continue;
    }
    $applied = (bool) call_user_func($migration['check'], $conn);
    printf("  %-36s %-8s %s\n", $name, $applied ? 'APPLIED' : 'TO RUN', $migration['what']);
    if (!$applied) {
        $to_run[] = $name;
    }
}

echo "\n";
if ($to_run) {
    echo "Run these, in this order:\n";
    foreach ($to_run as $name) {
        echo "  migrations/", $name, ".sql\n";
    }
    echo "\nBack up the database first (Plesk: Databases > Export Dump).\n";
} else {
    echo "Every migration is already applied. Nothing to run.\n";
}

echo "\nDone. Delete this file from the server once you have read it.\n";
