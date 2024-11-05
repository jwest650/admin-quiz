<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
}
// $exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : '';
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
                                <h2>Import Exam Questions</h2>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="x_panel">
    
    <div class="x_content">
        <form method="post" enctype="multipart/form-data" id="importForm" class="form-horizontal form-label-left">
            <input type="hidden" name="import_exam_question" value="1">
        <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Select Exam Module</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="exam_id" class="form-control" required>
                        <option value="">Select Module</option>
                        <?php
                                                    $db->sql("SET NAMES 'utf8'");
                                                    $sql = "SELECT id, title FROM exam_module ";
                                                    $db->sql($sql);
                                                    $res = $db->getResult();
                                                    ?>
                                                   
                                                    
                                                        <?php foreach ($res as $row) { ?>
                                                            <option value='<?= $row['id'] ?>'><?= $row['title'] ?></option>
                                                        <?php } ?>
                                        
                    </select>
                </div>
            </div>    
        <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Select CSV File</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    <small class="text-muted">Please upload a CSV file with questions data</small>
                </div>
            </div>
            <div class="ln_solid"></div>
            <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <button id="import_btn" type="submit" name="submit" class="btn btn-success">Import Questions</button>
                </div>
            </div>
        </form>
        <div class="row">
        <div class="col-md-offset-3 col-md-8" style="display:none;" id="result"></div>
        </div>

<div>
    </div>
</div>

<h3>How to convert CSV in to Unicode (For Non English)</h3>


<ol class="text-left space-y-3">
<li>Fill the data in excel sheet which formate we given</li>
<li>SAVE AS this file Unicode Text (*.txt)</li>
<li>Open .txt file in Notepad.</li>
<li>Replace Tab space( ) with ( , ) comma.</li>
<li>Save as this file with .txt extension and change the encoding : UTF-8.</li>
<li>Change the file extension .txt to .csv.</li>
<li>Now this file use import question.</li>
</ol>
</div>

            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
<script>
    $(document).ready(function() {
        $('#importForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: 'db_operations.php',
                type: 'POST',
                data: formData,
                beforeSend: function() {
                        $('#import_btn').html('Please wait..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                success: function(response) {
                    const data = JSON.parse(response);
                    $('#import_btn').html('Import Questions');
                    $('#importForm')[0].reset();
                    $('#result').html(data['message']);
                    $('#result').show().delay(4000).fadeOut();

                }
            });
        });
    });
</script>
</body>
</html>