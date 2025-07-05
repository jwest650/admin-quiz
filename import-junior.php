<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
  header("location:index.php");
  return false;
  exit();
}
$type = 1;
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
                <h2>Import Junior Questions</h2>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="x_panel">

          <div class="x_content">
            <form method="post" enctype="multipart/form-data" id="importForm" class="">
              <input type="hidden" name="import_junior_csv" value="1">
              <section class="">

                <div class="form-group">
                  <label class="control-label" for="category">Category</label>
                  <div class="">
                    <?php
                    $sql = "SELECT id, category_name FROM junior_category WHERE type=" . $type . " ORDER BY id DESC";
                    $db->sql($sql);
                    $categories = $db->getResult();
                    ?>
                    <select name='category' id='category' class='form-control' required>
                      <option value=''>Select Main Category</option>
                      <?php foreach ($categories as $row) { ?>
                        <option value='<?= $row['id'] ?>'><?= $row['category_name'] ?></option>
                      <?php } ?>
                    </select>
                  </div>

                </div>


                <div class="form-group ">
                  <label class="control-label " for="subcategory">Sub Category</label>
                  <div class="">
                    <select name='subcategory' id='subcategory' class='form-control'>
                      <option value=''>Select Sub Category</option>
                    </select>
                  </div>
                </div>
              </section>


              <div class="form-group ">
                <label class="control-label " for="subcategory">question type</label>
                <div class="">
                  <select name='question_type' id='question_type' class='form-control'>
                    <option value=''>Select Question Type</option>
                    <option value='1'>multiple choice</option>
                    <option value='2'>true / false</option>
                  </select>
                </div>
              </div>

              </section>

          </div>
          <div class="form-group">
            <label class="control-label ">Select CSV File</label>
            <div class="">
              <input type="file" name="questions" class="form-control" accept=".csv" required>
              <small class="text-muted">Please upload a CSV file with questions data</small>
            </div>
          </div>
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="">
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
          <li>Do not rearrange the column order. eg.(question,optiona,optionb,optionc,optiond,optione,answer,level,image,language_id</li>
          <li>Example data format:</li>
          What is the capital of Ghana?,Accra,Kumasi,Tamale,Takoradi,Cape Coast,a,1,https://example.com/image1.jpg,1
          Which planet is known as the Red Planet?,Earth,Venus,Mars,Jupiter,Saturn,c,2,https://example.com/image2.jpg,1
          )</li>
        </ol>
      </div>

    </div>
  </div>
  </div>
  <?php include 'footer.php'; ?>
  <script>
    $('#category').on('change', function(e) {
      var category_id = $('#category').val();
      $.ajax({
        type: 'POST',
        url: "db_operations.php",
        data: 'get_junior_subcategories_of_category=1&category_id=' + category_id,
        beforeSend: function() {
          $('#subcategory').html('Please wait..');
        },
        success: function(result) {
          $('#subcategory').html(result);
        }
      });
    });


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
            $('#import_btn').html('Import Questions');
            $('#importForm')[0].reset();
            console.log(response);
            $('#result').html(data['message']);

          },
          error: function(xhr, status, error) {
            $('#import_btn').html('Import Questions');
            console.error(xhr.responseText);
            $('#result').html('<div class="alert alert-danger">Error: ' + xhr.responseText + '</div>');
          }
        });
      });
    });
  </script>
</body>

</html>