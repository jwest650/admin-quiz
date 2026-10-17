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
    <title>Import Guess the Word Questions | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
    <?php include 'include-css.php'; ?>
    <style>
        .gw-format { background: #f7f7f7; border: 1px solid #e3e3e3; border-radius: 4px; padding: 10px 14px; font-family: monospace; white-space: pre; overflow-x: auto; }
        .gw-report { max-height: 340px; overflow-y: auto; }
        .gw-progress { margin: 12px 0 0; display: none; }
    </style>
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
                                <h2>Guess the Word: Import <small>Choose where the words and pictures go</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="alert alert-warning" id="no_topics" style="display:none;">Add a topic and a level on the <a href="junior-guess-word.php"><strong>Topics &amp; Levels</strong></a> page first.</div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <label for="import_topic">Topic</label>
                                        <select id="import_topic" class="form-control gw-topic-select">
                                            <option value="">Select topic</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <label for="import_level">Level</label>
                                        <select id="import_level" class="form-control">
                                            <option value="">Select level</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="x_panel">
                            <div class="x_title">
                                <h2>1. Import Questions <small>CSV</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <form id="import_form" class="form-horizontal form-label-left" enctype="multipart/form-data">
                                    <input type="hidden" name="import_guess_word_csv" value="1">
                                    <div class="form-group row">
                                        <div class="col-md-6 col-sm-12">
                                            <label for="import_file">CSV file</label>
                                            <input type="file" id="import_file" name="questions" required class="form-control" accept=".csv,text/csv">
                                        </div>
                                    </div>
                                    <button type="submit" id="import_btn" class="btn btn-success">Import Questions</button>
                                    <button type="button" id="sample_btn" class="btn btn-warning"><em class="fas fa-download"></em> Download Sample File</button>
                                </form>
                                <div id="import_result" style="margin-top:16px;"></div>

                                <hr>
                                <h4>How to fill the CSV</h4>
                                <ol>
                                    <li>Every word in the file goes into the <strong>topic</strong> and <strong>level</strong> chosen above.</li>
                                    <li>Use two columns: <strong>word</strong> and <strong>hint</strong>. The hint may be left blank. A heading row is optional.</li>
                                    <li>Words must be 2-30 letters. Spaces, hyphens and apostrophes are allowed between letters, e.g. <em>Polar bear</em>, <em>T-shirt</em>.</li>
                                    <li>In Excel or Google Sheets, save or download the sheet as <strong>CSV</strong>. Non-English letters work with any CSV option.</li>
                                    <li>Rows with a problem, and words the level already has, are skipped. The rest are imported, and the skipped rows are listed with their line numbers.</li>
                                    <li><strong>Pictures don't go in the CSV.</strong> There is no picture column, and anything after the hint column is ignored. After importing the words, add their pictures in <strong>2. Import Pictures</strong> below, with each picture file named after its word.</li>
                                </ol>
                                <p>Example:</p>
                                <div class="gw-format">word,hint
Elephant,A big grey animal with a long trunk
Giraffe,The animal with the longest neck
Polar bear,Lives where it is very cold</div>
                                <p style="margin-top:8px;">Pictures for this example, uploaded in step 2: <code>elephant.png</code>, <code>giraffe.jpg</code>, <code>polar-bear.png</code></p>
                            </div>
                        </div>

                        <div class="x_panel">
                            <div class="x_title">
                                <h2>2. Import Pictures <small>Named after the words</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <p>Import the words first (step 1, or add them on the <a href="junior-guess-word-questions.php">Questions</a> page). Then name each picture after its word and select them all at once. Each picture is added to the word with the same name in the topic and level chosen above.</p>
                                <ul>
                                    <li><code>elephant.png</code> &rarr; Elephant</li>
                                    <li><code>polar bear.jpg</code>, <code>polar-bear.jpg</code> or <code>polar_bear.jpg</code> &rarr; Polar bear</li>
                                    <li>Capital letters don't matter. JPG, PNG, GIF or WEBP, up to 2 MB each.</li>
                                </ul>
                                <form id="picture_form" class="form-horizontal form-label-left">
                                    <div class="form-group row">
                                        <div class="col-md-6 col-sm-12">
                                            <label for="picture_files">Pictures</label>
                                            <input type="file" id="picture_files" multiple required class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                                        </div>
                                    </div>
                                    <div class="checkbox">
                                        <label><input type="checkbox" id="picture_replace"> Replace pictures that words already have</label>
                                    </div>
                                    <button type="submit" id="picture_btn" class="btn btn-success">Import Pictures</button>
                                </form>
                                <div class="progress gw-progress" id="picture_progress">
                                    <div class="progress-bar progress-bar-success" role="progressbar" style="width:0%"></div>
                                </div>
                                <div id="picture_result" style="margin-top:16px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page content -->
        <!-- footer content -->
        <?php include 'footer.php'; ?>
        <!-- /footer content -->
    </div>

    <script src="js/guess-word-admin.js"></script>
    <script>
        GuessWord.loadTopics($('.gw-topic-select')).done(function(result) {
            $('#no_topics').toggle(!result.rows.length);
        });
        $('#import_topic').on('change', function() {
            GuessWord.loadLevels($(this).val(), $('#import_level'));
        });

        // Both imports need a level; returns its id, or warns and returns ''.
        function chosenLevel() {
            var level = $('#import_level').val();
            if (!level) {
                alert('Choose a topic and a level first.');
                $(!$('#import_topic').val() ? '#import_topic' : '#import_level').focus();
            }
            return level;
        }

        function alertBox(kind, text) {
            return $('<div>').addClass('alert alert-' + kind).text(text);
        }

        // A table of [cells...] rows under the given headings.
        function reportTable(headings, rows) {
            var $table = $('<table class="table table-striped table-condensed"><thead><tr></tr></thead><tbody></tbody></table>');
            $.each(headings, function(i, heading) {
                $('<th>').text(heading).appendTo($table.find('thead tr'));
            });
            $.each(rows, function(i, cells) {
                var $tr = $('<tr>').appendTo($table.find('tbody'));
                $.each(cells, function(j, cell) {
                    $('<td>').text(cell).appendTo($tr);
                });
            });
            return $('<div class="gw-report">').append($table);
        }

        /* ---------- CSV ---------- */

        // The sample is built here: CSV files in library/ are blocked from download.
        $('#sample_btn').on('click', function() {
            var csv = $('.gw-format').text() + '\n';
            var link = document.createElement('a');
            link.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }));
            link.download = 'guess-the-word-sample.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
        });

        $('#import_form').on('submit', function(e) {
            e.preventDefault();
            var level = chosenLevel();
            if (!level || !$(this).validate().form()) {
                return;
            }
            var data = new FormData(this);
            data.append('level_id', level);
            var $button = $('#import_btn');
            var $box = $('#import_result');
            $.ajax({
                type: 'POST',
                url: 'db_operations.php',
                data: data,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $button.prop('disabled', true).html('Importing..');
                    $box.empty();
                },
                success: function(result) {
                    $box.append(alertBox(result.error ? 'danger' : (result.skipped.length ? 'warning' : 'success'), result.message));
                    if (result.skipped.length) {
                        $box.append(reportTable(['Line', 'Word', 'Why it was skipped'], $.map(result.skipped, function(row) {
                            return [[row.line, row.word, row.reason]];
                        })));
                    }
                    if (result.added > 0) {
                        $('#import_file').val('');
                    }
                },
                error: function() {
                    $box.append(alertBox('danger', 'The import failed. Check the file and try again.'));
                },
                complete: function() {
                    $button.prop('disabled', false).html('Import Questions');
                }
            });
        });

        /* ---------- Pictures ---------- */

        var PICTURE_STATUS = {
            attached: 'Added',
            replaced: 'Replaced',
            has_picture: 'Skipped: the word already has a picture',
            no_match: 'Skipped: no word in this level matches the file name',
            bad_image: 'Skipped: not a usable picture'
        };

        // One request per picture keeps each upload under PHP's size and file-count limits.
        function sendPicture(file, level, replace) {
            var data = new FormData();
            data.append('attach_guess_word_picture', '1');
            data.append('level_id', level);
            data.append('replace', replace ? '1' : '0');
            data.append('image', file);
            return $.ajax({
                type: 'POST',
                url: 'db_operations.php',
                data: data,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false
            }).then(function(result) {
                return result;
            }, function() {
                return $.Deferred().resolve({ status: 'bad_image', word: '', message: 'Upload failed.' }).promise();
            });
        }

        $('#picture_form').on('submit', function(e) {
            e.preventDefault();
            var level = chosenLevel();
            var files = $('#picture_files')[0].files;
            if (!level) {
                return;
            }
            if (!files.length) {
                alert('Choose the pictures to import.');
                return;
            }
            var replace = $('#picture_replace').is(':checked');
            var $button = $('#picture_btn').prop('disabled', true).html('Importing..');
            var $bar = $('#picture_progress').show().find('.progress-bar').css('width', '0%');
            var $box = $('#picture_result').empty();
            var rows = [];
            var added = 0;
            var index = 0;

            function next() {
                if (index >= files.length) {
                    finish();
                    return;
                }
                var file = files[index];
                sendPicture(file, level, replace).done(function(result) {
                    if (result.status === 'attached' || result.status === 'replaced') {
                        added++;
                    }
                    rows.push([file.name, result.word || '-', PICTURE_STATUS[result.status] || result.message]);
                    index++;
                    $bar.css('width', Math.round(index / files.length * 100) + '%');
                    next();
                });
            }

            function finish() {
                var skipped = files.length - added;
                var text = added + (added === 1 ? ' picture' : ' pictures') + ' saved.' +
                    (skipped ? ' ' + skipped + (skipped === 1 ? ' file was' : ' files were') + ' skipped.' : '');
                $box.append(alertBox(added === 0 ? 'danger' : (skipped ? 'warning' : 'success'), text));
                $box.append(reportTable(['File', 'Word', 'Result'], rows));
                $button.prop('disabled', false).html('Import Pictures');
                $('#picture_files').val('');
                setTimeout(function() {
                    $('#picture_progress').fadeOut();
                }, 800);
            }

            next();
        });
    </script>
</body>

</html>
