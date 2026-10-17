/*
 * Shared helpers for the Guess the Word admin pages
 * (junior-guess-word.php and junior-guess-word-questions.php).
 */
var GuessWord = (function ($) {
    'use strict';

    function option(value, label) {
        return $('<option>').val(value).text(label);
    }

    // Fills each <select> with the topics, keeping its first ("Select…") option
    // and its current choice.
    function loadTopics($selects) {
        return $.getJSON('get-list.php', { table: 'junior_guess_word_topic', limit: 500, sort: 'row_order', order: 'asc' })
            .done(function (result) {
                $selects.each(function () {
                    var $select = $(this);
                    var current = $select.val();
                    $select.find('option:not(:first)').remove();
                    $.each(result.rows, function (i, row) {
                        $select.append(option(row.id, row.name_text));
                    });
                    $select.val(current);
                });
            });
    }

    // Fills $select with the levels of one topic, then selects `selected`.
    function loadLevels(topicId, $select, selected) {
        $select.find('option:not(:first)').remove();
        if (!topicId) {
            return $.Deferred().resolve().promise();
        }
        return $.getJSON('get-list.php', { table: 'junior_guess_word_level', category: topicId, limit: 500, sort: 'row_order', order: 'asc' })
            .done(function (result) {
                $.each(result.rows, function (i, row) {
                    $select.append(option(row.id, row.name_text));
                });
                if (selected) {
                    $select.val(String(selected));
                }
            });
    }

    // Posts a form (files included), shows the HTML message it returns and calls
    // after() when it saved.
    function submitForm(form, $button, $output, after) {
        var label = $button.html();
        $.ajax({
            type: 'POST',
            url: 'db_operations.php',
            data: new FormData(form),
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $button.prop('disabled', true).html('Please wait..');
            },
            success: function (result) {
                $output.html(result).show().delay(4000).fadeOut();
                if (result.indexOf('alert-success') !== -1 && after) {
                    after();
                }
            },
            error: function () {
                $output.html('<label class="alert alert-danger">Could not reach the server. Try again.</label>').show();
            },
            complete: function () {
                $button.prop('disabled', false).html(label);
                refreshTables();
            }
        });
    }

    function refreshTables() {
        $('.gw-table').bootstrapTable('refresh');
    }

    // Deletes ids with a db_operations.php action, e.g. delete_guess_word_topic.
    function remove(action, ids, done) {
        var data = { ids: ids };
        data[action] = 1;
        $.post('db_operations.php', data, function (result) {
            var ok = $.trim(result) === '1';
            if (!ok) {
                alert('Could not delete. Try again!');
            }
            refreshTables();
            if (done) {
                done(ok);
            }
        });
    }

    // Wires a toolbar button that deletes the rows ticked in $table.
    function bulkDelete($button, $table, action, warning, done) {
        $button.on('click', function () {
            var ids = $.map($table.bootstrapTable('getAllSelections'), function (row) {
                return row.id;
            }).join(',');
            if (ids === '') {
                alert('Tick the rows you want to delete first.');
                return;
            }
            if (confirm(warning)) {
                remove(action, ids, done);
            }
        });
    }

    // Show / hide buttons in every Guess the Word table.
    $(document).on('click', '.toggle-status', function () {
        var $link = $(this);
        $.post('db_operations.php', {
            set_guess_word_status: 1,
            kind: $link.data('kind'),
            id: $link.data('id'),
            status: String($link.data('status'))
        }, refreshTables);
    });

    return {
        loadTopics: loadTopics,
        loadLevels: loadLevels,
        submitForm: submitForm,
        refreshTables: refreshTables,
        remove: remove,
        bulkDelete: bulkDelete
    };
})(jQuery);
