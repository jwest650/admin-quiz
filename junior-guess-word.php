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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guess the Word Topics &amp; Levels | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
    <?php include 'include-css.php'; ?>
</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include 'sidebar.php'; ?>
            <?php
            $show_language = $fn->is_language_mode_enabled();
            $languages = array();
            if ($show_language) {
                $db->sql("SET NAMES 'utf8'");
                $db->sql("SELECT * FROM `languages` ORDER BY id DESC");
                $languages = $db->getResult();
            }

            function guess_word_language_select($id, $languages)
            {
                echo "<select id='$id' name='language_id' required class='form-control'><option value=''>Select language</option>";
                foreach ($languages as $language) {
                    echo "<option value='" . (int) $language['id'] . "'>" . htmlspecialchars($language['language']) . "</option>";
                }
                echo '</select>';
            }
            ?>
            <!-- page content -->
            <div class="right_col" role="main">
                <br />
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Guess the Word: Topics <small>Step 1 of 3</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <p class="text-muted">A topic is what the child picks first, e.g. <em>Animals</em> or <em>Fruits</em>. Then add levels to it below, and words on the <a href="junior-guess-word-questions.php">Questions</a> page.</p>
                                <form id="topic_form" method="POST" action="db_operations.php" class="form-horizontal form-label-left" enctype="multipart/form-data">
                                    <input type="hidden" name="add_guess_word_topic" value="1">
                                    <div class="form-group row">
                                        <?php if ($show_language) { ?>
                                            <div class="col-md-3 col-sm-12">
                                                <label for="topic_language">Language</label>
                                                <?php guess_word_language_select('topic_language', $languages); ?>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-<?= $show_language ? 3 : 5 ?> col-sm-12">
                                            <label for="topic_name">Topic name</label>
                                            <input type="text" id="topic_name" name="name" required maxlength="100" class="form-control" placeholder="e.g. Animals">
                                        </div>
                                        <div class="col-md-2 col-sm-12">
                                            <label for="topic_order">Order</label>
                                            <input type="number" id="topic_order" name="row_order" min="0" value="0" class="form-control">
                                        </div>
                                        <div class="col-md-<?= $show_language ? 4 : 5 ?> col-sm-12">
                                            <label for="topic_image">Picture</label>
                                            <input type="file" id="topic_image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                                        </div>
                                    </div>
                                    <div id="topic_result"></div>
                                    <button type="submit" id="topic_btn" class="btn btn-warning">Add Topic</button>
                                </form>
                                <hr>
                                <div id="topic_toolbar">
                                    <button class="btn btn-danger btn-sm" id="delete_topics" title="Delete selected topics"><em class='fa fa-trash'></em></button>
                                </div>
                                <table aria-describedby="topics" class='table-striped gw-table' id='topic_list' data-toggle="table" data-url="get-list.php?table=junior_guess_word_topic" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100]" data-search="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="row_order" data-sort-order="asc" data-toolbar="#topic_toolbar" data-mobile-responsive="true" data-maintain-selected="true">
                                    <thead>
                                        <tr>
                                            <th scope="col" data-field="state" data-checkbox="true"></th>
                                            <th scope="col" data-field="id" data-sortable="true">ID</th>
                                            <th scope="col" data-field="status">Status</th>
                                            <?php if ($show_language) { ?>
                                                <th scope="col" data-field="language">Language</th>
                                            <?php } ?>
                                            <th scope="col" data-field="name" data-sortable="true">Topic</th>
                                            <th scope="col" data-field="row_order" data-sortable="true">Order</th>
                                            <th scope="col" data-field="no_of_levels">Levels</th>
                                            <th scope="col" data-field="no_of_words">Words</th>
                                            <th scope="col" data-field="image">Picture</th>
                                            <th scope="col" data-field="operate" data-events="topicEvents">Operate</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Guess the Word: Levels <small>Step 2 of 3</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <p class="text-muted">Levels belong to one topic and are shown to the child in order, e.g. <em>Level 1</em>, <em>Level 2</em>. Leave Order blank to add the level after the topic's others.</p>
                                <form id="level_form" method="POST" action="db_operations.php" class="form-horizontal form-label-left">
                                    <input type="hidden" name="add_guess_word_level" value="1">
                                    <div class="form-group row">
                                        <div class="col-md-5 col-sm-12">
                                            <label for="level_topic">Topic</label>
                                            <select id="level_topic" name="category" required class="form-control gw-topic-select">
                                                <option value="">Select topic</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 col-sm-12">
                                            <label for="level_name">Level name</label>
                                            <input type="text" id="level_name" name="name" required maxlength="60" class="form-control" placeholder="e.g. Level 1">
                                        </div>
                                        <div class="col-md-2 col-sm-12">
                                            <label for="level_order">Order</label>
                                            <input type="number" id="level_order" name="row_order" min="0" class="form-control" placeholder="Auto">
                                        </div>
                                    </div>
                                    <div id="level_result"></div>
                                    <button type="submit" id="level_btn" class="btn btn-warning">Add Level</button>
                                </form>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select id="filter_level_topic" class="form-control gw-topic-select">
                                            <option value="">All topics</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="level_toolbar">
                                    <button class="btn btn-danger btn-sm" id="delete_levels" title="Delete selected levels"><em class='fa fa-trash'></em></button>
                                </div>
                                <table aria-describedby="levels" class='table-striped gw-table' id='level_list' data-toggle="table" data-url="get-list.php?table=junior_guess_word_level" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100]" data-search="true" data-show-refresh="true" data-trim-on-search="false" data-toolbar="#level_toolbar" data-mobile-responsive="true" data-maintain-selected="true" data-query-params="levelParams">
                                    <thead>
                                        <tr>
                                            <th scope="col" data-field="state" data-checkbox="true"></th>
                                            <th scope="col" data-field="id" data-sortable="true">ID</th>
                                            <th scope="col" data-field="status">Status</th>
                                            <th scope="col" data-field="topic" data-sortable="true">Topic</th>
                                            <th scope="col" data-field="name" data-sortable="true">Level</th>
                                            <th scope="col" data-field="row_order" data-sortable="true">Order</th>
                                            <th scope="col" data-field="no_of_words">Words</th>
                                            <th scope="col" data-field="operate" data-events="levelEvents">Operate</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page content -->

        <div class="modal fade" id='editTopicModal' tabindex="-1" role="dialog" aria-labelledby="editTopicLabel">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="editTopicLabel">Edit Topic</h4>
                    </div>
                    <div class="modal-body">
                        <form id="edit_topic_form" method="POST" action="db_operations.php" enctype="multipart/form-data">
                            <input type="hidden" name="update_guess_word_topic" value="1">
                            <input type="hidden" name="topic_id" id="edit_topic_id">
                            <?php if ($show_language) { ?>
                                <div class="form-group">
                                    <label for="edit_topic_language">Language</label>
                                    <?php guess_word_language_select('edit_topic_language', $languages); ?>
                                </div>
                            <?php } ?>
                            <div class="form-group">
                                <label for="edit_topic_name">Topic name</label>
                                <input type="text" name="name" id="edit_topic_name" required maxlength="100" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_topic_order">Order</label>
                                <input type="number" name="row_order" id="edit_topic_order" min="0" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_topic_image">Picture <small>( Leave it blank for no change )</small></label>
                                <input type="file" name="image" id="edit_topic_image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div id="edit_topic_result"></div>
                            <button type="submit" id="edit_topic_btn" class="btn btn-success">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id='editLevelModal' tabindex="-1" role="dialog" aria-labelledby="editLevelLabel">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="editLevelLabel">Edit Level</h4>
                    </div>
                    <div class="modal-body">
                        <form id="edit_level_form" method="POST" action="db_operations.php">
                            <input type="hidden" name="update_guess_word_level" value="1">
                            <input type="hidden" name="level_id" id="edit_level_id">
                            <div class="form-group">
                                <label for="edit_level_topic">Topic</label>
                                <select id="edit_level_topic" name="category" required class="form-control gw-topic-select">
                                    <option value="">Select topic</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_level_name">Level name</label>
                                <input type="text" name="name" id="edit_level_name" required maxlength="60" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_level_order">Order</label>
                                <input type="number" name="row_order" id="edit_level_order" min="0" class="form-control">
                            </div>
                            <div id="edit_level_result"></div>
                            <button type="submit" id="edit_level_btn" class="btn btn-success">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- footer content -->
        <?php include 'footer.php'; ?>
        <!-- /footer content -->
    </div>

    <script src="js/guess-word-admin.js"></script>
    <script>
        var $topicSelects = $('.gw-topic-select');
        GuessWord.loadTopics($topicSelects);

        function levelParams(p) {
            return {
                category: $('#filter_level_topic').val(),
                limit: p.limit,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search
            };
        }
        $('#filter_level_topic').on('change', function() {
            $('#level_list').bootstrapTable('refresh');
        });

        function whenValid(form, run) {
            $(form).on('submit', function(e) {
                e.preventDefault();
                if ($(this).validate().form()) {
                    run(this);
                }
            });
        }

        whenValid('#topic_form', function(form) {
            GuessWord.submitForm(form, $('#topic_btn'), $('#topic_result'), function() {
                $('#topic_name, #topic_image').val('');
                GuessWord.loadTopics($topicSelects);
            });
        });
        whenValid('#edit_topic_form', function(form) {
            GuessWord.submitForm(form, $('#edit_topic_btn'), $('#edit_topic_result'), function() {
                GuessWord.loadTopics($topicSelects);
                setTimeout(function() {
                    $('#editTopicModal').modal('hide');
                }, 1200);
            });
        });
        whenValid('#level_form', function(form) {
            GuessWord.submitForm(form, $('#level_btn'), $('#level_result'), function() {
                // Keep the topic so several levels are quick to add.
                $('#level_name, #level_order').val('');
                $('#level_name').focus();
            });
        });
        whenValid('#edit_level_form', function(form) {
            GuessWord.submitForm(form, $('#edit_level_btn'), $('#edit_level_result'), function() {
                setTimeout(function() {
                    $('#editLevelModal').modal('hide');
                }, 1200);
            });
        });

        window.topicEvents = {
            'click .edit-topic': function(e, value, row) {
                $('#edit_topic_id').val(row.id);
                $('#edit_topic_name').val(row.name_text);
                $('#edit_topic_order').val(row.row_order);
                $('#edit_topic_language').val(row.language_id);
                $('#edit_topic_image').val('');
            },
            'click .delete-topic': function(e, value, row) {
                if (confirm('Delete the topic "' + row.name_text + '"? Its ' + row.no_of_levels + ' level(s) and ' + row.no_of_words + ' word(s) will be deleted too.')) {
                    GuessWord.remove('delete_guess_word_topic', String(row.id), function() {
                        GuessWord.loadTopics($topicSelects);
                    });
                }
            }
        };
        window.levelEvents = {
            'click .edit-level': function(e, value, row) {
                $('#edit_level_id').val(row.id);
                $('#edit_level_topic').val(row.category);
                $('#edit_level_name').val(row.name_text);
                $('#edit_level_order').val(row.row_order);
            },
            'click .delete-level': function(e, value, row) {
                if (confirm('Delete the level "' + row.name_text + '"? Its ' + row.no_of_words + ' word(s) will be deleted too.')) {
                    GuessWord.remove('delete_guess_word_level', String(row.id));
                }
            }
        };

        GuessWord.bulkDelete($('#delete_topics'), $('#topic_list'), 'delete_guess_word_topic', 'Delete the selected topics? Their levels and words will be deleted too.', function() {
            GuessWord.loadTopics($topicSelects);
        });
        GuessWord.bulkDelete($('#delete_levels'), $('#level_list'), 'delete_guess_word_level', 'Delete the selected levels? Their words will be deleted too.');
    </script>
</body>

</html>
