        <?php
        include_once 'library/crud.php';
        include 'library/functions.php';
        $fn = new Functions();
        $config = $fn->get_configurations();
        $db = new Database();
        $db->connect();

        if (isset($config['system_timezone']) && !empty($config['system_timezone'])) {
            date_default_timezone_set($config['system_timezone']);
        } else {
            date_default_timezone_set('Asia/Kolkata');
        }

        if (isset($config['system_timezone_gmt']) && !empty($config['system_timezone_gmt'])) {
            $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
        } else {
            $db->sql("SET `time_zone` = '+05:30'");
        }

        function get_count($field, $table, $where = '')
        {
            if (!empty($where))
                $where = "where " . $where;

            $sql = "SELECT COUNT(" . $field . ") as total FROM " . $table . " " . $where;
            global $db;
            $db->sql($sql);
            $res = $db->getResult();
            foreach ($res as $row)
                return $row['total'];
        }

        $auth_username = $db->escapeString($_SESSION["username"]);

        function checkadmin($auth_username)
        {
            $db = new Database();
            $db->connect();
            $db->sql("SELECT `auth_username`,`role` FROM `authenticate` WHERE `auth_username`='$auth_username' LIMIT 1");
            $res = $db->getResult();
            if (!empty($res)) {
                if ($res[0]["role"] == "admin") {
                    return true;
                } else {
                    return false;
                }
            }
        }

        if (!checkadmin($auth_username)) {
            $pages = array('languages.php', 'users.php', 'monthly-leaderboard.php', 'send-notifications.php', 'user-accounts-rights.php', 'notification-settings.php', 'privacy-policy.php', 'school-provisioning.php');
            foreach ($pages as $page) {
                if (basename($_SERVER['PHP_SELF']) == $page) {
                    exit("<center><h2 style='color:#fff;'><br><br><br><br><em style='color:#f7d701;' class='fas fa-exclamation-triangle fa-4x'></em><br><br>Access denied - You are not authorized to access this.</h2></center>");
                }
            }
        }
        if (basename($_SERVER['PHP_SELF']) == 'languages.php' && !$fn->is_language_mode_enabled()) {
            exit("<center><h2 style='color:#fff;'><br><br><br><br><em style='color:#f7d701;' class='fas fa-exclamation-triangle fa-4x'></em><br><br>Language mode is disabled - You are not allowed to access this page.</h2></center>");
        }
        ?>
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <div class="navbar nav_title text-center" style="border: 0;">
                    <img src="images/logo-460x114.png" alt="logo" width="230" class="md">
                    <img src="images/logo-half.png" alt="logo" width="56" class="sm">
                </div>
                <div class="clearfix"></div>
                <!-- menu profile quick info -->
                <div class="profile clearfix text-center">
                    <div class="profile_info">
                        <h2> <?= ucwords($_SESSION['company_name']) ?> Admin Panel</h2>
                    </div>
                </div>
                <!-- /menu profile quick info -->
                <!-- sidebar menu -->
                <?php $is_admin = checkadmin($auth_username); ?>
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                    <div class="menu_section">
                        <h3>Overview</h3>
                        <ul class="nav side-menu">
                            <li><a href="home.php"><em class="fas fa-home"></em> Home</a></li>
                            <?php if ($is_admin) { ?>
                                <li><a href="users.php"><em class="fas fa-users"></em> Users</a></li>
                                <?php if ($fn->is_language_mode_enabled()) { ?>
                                    <li><a href="languages.php"><em class="fas fa-language"></em> Languages</a></li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>

                    <?php if ($is_admin) { ?>
                        <div class="menu_section">
                            <h3>Leaderboards</h3>
                            <ul class="nav side-menu">
                                <li>
                                    <a><em class="fas fa-trophy"></em> Leaderboard<span class="fas fa-caret-down"></span></a>
                                    <ul class="nav child_menu">
                                        <li><a href="global-leaderboard.php">All Time</a></li>
                                        <li><a href="monthly-leaderboard.php">Monthly</a></li>
                                        <li><a href="daily-leaderboard.php">Daily</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a><em class="fas fa-star"></em> Junior Leaderboard<span class="fas fa-caret-down"></span></a>
                                    <ul class="nav child_menu">
                                        <li><a href="global-junior-leaderboard.php">All Time</a></li>
                                        <li><a href="monthly-junior-leaderboard.php">Monthly</a></li>
                                        <li><a href="daily-junior-leaderboard.php">Daily</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    <?php } ?>

                    <div class="menu_section">
                        <h3>Quizzes</h3>
                        <ul class="nav side-menu">
                            <li>
                                <a><em class="fas fa-book"></em> Quiz Zone<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="main-category.php">Main Category</a></li>
                                    <li><a href="sub-category.php">Sub Category</a></li>
                                    <li><a href="category-order.php">Category Order</a></li>
                                    <li><a href="questions.php">Questions</a></li>
                                    <li><a href="import-questions.php">Import Questions</a></li>
                                </ul>
                            </li>
                            <li><a href="daily-quiz.php"><em class="fas fa-calendar-alt"></em> Daily Quiz</a></li>
                            <li>
                                <a><em class="fas fa-lightbulb"></em> Learning Zone<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="learning-category.php">Category</a></li>
                                    <li><a href="learning-category-order.php">Category Order</a></li>
                                    <li><a href="learning.php">Manage Learning</a></li>
                                </ul>
                            </li>
                            <li>
                                <a><em class="fas fa-calculator"></em> Maths Quiz<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="maths-category.php">Category</a></li>
                                    <li><a href="maths-subcategory.php">Sub Category</a></li>
                                    <li><a href="maths-category-order.php">Category Order</a></li>
                                    <li><a href="maths-questions.php">Create Questions</a></li>
                                    <li><a href="maths-questions-view.php">View Questions</a></li>
                                </ul>
                            </li>
                            <li>
                                <a><em class="fas fa-graduation-cap"></em> Exam Module<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="exam-module.php">Exams</a></li>
                                    <li><a href="exam-import-question.php">Import Questions</a></li>
                                </ul>
                            </li>
                            <li>
                                <a><em class="fas fa-gift"></em> Contests<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="contest.php">Manage Contests</a></li>
                                    <li><a href="contest-questions.php">Questions</a></li>
                                    <li><a href="contest-questions-import.php">Import Questions</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <div class="menu_section">
                        <h3>Junior</h3>
                        <ul class="nav side-menu">
                            <li>
                                <a><em class="fas fa-book"></em> Junior Quiz Zone<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="junior-main-category.php">Main Category</a></li>
                                    <li><a href="junior-sub-category.php">Sub Category</a></li>
                                    <li><a href="junior-category-order.php">Category Order</a></li>
                                    <li><a href="junior-questions.php">Questions</a></li>
                                    <?php if ($is_admin) { ?>
                                        <li><a href="import-junior.php">Import Questions</a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li>
                                <a><em class="fas fa-puzzle-piece"></em> Guess the Word<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="junior-guess-word.php">Topics &amp; Levels</a></li>
                                    <li><a href="junior-guess-word-questions.php">Questions</a></li>
                                    <li><a href="junior-guess-word-import.php">Import Questions</a></li>
                                </ul>
                            </li>
                            <li><a href="daily-junior-quiz.php"><em class="fas fa-calendar-alt"></em> Daily Junior Quiz</a></li>
                            <li>
                                <a><em class="fas fa-lightbulb"></em> Junior Learning Zone<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="junior-learning-category.php">Category</a></li>
                                    <li><a href="junior-learning-category-order.php">Category Order</a></li>
                                    <li><a href="junior-learning.php">Manage Learning</a></li>
                                </ul>
                            </li>
                            <li>
                                <a><em class="fas fa-calculator"></em> Junior Maths Quiz<span class="fas fa-caret-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="maths-junior-category.php">Category</a></li>
                                    <li><a href="maths-junior-subcategory.php">Sub Category</a></li>
                                    <li><a href="maths-junior-category-order.php">Category Order</a></li>
                                    <li><a href="maths-junior-questions.php">Create Questions</a></li>
                                    <li><a href="maths-junior-questions-view.php">View Questions</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <div class="menu_section">
                        <h3>Management</h3>
                        <ul class="nav side-menu">
                            <li><a href="question-reports.php"><em class="fas fa-flag"></em> Question Reports</a></li>
                            <?php if ($is_admin) { ?>
                                <li><a href="send-notifications.php"><em class="fas fa-bullhorn"></em> Send Notifications</a></li>
                                <li><a href="user-accounts-rights.php"><em class="fas fa-key"></em> Accounts &amp; Rights</a></li>
                                <li><a href="school-provisioning.php"><em class="fas fa-school"></em> School Provisioning</a></li>
                                <li>
                                    <a><em class="fas fa-cog"></em> Settings<span class="fas fa-caret-down"></span></a>
                                    <ul class="nav child_menu">
                                        <li><a href="system-configurations.php">System Configurations</a></li>
                                        <li><a href="notification-settings.php">Notification Settings</a></li>
                                        <li><a href="badges.php">Badges Settings</a></li>
                                        <li><a href="about-us.php">About Us</a></li>
                                        <li><a href="instructions.php">Instructions</a></li>
                                        <li><a href="privacy-policy.php">Privacy Policy</a></li>
                                        <li><a href="terms-conditions.php">Terms and Conditions</a></li>
                                        <li><a href="web-firebase-settings.php">Web Firebase Settings</a></li>
                                    </ul>
                                </li>
                                <li><a href="system-update.php"><em class="fas fa-cloud-download-alt"></em> System Update</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- top navigation -->
        <?php
        $header_name = ucwords((string) $_SESSION['username']);
        $header_initial = strtoupper(mb_substr($header_name, 0, 1)) ?: '?';
        $header_role = $is_admin ? 'Administrator' : 'Editor';
        ?>
        <div class="top_nav">
            <div class="nav_menu">
                <nav class="admin-header">
                    <div class="nav toggle">
                        <a id="menu_toggle" role="button" tabindex="0" aria-label="Show or hide the menu" title="Show or hide the menu"><em class="fa fa-bars"></em></a>
                    </div>
                    <!-- Filled from the sidebar link for this page, e.g. Junior › Guess the Word › Questions -->
                    <div class="admin-header__crumbs" id="admin_crumbs" aria-label="You are here"></div>
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="javascript:;" class="user-profile dropdown-toggle admin-header__user" data-toggle="dropdown" aria-expanded="false">
                                <span class="admin-header__avatar" aria-hidden="true"><?= htmlspecialchars($header_initial) ?></span>
                                <span class="admin-header__who">
                                    <span class="admin-header__name"><?= htmlspecialchars($header_name) ?></span>
                                    <span class="admin-header__role"><?= $header_role ?></span>
                                </span>
                                <span class="fa fa-angle-down admin-header__caret"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-usermenu pull-right admin-header__menu">
                                <li class="admin-header__menu-head">
                                    <strong><?= htmlspecialchars($header_name) ?></strong>
                                    <span><?= $header_role ?> · <?= htmlspecialchars(ucwords((string) $_SESSION['company_name'])) ?></span>
                                </li>
                                <li><a href="password.php"><em class="fas fa-key"></em> Change Password</a></li>
                                <li class="admin-header__logout"><a href="logout.php"><em class="fas fa-sign-out-alt"></em> Log Out</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <script>
            // Breadcrumb from the sidebar: section heading › menu group › page.
            jQuery(function($) {
                var here = location.pathname.split('/').pop() || 'home.php';
                var $link = $('#sidebar-menu a').filter(function() {
                    return $(this).attr('href') === here;
                }).first();
                var parts = [];
                if ($link.length) {
                    parts.push($link.closest('.menu_section').children('h3').text());
                    var $group = $link.closest('ul.child_menu').parent('li').children('a');
                    if ($group.length) {
                        parts.push($group.clone().children().remove().end().text());
                    }
                    parts.push($link.clone().children().remove().end().text());
                } else {
                    // Pages not in the menu (e.g. Change Password): use the page title.
                    parts.push(document.title.split('|')[0]);
                }
                var $crumbs = $('#admin_crumbs');
                $.each(parts, function(i, part) {
                    part = $.trim(part);
                    if (!part) {
                        return;
                    }
                    if ($crumbs.children().length) {
                        $('<span class="admin-header__sep" aria-hidden="true">').text('›').appendTo($crumbs);
                    }
                    $('<span>').addClass(i === parts.length - 1 ? 'admin-header__page' : 'admin-header__crumb').text(part).appendTo($crumbs);
                });
            });
            // The menu toggle is a link without href, so let Enter and Space work it too.
            jQuery(document).on('keydown', '#menu_toggle', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    jQuery(this).trigger('click');
                }
            });
        </script>
        <!-- /top navigation -->