<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
}
// Opened from the Users list with ?user_id=.
$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if ($user_id <= 0) {
    header("location:users.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Payments | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
    <?php include 'include-css.php'; ?>
</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include 'sidebar.php'; ?>
            <?php
            // sidebar.php blocks this page for non-admins only by name, so check here too.
            if (!checkadmin($auth_username)) {
                exit("<div class='right_col' role='main'><div class='x_panel'><div class='alert alert-danger'>Access denied - You are not authorized to access this page.</div></div></div>");
            }
            $stmt = $db->getConnection()->prepare('SELECT username, email, reference, currency, amount, channel, status, created_at FROM payment_history WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $stmt = $db->getConnection()->prepare('SELECT name, email FROM users WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $paid = array();
            foreach ($payments as $payment) {
                if ($payment['status'] === 'success') {
                    $currency = $payment['currency'];
                    $paid[$currency] = (isset($paid[$currency]) ? $paid[$currency] : 0) + $payment['amount'] / 100;
                }
            }
            $who = $user ? ($user['name'] !== '' ? $user['name'] : $user['email']) : 'User #' . $user_id;
            ?>
            <!-- page content -->
            <div class="right_col" role="main">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Payments <small><?= htmlspecialchars($who) ?></small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <p><a href="users.php"><em class="fas fa-arrow-left"></em> Back to Users</a></p>
                                <?php if (!$payments) { ?>
                                    <div class="dash-empty" style="height:220px;"><em class="fas fa-receipt"></em>No payments recorded for this user.</div>
                                <?php } else { ?>
                                    <p>
                                        <?= count($payments) ?> payment<?= count($payments) === 1 ? '' : 's' ?>.
                                        <?php foreach ($paid as $currency => $total) { ?>
                                            <span class="label label-success"><?= htmlspecialchars($currency) ?> <?= number_format($total, 2) ?> paid</span>
                                        <?php } ?>
                                    </p>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Reference</th>
                                                    <th>Amount</th>
                                                    <th>Channel</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($payments as $payment) {
                                                    $status = (string) $payment['status'];
                                                    $label = $status === 'success' ? 'label-success' : ($status === 'failed' ? 'label-danger' : 'label-default');
                                                ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($payment['created_at']) ?></td>
                                                        <td><?= htmlspecialchars($payment['username']) ?></td>
                                                        <td><?= htmlspecialchars($payment['email']) ?></td>
                                                        <td><code><?= htmlspecialchars($payment['reference']) ?></code></td>
                                                        <td><?= htmlspecialchars($payment['currency']) ?> <?= number_format($payment['amount'] / 100, 2) ?></td>
                                                        <td><?= htmlspecialchars(ucfirst((string) $payment['channel'])) ?></td>
                                                        <td><span class="label <?= $label ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>
</body>

</html>
