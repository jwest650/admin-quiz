<?php 
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:home.php");
    return false;
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'include-css.php'; ?>

</head>
<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include 'sidebar.php'; ?>
            <!-- page content -->
            <div class="right_col" role="main">
                <!-- top tiles -->
                <br />
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Create Exam Module</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class='row'>
                                    <div class='col-md-12'>
                                        <form id="quiz_form" method="POST" action="db_operations.php" class="form-horizontal form-label-left" enctype="multipart/form-data">
                                            <input type="hidden" id="add_exam_module" name="add_exam_module" required="" value="1" aria-required="true">
                                           
                                            <div class="form-group row">
                                            <?php
                                            $sql = "SELECT * FROM `languages` ORDER BY id DESC";
                                            $db->sql($sql);
                                            $languages = $db->getResult();
                                            ?>
                                                <div class="col-md-4 col-sm-12">
                                                    <label for="language">Language</label>
                                                    <div>
                                                    <select class="form-control" name="language" id="language">
                                                    <?php foreach ($languages as $language) { ?>
                                                        <option value='<?= $language['id'] ?>'><?= $language['language'] ?></option>
                                                    <?php } ?>
                                                    </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-12">
                                                    <label for="title">Title</label>
                                                    <input type='text' class="form-control" name="title" id="title" required>
                                                </div>
                                                <div class="col-md-4 col-sm-12">
                                                    <label for="date"> Date</label>
                                                    <input type="date" id="date" name="date" required class="form-control">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row">
                                                <div class="col-md-6 col-sm-12">
                                                    <label for="key">Exam Key</label>
                                                    <input type="number" id="key" name="key" required class="form-control"  min='0'>
                                                </div>
                                           

                                            
                                                <div class="col-md-6 col-sm-12">
                                                    <label for="duration">Duration</label>
                                                    <input type="number" name="duration" required class="form-control"  min='0'>

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <button type="submit" id="submit_btn" class="btn btn-warning">Add New</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-12 col-lg-12'>
                                        <div id="toolbar">
                                            <div class="col-md-3">
                                                <button class="btn btn-danger btn-sm" id="delete_multiple_contests" title="Delete Selected Contests"><em class='fa fa-trash'></em></button>
                                            </div>
                                        </div>
                                        <table aria-describedby="mydesc" class='table-striped' id='exam_list' data-toggle="table" data-url="get-list.php?table=exam_module" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="#toolbar" data-maintain-selected="true" data-show-export="false" data-export-types='["txt","excel"]' data-export-options='{
                                                   "fileName": "contest-list-<?= date('d-m-y') ?>",
                                                   "ignoreColumn": ["state"]	
                                                   }' >
                                            <thead>
                                                <tr>
                                                    <th scope="col" data-field="state" data-checkbox="true"></th>
                                                    <th scope="col" data-field="id" data-sortable="true">ID</th>
                                                    <th scope="col" data-field="status" data-sortable="false">Status</th>
                                                    <th scope="col" data-field="title" data-sortable="true">Title</th>
                                                    <th scope="col" data-field="date" data-sortable="true"> Date</th>
                                                    <th scope="col" data-field="exam_key" data-sortable="false">Key</th>
                                                    <th scope="col" data-field="duration" data-sortable="false" >Duration</th>
                                                    <th scope="col" data-field="total_question" data-sortable="false" >Total</th>
                                                    


                                                   
                                                    <th scope="col" data-field="operate" data-sortable="false" data-events="actionEvents">Operate</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page content -->
        <div class="modal fade" id='editStatusModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Update Status</h4>
                    </div>
                    <div class="modal-body">
                        <form id="update_status_form" method="POST" action="db_operations.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="update_id" id="update_id" value='' />
                            <input type='hidden' name="update_exam_status" id="update_exam_status" value='1' />
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div id="status" class="btn-group">
                                        <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0"> Deactive
                                        </label>
                                        <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" id="update_btn1" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-offset-3 col-md-8" style="display:none;" id="result1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id='editCategoryModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Edit Exam Details</h4>
                    </div>
                    <div class="modal-body">
                        <form id="update_form" method="POST" action="db_operations.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="update_exam_module" id="update_exam_module" value='1' />
                            <input type='hidden' name="exam_id" id="exam_id" value='' />
                           
                            <div class="form-group">
                                <label for="update_title"> Title</label>
                                <input type="text" name="title" id="update_title" placeholder="Exam Title" class='form-control' required>
                            </div>
                            <div class="form-group">
                                <label for="update_date"> Date</label>
                                <input type="date" id="update_date" name="date" required class="form-control">
                            </div>
                         
                            <div class="form-group">
                                <label for="key">Exam Key</small></label>
                                <input type="number" class="form-control" name="key" id="update_key" aria-required="true">
                            </div>
                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <input type="number" id="update_duration" name="duration" required class="form-control" placeholder="Duration in hours" min='1'>
                            </div>

                            <input type="hidden" id="id" name="id">
                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-offset-3 col-md-8" style="display:none;" id="update_result"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer content -->
        <?php include 'footer.php'; ?>
        <!-- /footer content -->
    </div>
    </div>
    <!-- jQuery -->
    <script>
        $('#quiz_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            if ($("#quiz_form").validate().form()) {
                if (confirm('Are you sure?Want to create Exam Module')) {
                    $.ajax({
                        type: 'POST',
                        url: $(this).attr('action'),
                        data: formData,
                        beforeSend: function() {
                            $('#submit_btn').html('Please wait..');
                        },
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(result) {
                            $('#result').html(result);
                            $('#result').show().delay(4000).fadeOut();
                            $('#submit_btn').html('Submit');
                            $('#quiz_form')[0].reset();
                            $('#exam_list').bootstrapTable('refresh');
                        }
                    });
                }
            }
        });
    </script>
 <script>
        window.actionEvents = {
            'click .edit-quiz': function(e, value, row, index) {
                // alert('You click remove icon, row: ' + JSON.stringify(row));
                $('#exam_id').val(row.id);
                $('#update_title').val(row.title);
                $('#update_date').val(row.date);
                $('#update_key').val(row.exam_key);
                $('#update_duration').val(row.duration);
               

            },
            'click .edit-data': function(e, value, row, index) {
                $('#update_id').val(row.id);
                // alert('You click remove icon, row: ' + JSON.stringify(row));
                $("input[name=status][value=1]").prop('checked', true);
                if ($(row.status).text() == 'Deactive')
                    $("input[name=status][value=0]").prop('checked', true);
            }
        };
    </script>

<script>
        $('#update_status_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            if ($("#update_status_form").validate().form()) {
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    beforeSend: function() {
                        $('#update_btn1').html('Please wait..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        $('#result1').html(result);
                        $('#result1').show().delay(3000).fadeOut();
                        $('#update_btn1').html('Update');
                        $('#exam_list').bootstrapTable('refresh');
                        setTimeout(function() {
                            $('#editStatusModal').modal('hide');
                        }, 3000);
                    }
                });
            }
        });
    </script>

<script>
        $('#update_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            if ($("#update_form").validate().form()) {
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    beforeSend: function() {
                        $('#update_btn').html('Please wait..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        $('#update_result').html(result);
                        $('#update_result').show().delay(4000).fadeOut();
                        $('#update_btn').html('Update');
                        
                        $('#exam_list').bootstrapTable('refresh');
                        setTimeout(function() {
                            $('#editCategoryModal').modal('hide');
                        }, 3000);
                    }
                });
            }
        });
</script>


<script>
        $(document).on('click', '.delete-quiz', function() {
            if (confirm('Are you sure? Want to delete Exam Module? All related questions & leaderboard details will also be deleted')) {
                id = $(this).data("id");
              
                $.ajax({
                    url: 'db_operations.php',
                    type: "get",
                    data: 'id=' + id  + '&delete_exam_module=1',
                    success: function(result) {
                        if (result == 1) {
                            $('#exam_list').bootstrapTable('refresh');
                        } else
                            alert('Error! Exam Module could not be deleted');
                    }
                });
            }
        });
    </script>
</body>
</html>


