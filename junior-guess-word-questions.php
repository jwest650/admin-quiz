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
    <title>Guess the Word Questions | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
    <?php include 'include-css.php'; ?>
</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include 'sidebar.php'; ?>
            <!-- page content -->
            <div class="right_col" role="main">
                <br />
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Guess the Word: Questions <small>Step 3 of 3</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="alert alert-warning" id="no_topics" style="display:none;">Add a topic and a level on the <a href="junior-guess-word.php"><strong>Topics &amp; Levels</strong></a> page first.</div>
                                <form id="word_form" method="POST" action="db_operations.php" class="form-horizontal form-label-left" enctype="multipart/form-data">
                                    <input type="hidden" name="add_junior_guess_word" value="1">
                                    <div class="form-group row">
                                        <div class="col-md-6 col-sm-12">
                                            <label for="word_topic">Topic</label>
                                            <select id="word_topic" required class="form-control gw-topic-select">
                                                <option value="">Select topic</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="word_level">Level</label>
                                            <select id="word_level" name="level_id" required class="form-control">
                                                <option value="">Select level</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 col-sm-12">
                                            <label for="word">Word</label>
                                            <input type="text" id="word" name="word" required maxlength="30" class="form-control" placeholder="e.g. Elephant">
                                            <small class="text-muted">2-30 letters. Spaces, hyphens and apostrophes are shown to the child, not guessed.</small>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="image">Picture</label>
                                            <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                                            <small class="text-muted">JPG, PNG, GIF or WEBP, up to 2 MB. Recommended: young children play from the picture.</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12 col-sm-12">
                                            <label for="hint">Hint</label>
                                            <input type="text" id="hint" name="hint" maxlength="255" class="form-control" placeholder="e.g. A big grey animal with a long trunk">
                                        </div>
                                    </div>
                                    <div id="result"></div>
                                    <button type="submit" id="submit_btn" class="btn btn-warning">Add Question</button>
                                </form>
                                <hr>
                                <h2>Questions <small>View / Update / Delete</small></h2>
                                <div class="row">
                                    <div class='col-md-4'>
                                        <select id='filter_topic' class='form-control gw-topic-select'>
                                            <option value="">All topics</option>
                                        </select>
                                    </div>
                                    <div class='col-md-4'>
                                        <select id='filter_level' class='form-control'>
                                            <option value="">All levels</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="toolbar">
                                    <button class="btn btn-danger btn-sm" id="delete_words" title="Delete selected questions"><em class='fa fa-trash'></em></button>
                                </div>
                                <table aria-describedby="questions" class='table-striped gw-table' id='word_list' data-toggle="table" data-url="get-list.php?table=junior_guess_word" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-toolbar="#toolbar" data-mobile-responsive="true" data-maintain-selected="true" data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            <th scope="col" data-field="state" data-checkbox="true"></th>
                                            <th scope="col" data-field="id" data-sortable="true">ID</th>
                                            <th scope="col" data-field="status">Status</th>
                                            <th scope="col" data-field="topic" data-sortable="true">Topic</th>
                                            <th scope="col" data-field="level_name" data-sortable="true">Level</th>
                                            <th scope="col" data-field="word" data-sortable="true">Word</th>
                                            <th scope="col" data-field="hint">Hint</th>
                                            <th scope="col" data-field="image">Picture</th>
                                            <th scope="col" data-field="operate" data-events="wordEvents">Operate</th>
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

        <div class="modal fade" id='editWordModal' tabindex="-1" role="dialog" aria-labelledby="editWordLabel">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="editWordLabel">Edit Question</h4>
                    </div>
                    <div class="modal-body">
                        <form id="edit_word_form" method="POST" action="db_operations.php" enctype="multipart/form-data">
                            <input type="hidden" name="update_junior_guess_word" value="1">
                            <input type="hidden" name="word_id" id="edit_word_id">
                            <div class="form-group">
                                <label for="edit_word_topic">Topic</label>
                                <select id="edit_word_topic" required class="form-control gw-topic-select">
                                    <option value="">Select topic</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_word_level">Level</label>
                                <select id="edit_word_level" name="level_id" required class="form-control">
                                    <option value="">Select level</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_word">Word</label>
                                <input type="text" name="word" id="edit_word" required maxlength="30" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_hint">Hint</label>
                                <input type="text" name="hint" id="edit_hint" maxlength="255" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_image">Picture <small>( Leave it blank for no change )</small></label>
                                <input type="file" name="image" id="edit_image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div id="edit_result"></div>
                            <button type="submit" id="edit_btn" class="btn btn-success">Update</button>
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
        GuessWord.loadTopics($('.gw-topic-select')).done(function(result) {
            $('#no_topics').toggle(!result.rows.length);
        });

        // Each Topic dropdown fills the Level dropdown next to it.
        $('#word_topic').on('change', function() {
            GuessWord.loadLevels($(this).val(), $('#word_level'));
        });
        $('#edit_word_topic').on('change', function() {
            GuessWord.loadLevels($(this).val(), $('#edit_word_level'));
        });
        $('#filter_topic').on('change', function() {
            GuessWord.loadLevels($(this).val(), $('#filter_level'));
            $('#word_list').bootstrapTable('refresh');
        });
        $('#filter_level').on('change', function() {
            $('#word_list').bootstrapTable('refresh');
        });

        function queryParams(p) {
            return {
                category: $('#filter_topic').val(),
                level: $('#filter_level').val(),
                limit: p.limit,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search
            };
        }

        $('#word_form').on('submit', function(e) {
            e.preventDefault();
            if ($(this).validate().form()) {
                GuessWord.submitForm(this, $('#submit_btn'), $('#result'), function() {
                    // Keep topic and level so a batch of words is quick to enter.
                    $('#word, #hint, #image').val('');
                    $('#word').focus();
                });
            }
        });

        $('#edit_word_form').on('submit', function(e) {
            e.preventDefault();
            if ($(this).validate().form()) {
                GuessWord.submitForm(this, $('#edit_btn'), $('#edit_result'), function() {
                    setTimeout(function() {
                        $('#editWordModal').modal('hide');
                    }, 1200);
                });
            }
        });

        window.wordEvents = {
            'click .edit-word': function(e, value, row) {
                $('#edit_word_id').val(row.id);
                $('#edit_word_topic').val(row.category);
                GuessWord.loadLevels(row.category, $('#edit_word_level'), row.level_id);
                $('#edit_word').val(row.word_text);
                $('#edit_hint').val(row.hint_text);
                $('#edit_image').val('');
            },
            'click .delete-word': function(e, value, row) {
                if (confirm('Delete the question "' + row.word_text + '"?')) {
                    GuessWord.remove('delete_junior_guess_word', String(row.id));
                }
            }
        };

        GuessWord.bulkDelete($('#delete_words'), $('#word_list'), 'delete_junior_guess_word', 'Delete the selected questions?');
    </script>
</body>

</html>
