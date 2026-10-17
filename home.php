<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Home | <?= ucwords($_SESSION['company_name']) ?> </title>
        <?php include 'include-css.php'; ?>
    </head>
    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                <?php include 'sidebar.php'; ?>
                <?php
                $home_admin = checkadmin($auth_username);

                // Guess the Word needs migration 012; count it only once its table exists.
                $db->sql("SHOW TABLES LIKE 'tbl_junior_guess_word'");
                $has_guess_word = $db->numRows() > 0;
                $db->getResult(); // clear it before get_count() reuses $db

                // label, table, icon, colour, page it opens (admin-only pages only for admins)
                $tiles = array(
                    array('General Categories', 'category', 'fas fa-folder', 'violet', 'main-category.php'),
                    array('General Sub Categories', 'subcategory', 'fas fa-sitemap', 'blue', 'sub-category.php'),
                    array('General Questions', 'question', 'fas fa-question-circle', 'green', 'questions.php'),
                    array('Registered Users', 'users', 'fas fa-users', 'teal', $home_admin ? 'users.php' : ''),
                    array('Junior Categories', 'junior_category', 'fas fa-folder-open', 'orange', 'junior-main-category.php'),
                    array('Junior Sub Categories', 'junior_subcategory', 'fas fa-sitemap', 'pink', 'junior-sub-category.php'),
                    array('Junior Questions', 'junior_question', 'fas fa-child', 'amber', 'junior-questions.php'),
                );
                if ($has_guess_word) {
                    $tiles[] = array('Guess the Word Questions', 'tbl_junior_guess_word', 'fas fa-puzzle-piece', 'purple', 'junior-guess-word-questions.php');
                }
                ?>
                <!-- page content -->
                <div class="right_col" role="main">
                    <section class="dash-hero">
                        <div class="dash-hero__text">
                            <p class="dash-hero__date"><?= date('l, j F Y') ?></p>
                            <h1>Welcome back, <?= htmlspecialchars(ucwords((string) $_SESSION['username'])) ?></h1>
                            <p>Here's an overview of the <?= htmlspecialchars(ucwords((string) $_SESSION['company_name'])) ?> quizzes.</p>
                        </div>
                        <div class="dash-hero__actions">
                            <a class="dash-hero__action" href="questions.php"><em class="fas fa-plus"></em> Add Question</a>
                            <a class="dash-hero__action" href="junior-guess-word-questions.php"><em class="fas fa-puzzle-piece"></em> Guess the Word</a>
                            <?php if ($home_admin) { ?>
                                <a class="dash-hero__action" href="send-notifications.php"><em class="fas fa-bullhorn"></em> Send Notification</a>
                            <?php } ?>
                        </div>
                    </section>

                    <div class="dash-tiles">
                        <?php foreach ($tiles as $tile) {
                            list($label, $table, $icon, $colour, $href) = $tile;
                            $tag = $href !== '' ? 'a' : 'div';
                        ?>
                            <<?= $tag ?> class="dash-tile dash-tile--<?= $colour ?>"<?= $href !== '' ? ' href="' . $href . '"' : '' ?>>
                                <span class="dash-tile__icon"><em class="<?= $icon ?>"></em></span>
                                <span class="dash-tile__body">
                                    <span class="dash-tile__count"><?= number_format((int) get_count('id', $table)) ?></span>
                                    <span class="dash-tile__label"><?= $label ?></span>
                                </span>
                            </<?= $tag ?>>
                        <?php } ?>
                    </div>

                    <?php if ($home_admin) {
                        $year = date("Y");
                        // Rows per month, Jan to Dec. A failed query comes back as array('error' => ...),
                        // which is treated as no data. MONTHNAME is grouped on too, for ONLY_FULL_GROUP_BY.
                        $month_rows = function ($sql) use ($db) {
                            $db->sql($sql);
                            $rows = $db->getResult();
                            if (!is_array($rows) || isset($rows['error'])) {
                                error_log('home.php chart query: ' . (is_array($rows) ? $rows['error'] : 'no result'));
                                return array();
                            }
                            return $rows;
                        };
                        $result = $month_rows("SELECT COUNT(id) AS month_wise_count, MONTHNAME(date_created) AS month_name FROM monthly_leaderboard WHERE YEAR(date_created) = '$year' GROUP BY MONTH(date_created), MONTHNAME(date_created) ORDER BY MONTH(date_created)");
                        $users = $month_rows("SELECT COUNT(id) AS month_wise_user_count, MONTHNAME(date_registered) AS month_name_wise_user_count FROM users WHERE YEAR(date_registered) = '$year' GROUP BY MONTH(date_registered), MONTHNAME(date_registered) ORDER BY MONTH(date_registered)");
                    ?>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="x_panel dash-chart">
                                    <div class="x_title">
                                        <h2>Quiz Players <small>Players on the monthly leaderboard, <?= $year ?></small></h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <?php if ($result) { ?>
                                        <div id="columnchart_material" class="dash-chart__canvas"></div>
                                    <?php } else { ?>
                                        <div class="dash-empty"><em class="fas fa-chart-bar"></em>No quiz players yet in <?= $year ?>.</div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="x_panel dash-chart">
                                    <div class="x_title">
                                        <h2>New Users <small>Sign-ups per month, <?= $year ?></small></h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <?php if ($users) { ?>
                                        <div id="columnchart_material_2" class="dash-chart__canvas"></div>
                                    <?php } else { ?>
                                        <div class="dash-empty"><em class="fas fa-chart-bar"></em>No new users yet in <?= $year ?>.</div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
        <?php if ($home_admin && ($result || $users)) { ?>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="text/javascript">
                google.charts.load('current', {'packages': ['bar']});
                google.charts.setOnLoadCallback(function() {
                    // Titles are in the panel headings, so the charts only draw bars.
                    function draw(id, heading, rows, colour) {
                        var element = document.getElementById(id);
                        if (!element) {
                            return;
                        }
                        var data = google.visualization.arrayToDataTable([['Month', heading]].concat(rows));
                        var options = {
                            colors: [colour],
                            legend: { position: 'none' },
                            bar: { groupWidth: '55%' },
                            vAxis: { format: 'short', gridlines: { color: '#ECE8F5' } },
                            chartArea: { left: 48, right: 12, top: 12, bottom: 36 }
                        };
                        new google.charts.Bar(element).draw(data, google.charts.Bar.convertOptions(options));
                    }
                    draw('columnchart_material', 'Players', [
                        <?php foreach ($result as $row) {
                            echo "[" . json_encode($row['month_name']) . "," . (int) $row['month_wise_count'] . "],";
                        } ?>
                    ], '#7C3AED');
                    draw('columnchart_material_2', 'New users', [
                        <?php foreach ($users as $row) {
                            echo "[" . json_encode($row['month_name_wise_user_count']) . "," . (int) $row['month_wise_user_count'] . "],";
                        } ?>
                    ], '#F05A1E');
                });
            </script>
        <?php } ?>
    </body>
</html>
