<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}
require_once 'library/crud.php';
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8mb4'");
$auth_username = $db->escapeString($_SESSION['username']);
$db->sql("SELECT role FROM authenticate WHERE auth_username = '$auth_username' LIMIT 1");
$auth = $db->getResult();
if (empty($auth) || $auth[0]['role'] !== 'admin') {
    http_response_code(403);
    exit('<center><h2>Access denied</h2></center>');
}
if (empty($_SESSION['school_provisioning_csrf'])) {
    $_SESSION['school_provisioning_csrf'] = bin2hex(random_bytes(32));
}
$message = '';
$message_type = 'success';
function provisioning_redirect($message, $type = 'success')
{
    $_SESSION['school_provisioning_flash'] = array('message' => $message, 'type' => $type);
    header('Location: school-provisioning.php');
    exit;
}
/* Rows for a query, read once (getResult() hands them over a single time). */
function provisioning_rows($db, $sql)
{
    return $db->sql($sql) ? (array) $db->getResult() : array();
}
/* The active application user with this email, or null. */
function provisioning_find_user($db, $email)
{
    $email = $db->escapeString($email);
    $user = provisioning_rows($db, "SELECT id, name FROM users WHERE email = '$email' AND status = 1 LIMIT 1");
    return empty($user) ? null : $user[0];
}
/* A positive integer from the form, or 0. */
function provisioning_id($field)
{
    $value = filter_var(isset($_POST[$field]) ? $_POST[$field] : null, FILTER_VALIDATE_INT);
    return $value && $value > 0 ? (int) $value : 0;
}
if (!empty($_SESSION['school_provisioning_flash'])) {
    $message = $_SESSION['school_provisioning_flash']['message'];
    $message_type = $_SESSION['school_provisioning_flash']['type'];
    unset($_SESSION['school_provisioning_flash']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if (!hash_equals($_SESSION['school_provisioning_csrf'], $csrf)) {
        provisioning_redirect('Your form expired. Refresh and try again.', 'danger');
    }
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'district') {
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        if ($name === '' || strlen($name) > 160) provisioning_redirect('Enter a district name up to 160 characters.', 'danger');
        $name = $db->escapeString($name);
        $ok = $db->sql("INSERT INTO districts (name) VALUES ('$name')");
        provisioning_redirect($ok ? 'District created.' : 'Unable to create district.', $ok ? 'success' : 'danger');
    }
    if ($action === 'school') {
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $code = strtoupper(trim(isset($_POST['code']) ? $_POST['code'] : ''));
        $district_id = filter_var(isset($_POST['district_id']) ? $_POST['district_id'] : null, FILTER_VALIDATE_INT);
        if ($name === '' || strlen($name) > 160 || !preg_match('/^[A-Z0-9_-]{2,48}$/', $code) || $district_id === false || $district_id < 1) provisioning_redirect('Enter a valid school name, code, and district.', 'danger');
        $name = $db->escapeString($name); $code = $db->escapeString($code); $district_id = $db->escapeString($district_id);
        $ok = $db->sql("INSERT INTO schools (district_id, name, code) VALUES ('$district_id', '$name', '$code')");
        provisioning_redirect($ok ? 'School created.' : 'Unable to create school. The code may already exist.', $ok ? 'success' : 'danger');
    }
    if ($action === 'school_admin') {
        $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
        $school_id = provisioning_id('school_id');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$school_id) provisioning_redirect('Enter a valid user email and school.', 'danger');
        $user = provisioning_find_user($db, $email);
        if (!$user) provisioning_redirect('No active application user matches that email.', 'danger');
        $user_id = (int) $user['id'];
        // Someone removed earlier keeps their record, so assigning them again switches it back on.
        $existing = provisioning_rows($db, "SELECT id, status FROM school_members WHERE school_id = $school_id AND user_id = $user_id AND role = 'school_admin' LIMIT 1");
        if ($existing && (int) $existing[0]['status'] === 1) provisioning_redirect($user['name'] . ' is already a school admin there.', 'danger');
        $ok = $existing
            ? $db->sql("UPDATE school_members SET status = 1 WHERE id = " . (int) $existing[0]['id'])
            : $db->sql("INSERT INTO school_members (school_id, user_id, role) VALUES ($school_id, $user_id, 'school_admin')");
        provisioning_redirect($ok ? $user['name'] . ' is now a school admin.' : 'Unable to assign the school admin.', $ok ? 'success' : 'danger');
    }
    if ($action === 'district_admin') {
        $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
        $district_id = provisioning_id('district_id');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$district_id) provisioning_redirect('Enter a valid user email and district.', 'danger');
        $user = provisioning_find_user($db, $email);
        if (!$user) provisioning_redirect('No active application user matches that email.', 'danger');
        $user_id = (int) $user['id'];
        $existing = provisioning_rows($db, "SELECT id, status FROM district_members WHERE district_id = $district_id AND user_id = $user_id LIMIT 1");
        if ($existing && (int) $existing[0]['status'] === 1) provisioning_redirect($user['name'] . ' is already a district admin there.', 'danger');
        $ok = $existing
            ? $db->sql("UPDATE district_members SET status = 1 WHERE id = " . (int) $existing[0]['id'])
            : $db->sql("INSERT INTO district_members (district_id, user_id) VALUES ($district_id, $user_id)");
        provisioning_redirect($ok ? $user['name'] . ' is now a district admin. They can see every school in the district.' : 'Unable to assign the district admin.', $ok ? 'success' : 'danger');
    }
    if ($action === 'remove_school_admin') {
        $membership_id = provisioning_id('membership_id');
        $row = $membership_id ? provisioning_rows($db, "SELECT sm.id, u.name, s.name AS school_name FROM school_members sm INNER JOIN users u ON u.id = sm.user_id INNER JOIN schools s ON s.id = sm.school_id WHERE sm.id = $membership_id AND sm.role = 'school_admin' AND sm.status = 1 LIMIT 1") : array();
        if (!$row) provisioning_redirect('That school admin was not found. They may already have been removed.', 'danger');
        $ok = $db->sql("UPDATE school_members SET status = 0 WHERE id = $membership_id");
        provisioning_redirect($ok ? $row[0]['name'] . ' is no longer a school admin at ' . $row[0]['school_name'] . '. They lose access next time they sign in.' : 'Unable to remove the school admin.', $ok ? 'success' : 'danger');
    }
    if ($action === 'remove_district_admin') {
        $membership_id = provisioning_id('membership_id');
        $row = $membership_id ? provisioning_rows($db, "SELECT dm.id, u.name, d.name AS district_name FROM district_members dm INNER JOIN users u ON u.id = dm.user_id INNER JOIN districts d ON d.id = dm.district_id WHERE dm.id = $membership_id AND dm.status = 1 LIMIT 1") : array();
        if (!$row) provisioning_redirect('That district admin was not found. They may already have been removed.', 'danger');
        $ok = $db->sql("UPDATE district_members SET status = 0 WHERE id = $membership_id");
        provisioning_redirect($ok ? $row[0]['name'] . ' is no longer a district admin of ' . $row[0]['district_name'] . '. They lose access next time they sign in.' : 'Unable to remove the district admin.', $ok ? 'success' : 'danger');
    }
    if ($action === 'deactivate_school' || $action === 'reactivate_school') {
        $school_id = provisioning_id('school_id');
        $school = $school_id ? provisioning_rows($db, "SELECT id, name FROM schools WHERE id = $school_id LIMIT 1") : array();
        if (!$school) provisioning_redirect('That school was not found.', 'danger');
        $status = $action === 'reactivate_school' ? 1 : 0;
        $ok = $db->sql("UPDATE schools SET status = $status WHERE id = $school_id");
        $done = $status ? ' is active again. Its admins, teachers and classes can use it.' : ' is deactivated. It is hidden from the portals, and its classes and history are kept.';
        provisioning_redirect($ok ? $school[0]['name'] . $done : 'Unable to change the school.', $ok ? 'success' : 'danger');
    }
    if ($action === 'delete_school') {
        $school_id = provisioning_id('school_id');
        $school = $school_id ? provisioning_rows($db, "SELECT s.id, s.name, (SELECT COUNT(*) FROM teacher_classes tc WHERE tc.school_id = s.id) AS class_count FROM schools s WHERE s.id = $school_id LIMIT 1") : array();
        if (!$school) provisioning_redirect('That school was not found.', 'danger');
        // Only schools with no classes can be deleted: otherwise classes and rosters would point at nothing.
        if ((int) $school[0]['class_count'] > 0) provisioning_redirect($school[0]['name'] . ' has classes, so it can only be deactivated.', 'danger');
        $db->sql("DELETE FROM teacher_invitations WHERE school_id = $school_id");
        $db->sql("DELETE FROM school_portal_audit_logs WHERE school_id = $school_id");
        // Memberships (admins, teachers, students) are removed with the school.
        $ok = $db->sql("DELETE FROM schools WHERE id = $school_id");
        provisioning_redirect($ok ? $school[0]['name'] . ' was deleted.' : 'Unable to delete the school.', $ok ? 'success' : 'danger');
    }
    provisioning_redirect('Unknown provisioning action.', 'danger');
}
$districts = provisioning_rows($db, "SELECT id, name FROM districts WHERE status = 1 ORDER BY name ASC");
$schools = provisioning_rows($db, "SELECT s.id, s.name, s.code, s.status, d.name AS district_name, (SELECT COUNT(*) FROM teacher_classes tc WHERE tc.school_id = s.id) AS class_count FROM schools s LEFT JOIN districts d ON d.id = s.district_id ORDER BY s.status DESC, s.name ASC");
$memberships = provisioning_rows($db, "SELECT sm.id, s.name AS school_name, u.name AS user_name, u.email FROM school_members sm INNER JOIN schools s ON s.id = sm.school_id INNER JOIN users u ON u.id = sm.user_id WHERE sm.role = 'school_admin' AND sm.status = 1 ORDER BY s.name ASC, u.name ASC");
$district_admins = provisioning_rows($db, "SELECT dm.id, d.name AS district_name, u.name AS user_name, u.email, (SELECT COUNT(*) FROM schools s WHERE s.district_id = d.id AND s.status = 1) AS school_count FROM district_members dm INNER JOIN districts d ON d.id = dm.district_id INNER JOIN users u ON u.id = dm.user_id WHERE dm.status = 1 ORDER BY d.name ASC, u.name ASC");
$csrf_field = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['school_provisioning_csrf'], ENT_QUOTES, 'UTF-8') . '">';
/* A one-button form for a row action, with a confirmation first. */
function provisioning_row_action($csrf_field, $action, $id_field, $id, $label, $class, $confirm)
{
    return '<form method="post" style="display:inline-block;margin:0 4px 0 0" onsubmit="return confirm(' . htmlspecialchars(json_encode($confirm), ENT_QUOTES, 'UTF-8') . ')">'
        . $csrf_field
        . '<input type="hidden" name="action" value="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name="' . htmlspecialchars($id_field, ENT_QUOTES, 'UTF-8') . '" value="' . (int) $id . '">'
        . '<button type="submit" class="btn btn-xs ' . $class . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</button></form>';
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>School Provisioning | Admin</title><?php include 'include-css.php'; ?></head>
<body class="nav-md"><div class="container body"><div class="main_container"><?php include 'sidebar.php'; ?><div class="right_col" role="main"><div class="row"><div class="col-md-12"><div class="x_panel"><div class="x_title"><h2>School Portal Provisioning</h2><div class="clearfix"></div></div><div class="x_content">
<?php if ($message) : ?><div class="alert alert-<?php echo htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
<div class="row">
<div class="col-md-3 col-sm-6"><h4>Create district</h4><form method="post"><?php echo $csrf_field; ?><input type="hidden" name="action" value="district"><input class="form-control" name="name" maxlength="160" placeholder="District name" required><button class="btn btn-primary" style="margin-top:10px">Create district</button></form></div>
<div class="col-md-3 col-sm-6"><h4>Create school</h4><form method="post"><?php echo $csrf_field; ?><input type="hidden" name="action" value="school"><input class="form-control" name="name" maxlength="160" placeholder="School name" required><input class="form-control" name="code" maxlength="48" placeholder="School code" style="margin-top:8px" required><select class="form-control" name="district_id" style="margin-top:8px" required><option value="">Select district</option><?php foreach ($districts as $district) : ?><option value="<?php echo (int) $district['id']; ?>"><?php echo htmlspecialchars($district['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select><button class="btn btn-primary" style="margin-top:10px">Create school</button></form></div>
<div class="col-md-3 col-sm-6"><h4>Assign school admin</h4><form method="post"><?php echo $csrf_field; ?><input type="hidden" name="action" value="school_admin"><input class="form-control" type="email" name="email" placeholder="Existing user email" required><select class="form-control" name="school_id" style="margin-top:8px" required><option value="">Select school</option><?php foreach ($schools as $school) : if ((int) $school['status'] !== 1) continue; ?><option value="<?php echo (int) $school['id']; ?>"><?php echo htmlspecialchars($school['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select><button class="btn btn-primary" style="margin-top:10px">Assign school admin</button></form></div>
<div class="col-md-3 col-sm-6"><h4>Assign district admin</h4><form method="post"><?php echo $csrf_field; ?><input type="hidden" name="action" value="district_admin"><input class="form-control" type="email" name="email" placeholder="Existing user email" required><select class="form-control" name="district_id" style="margin-top:8px" required><option value="">Select district</option><?php foreach ($districts as $district) : ?><option value="<?php echo (int) $district['id']; ?>"><?php echo htmlspecialchars($district['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select><p class="help-block" style="margin:6px 0 0">Sees every school in the district, read-only.</p><button class="btn btn-primary" style="margin-top:10px">Assign district admin</button></form></div>
</div><hr>
<h4>Schools</h4>
<p class="help-block">Deactivating hides a school from the portals and keeps its classes and history. Only schools with no classes can be deleted.</p>
<table class="table table-striped"><thead><tr><th>Name</th><th>Code</th><th>District</th><th>Classes</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($schools)) : ?><tr><td colspan="6">No schools yet.</td></tr><?php endif; ?>
<?php foreach ($schools as $school) : $school_name = $school['name']; $is_active = (int) $school['status'] === 1; ?>
<tr>
<td><?php echo htmlspecialchars($school_name, ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($school['code'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($school['district_name'] ?: 'Independent', ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo (int) $school['class_count']; ?></td>
<td><?php echo $is_active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
<td>
<?php
if ($is_active) {
    echo provisioning_row_action($csrf_field, 'deactivate_school', 'school_id', $school['id'], 'Deactivate', 'btn-warning', 'Deactivate ' . $school_name . '? It will be hidden from the portals. Its classes and history are kept, and you can reactivate it later.');
} else {
    echo provisioning_row_action($csrf_field, 'reactivate_school', 'school_id', $school['id'], 'Reactivate', 'btn-success', 'Reactivate ' . $school_name . '?');
}
if ((int) $school['class_count'] === 0) {
    echo provisioning_row_action($csrf_field, 'delete_school', 'school_id', $school['id'], 'Delete', 'btn-danger', 'Permanently delete ' . $school_name . '? Its admins, teachers, students, invites and activity history for this school are removed. This cannot be undone.');
}
?>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<h4>School admins</h4><table class="table table-striped"><thead><tr><th>User</th><th>Email</th><th>School</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($memberships)) : ?><tr><td colspan="4">No school admins yet.</td></tr><?php endif; ?>
<?php foreach ($memberships as $membership) : ?><tr><td><?php echo htmlspecialchars($membership['user_name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($membership['email'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($membership['school_name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo provisioning_row_action($csrf_field, 'remove_school_admin', 'membership_id', $membership['id'], 'Remove', 'btn-danger', 'Remove ' . $membership['user_name'] . ' as a school admin at ' . $membership['school_name'] . '?'); ?></td></tr><?php endforeach; ?>
</tbody></table>
<h4>District admins</h4><table class="table table-striped"><thead><tr><th>User</th><th>Email</th><th>District</th><th>Active schools</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($district_admins)) : ?><tr><td colspan="5">No district admins yet.</td></tr><?php endif; ?>
<?php foreach ($district_admins as $district_admin) : ?><tr><td><?php echo htmlspecialchars($district_admin['user_name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($district_admin['email'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($district_admin['district_name'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo (int) $district_admin['school_count']; ?></td><td><?php echo provisioning_row_action($csrf_field, 'remove_district_admin', 'membership_id', $district_admin['id'], 'Remove', 'btn-danger', 'Remove ' . $district_admin['user_name'] . ' as a district admin of ' . $district_admin['district_name'] . '?'); ?></td></tr><?php endforeach; ?>
</tbody></table>
</div></div></div></div></div></div></div><?php include 'footer.php'; ?></body></html>
