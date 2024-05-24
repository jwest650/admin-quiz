<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
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
        <title>Notification Settings | FCM Server Key | <?= ucwords($_SESSION['company_name']) ?> Admin Panel  </title>
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
                                    <h2>Notification Settings <small>Update FCM Server Key here</small></h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <br />
                                    <?php
                                    $db->sql("SET NAMES 'utf8'");
                                    $sql = "select * from `tbl_fcm_key` where id=1";
                                    $db->sql($sql);
                                    $res = $db->getResult();
                                    if (!empty($res)) {
                                        $data = $res[0];
                                    }
                                    ?>
                                    <form id="register_form"  method="POST" action ="db_operations.php"data-parsley-validate class="form-horizontal form-label-left">
                                        <input type="hidden" id="update_fcm_server_key" name="update_fcm_server_key" required value='1'/>
                                        <input type="hidden" id="update_fcm_server_key_id" name="update_fcm_server_key_id" value='<?php
                                        if (!empty($data['id'])) {
                                            echo $data['id'];
                                        }
                                        ?>'/>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fcm_key">FCM Server Key</label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <textarea id="fcm_key"  name="fcm_key" required class="form-control col-md-7 col-xs-12" rows=5><?php
                                                    if (!empty($data['fcm_key'])) {
                                                        echo $data['fcm_key'];
                                                    }
                                                    ?></textarea>
                                            </div>
                                        </div>
                                        <div class="ln_solid"></div>
                                        <div class="form-group">
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                                <button type="submit" id="submit_btn" class="btn btn-success">Update FCM Server Key</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="row">
                                    <div  class="col-md-offset-3 col-md-4" style ="display:none;" id="result">
                                    </div>
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

        <!-- jQuery -->
        <script>
            $('#register_form').validate({
                rules: {
                    fcm_key: "required",
                }
            });
        </script>
        <script>
            $('#register_form').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                if ($("#register_form").validate().form()) {
                    $.ajax({
                        type: 'POST',
                        url: $(this).attr('action'),
                        data: formData,
                        beforeSend: function () {
                            $('#submit_btn').html('Please wait..');
                        },
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (result) {
                            $('#result').html(result);
                            $('#result').show();
                            $('#submit_btn').html('Submit');
                            location.reload();
                        }
                    });
                }
            });
        </script>
    </body>
</html>