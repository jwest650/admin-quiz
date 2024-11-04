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
                                                <button class="btn btn-danger btn-sm" id="delete_multiple_contests" title="Delete Selected Contests"><em class='fa fa-trash'></em></button>
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


    <?php include 'footer.php'; ?>

</body>
</html>