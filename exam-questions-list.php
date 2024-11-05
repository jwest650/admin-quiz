<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
}
$exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
                                <h2>Manage Exam Questions</h2>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='row'>
                                    <div class='col-md-12 col-lg-12'>
                                        <div id="toolbar">
                                            <div class="col-md-3">
                                                <button class="btn btn-danger btn-sm" id="delete_multiple_questions" title="Delete Selected Contests"><em class='fa fa-trash'></em></button>
                                            </div>
                                        </div>
                                        <table aria-describedby="mydesc" class='table-striped' id='exam_list' data-toggle="table" data-url="get-list.php?table=exam-question-list&exam_id=<?= $exam_id ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="#toolbar" data-maintain-selected="true" data-show-export="false" data-export-types='["txt","excel"]' data-export-options='{
                                                   "fileName": "contest-list-<?= date('d-m-y') ?>",
                                                   "ignoreColumn": ["state"]	
                                                   }' >
                                            <thead>
                                                <tr>
                                                    <th scope="col" data-field="state" data-checkbox="true"></th>
                                                    <th scope="col" data-field="id" data-sortable="true">ID</th>
                                                    <th scope="col" data-field="image" data-sortable="false">Image</th>
                                                    <th scope="col" data-field="question" data-sortable="true">Question</th>
                                                    <th scope="col" data-field="optiona" data-sortable="true"> Option A</th>
                                                    <th scope="col" data-field="optionb" data-sortable="true"> Option B</th>
                                                    <th scope="col" data-field="optionc" data-sortable="true"> Option C</th>
                                                    <th scope="col" data-field="optiond" data-sortable="true"> Option D</th>
                                                    <th scope="col" data-field="marks" data-sortable="true"> Marks</th>
                                                    
                                                    


                                                   
                                                    <th scope="col" data-field="operate" data-sortable="false" data-events="actionEvents">Operate</th>
                                                </tr>
                                            </thead>
                                        </table>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
    <div class="modal fade" id='editCategoryModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Edit Exam Details</h4>
                    </div>
                    <div class="modal-body ">
                        <form id="update_form" method="POST" action="db_operations.php" data-parsley-validate class="form-horizontal  form-label-left">
                            <input type='hidden' name="update_exam_question" id="update_exam_question" value='1' />
                            <input type='hidden' name="question_id" id="question_id" value='' />
                           
                           
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="exam_id">Exam Modules</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <?php
                                                    $db->sql("SET NAMES 'utf8'");
                                                    $sql = "SELECT id, title FROM exam_module ";
                                                    $db->sql($sql);
                                                    $res = $db->getResult();
                                                    ?>
                                                    <select name='exam_id' id='exam_id' class='form-control'>
                                                        <option value=''>Select any exam module</option>
                                                        <?php foreach ($res as $row) { ?>
                                                            <option value='<?= $row['id'] ?>'><?= $row['title'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="question">Question</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <textarea id="question" name="question" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="image">Image <small>(Optional)</small></label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <input type='file' class="form-control" name="image" id="image">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="answer type">Question Type</label>
                                                <div class="col-md-8 col-sm-6 col-xs-12">                                                     
                                                    <div id="status" class="btn-group">
                                                        <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                            <input type="radio" name="question_type" value="1" checked=""> Options 
                                                        </label>
                                                        <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                            <input type="radio" name="question_type" value="2"> True / False
                                                        </label>                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="a">Options</label>
                                                <div class="col-md-8 col-sm-6 col-xs-12"></div>
                                            </div>
                                            <div class="form-group">
                                                <label for="a" class="control-label col-md-1 col-sm-3 col-xs-12">A</label>
                                                <div class="col-md-4 col-sm-6 col-xs-12">
                                                    <input id="a" class="form-control" type="text" name="a">
                                                </div>
                                                <label for="b" class="control-label col-md-1 col-sm-3 col-xs-12">B</label>
                                                <div class="col-md-5 col-sm-6 col-xs-12">
                                                    <input id="b" class="form-control" type="text" name="b">
                                                </div>
                                            </div>
                                            <div id="tf">
                                                <div class="form-group" >
                                                    <label for="c" class="control-label col-md-1 col-sm-3 col-xs-12">C</label>
                                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                                        <input id="c" class="form-control" type="text" name="c">
                                                    </div>
                                                    <label for="d" class="control-label col-md-1 col-sm-3 col-xs-12">D</label>
                                                    <div class="col-md-5 col-sm-6 col-xs-12">
                                                        <input id="d" class="form-control" type="text" name="d">
                                                    </div>
                                                </div>
                                                <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                                    <div class="form-group">
                                                        <label for="e" class="control-label col-md-1 col-sm-3 col-xs-12">E </label>
                                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                                            <input id="e" class="form-control" type="text" name="e">
                                                        </div>
                                                        <label for="d" class="control-label col-md-1 col-sm-3 col-xs-12"></label>
                                                        <div class="col-md-5 col-sm-6 col-xs-12">
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="answer">Answer</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <select name='answer' id='answer' class='form-control'>
                                                        <option value=''>Select Right Answer</option>
                                                        <option value='a'>A</option>
                                                        <option value='b'>B</option>
                                                        <option class='ntf' value='c'>C</option>
                                                        <option class='ntf' value='d'>D</option>
                                                        <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                                            <option class='ntf' value='e'>E</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="marks">Marks</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <input type="number" id="marks" name="marks" class="form-control "></input>
                                                </div>
                                            </div>

                                            <div class="ln_solid"></div>
                                            <div class="form-group">
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <button type="submit" id="submit_btn" class="btn btn-success">Update Now</button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div  class="col-md-offset-3 col-md-4" style ="display:none;" id="result">
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


    <?php include 'footer.php'; ?>
    <script>
      
      $('input[name="question_type"]').on("click", function (e) {
          var question_type = $(this).val();

          if (question_type == "2") {
              $('#tf').hide('fast');
              $('#a').val("<?php echo $config['true_value'] ?>");
              $('#b').val("<?php echo $config['false_value'] ?>");
              $('.ntf').hide('fast');
              $('#answer').val('');
          } else {
              $('#tf').show('fast');
              $('.ntf').show('fast');
          }
      });
  </script>
    <script>
        window.actionEvents = {
            'click .edit-quiz': function(e, value, row, index) {
                // alert('You click remove icon, row: ' + JSON.stringify(row));
                $('#question_id').val(row.id);
                $('#exam_id').val(row.exam_id);
                $('#question').val(row.question);
                $('#a').val(row.optiona);
                $('#b').val(row.optionb);
                $('#c').val(row.optionc);
                $('#d').val(row.optiond);
                $('#e').val(row.optione);
                $('#answer').val(row.answer);
                $('#marks').val(row.marks);
                $('#image').val(row.image);
                $('#question_type').val(row.question_type);



               
               

            }, 'click .delete-quiz': function(e, value, row, index) {
                if (confirm('Are you sure you want to delete this question?')) {
                    $.ajax({
                        url: 'db_operations.php',
                        type: "get",
                data: 'id=' + row.id + '&delete_exam_question=1',
                success: function(result) {
                    if (result == 1) {
                        $('#exam_list').bootstrapTable('refresh');
                    } else {
                        alert('Error! Exam Question could not be deleted');
                    }
                }
            });
                }
            }
            
        };
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

        $("#delete_multiple_questions").on("click", function(e){
            var selected = $('#exam_list').bootstrapTable('getSelections');
    var selected_ids = [];
    if (selected.length > 0) {
        if (confirm('Are you sure you want to delete selected questions?')) {
            $.each(selected, function(i, e) {
                selected_ids.push(e.id);
            });
            $.ajax({
                type: 'POST',
                url: 'db_operations.php',
                data: {
                    delete_multiple_questions: 1,
                    question_ids: selected_ids
                },
                success: function(result) {
                    if (result == 1) {
                        $('#exam_list').bootstrapTable('refresh');
                    } else {
                        alert('Error! Questions could not be deleted');
                    }
                }
            });
        }
    } else {
        alert('Please select some questions to delete!');
    }
        })


    </script>


</body>
</html>