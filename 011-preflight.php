<?php
/*
 * TEMPORARY pre-flight check for migration 011 (common assessments).
 *
 * Upload to the admin-quiz folder, open it in a browser, copy the output, then
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
    echo "FAILED: could not connect with the application's database settings.\n";
    exit;
}
$conn = $db->getConnection();

function rows($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) {
        echo '  query failed: ', $conn->error, "\n";
        return array();
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

$database = $conn->query('SELECT DATABASE()')->fetch_row()[0];
echo "Database in use: ", $database, "\n";
echo "MySQL version:   ", $conn->query('SELECT VERSION()')->fetch_row()[0], "\n";
echo "PHP version:     ", PHP_VERSION, "\n\n";

echo "1. Tables the migration points at (all three must be InnoDB)\n";
$found = array();
foreach (rows($conn, "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME IN ('districts','schools','teacher_classes','teacher_category','teacher_questions','teacher_class_students')") as $row) {
    $found[$row['TABLE_NAME']] = $row;
    printf("   %-24s %-8s %s\n", $row['TABLE_NAME'], $row['ENGINE'], $row['TABLE_COLLATION']);
}
foreach (array('districts', 'schools', 'teacher_classes', 'teacher_category', 'teacher_questions', 'teacher_class_students') as $name) {
    if (!isset($found[$name])) {
        echo "   MISSING: $name\n";
    }
}

echo "\n2. Key column types (districts.id and schools.id must be unsigned, teacher_classes.id signed)\n";
foreach (rows($conn, "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id'
      AND TABLE_NAME IN ('districts','schools','teacher_classes')") as $row) {
    printf("   %-24s %s\n", $row['TABLE_NAME'] . '.id', $row['COLUMN_TYPE']);
}

echo "\n3. Does teacher_classes.grade already exist? (it should not, before the migration)\n";
$grade = rows($conn, "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'teacher_classes' AND COLUMN_NAME = 'grade'");
echo $grade ? "   already present: " . $grade[0]['COLUMN_TYPE'] . "\n" : "   not present - good\n";

echo "\n4. Have the new tables been created already?\n";
$new = rows($conn, "SELECT TABLE_NAME FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'common_assessment%'");
if ($new) {
    foreach ($new as $row) {
        echo "   exists: ", $row['TABLE_NAME'], "\n";
    }
} else {
    echo "   none yet - the migration has not been run\n";
}

echo "\n5. Clocks (a window opens and closes by the database clock)\n";
$time = $conn->query('SELECT NOW(), @@time_zone, @@system_time_zone')->fetch_row();
echo "   MySQL now:  ", $time[0], "   time_zone=", $time[1], " (system ", $time[2], ")\n";
echo "   PHP now:    ", date('Y-m-d H:i:s'), "   timezone=", date_default_timezone_get(), "\n";
echo "   ", abs(strtotime($time[0]) - time()) <= 120 ? "the two agree - good" : "WARNING: these differ by more than two minutes", "\n";

echo "\n6. Districts and schools already set up\n";
$counts = $conn->query('SELECT (SELECT COUNT(*) FROM districts) d, (SELECT COUNT(*) FROM schools) s,
    (SELECT COUNT(*) FROM district_members WHERE status = 1) dm');
if ($counts) {
    $row = $counts->fetch_assoc();
    echo "   districts=", $row['d'], "  schools=", $row['s'], "  active district admins=", $row['dm'], "\n";
    if ((int) $row['dm'] === 0) {
        echo "   note: with no district admin, nobody can open the Assessments page yet\n";
    }
}

echo "\nDone. Copy this output, then delete this file from the server.\n";
