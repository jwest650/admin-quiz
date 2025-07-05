

<?php

/*
  API v7.0.7
  Quiz Online - WRTeam.in
  WRTeam Developers
 */
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
}
include('library/crud.php');
include('library/functions.php');

$db = new Database();
$db->connect();

$fn = new Functions();
$config = $fn->get_configurations();

if (isset($config['system_timezone']) && !empty($config['system_timezone'])) {
    date_default_timezone_set($config['system_timezone']);
} else {
    date_default_timezone_set('Asia/Kolkata');
}
if (isset($config['system_timezone_gmt']) && !empty($config['system_timezone_gmt'])) {
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    $db->sql("SET `time_zone` = '+05:30'");
}

$db->sql("SET NAMES 'utf8'");
$auth_username = $db->escapeString($_SESSION["username"]);

$toDate = date('Y-m-d');
$toDateTime = date('Y-m-d H:i:s');
$allowedExts = array("gif", "jpeg", "jpg", "png", "JPEG", "JPG", "PNG");
$allowedType = array("pdf");

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

define('ALLOW_MODIFICATION', 1);
/*
  1. add_category
  2. update_category
  3. delete_category
  4. add_subcategory
  5. update_subcategory
  6. delete_subcategory
  7. get_subcategories_of_category
  8. add_question
  9. update_question
  10. delete_question
  11. send_notifications
  12. delete_notification
  13. update_fcm_server_key
  14. delete_question_report
  15. import_questions
  16. update_category_order
  17. update_subcategory_order
  18. update_policy
  19. update_terms
  20. update_user
  21. add_admin_form
  22. update_admin
  23. delete_admin
  24. system_configurations
  25. delete_multiple
  26. add_language
  27. update_language
  28. delete_language
  29. get_categories_of_language
  30. update_about_us
  31. update_instructions
  32. update_daily_quiz_order
  33. get_selected_date - Date options
  34. add_contest
  35. delete_contest
  36. update_contest
  37. update_contest_status
  38. add_contest_prize
  39. update_contest_prize
  40. delete_contest_prize
  41. add_contest_question
  42. update_contest_question
  43. delete_contest_question
  44. import_contest_questions
  45. battle_settings()
  46. add_learning
  47. update_question
  48. update_learning_status
  49. delete_question
  50.update_category_status
  51.add_junior_cateory

  functions
  ----------------
  1. checkadmin($auth_username)
 */

function checkadmin($auth_username)
{
    $db = new Database();
    $db->connect();
    $db->sql("SELECT `auth_username`,`role` FROM `authenticate` WHERE `auth_username`='$auth_username' LIMIT 1");
    $res = $db->getResult();
    if (!empty($res)) {
        if ($res[0]["role"] == "admin") {
            return true;
        } else {
            return false;
        }
    }
}

//7. get_subcategories_of_category - ajax dropdown menu options 
if (isset($_POST['get_subcategories_of_category']) && $_POST['get_subcategories_of_category'] != '') {
    $id = $_POST['category_id'];
    if (empty($id)) {
        echo '<option value="">Select Sub Category</option>';
        return false;
    }
    $sql = 'SELECT * FROM `subcategory` WHERE `maincat_id`=' . $id . ' ORDER BY row_order + 0 ASC';

    $db->sql($sql);
    $res = $db->getResult();

    if (isset($_POST['sortable']) && $_POST['sortable'] == 'sortable') {
        $options = '';
        foreach ($res as $category) {
            if (!empty($category["image"])) {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/subcategory/$category[image]' height=30 > " . $category["subcategory_name"] . "</li>";
            } else {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/logo-half.png' height=30 > " . $category["subcategory_name"] . "</li>";
            }
        }
    } else {
        $options = '<option value="">Select Sub Category</option>';
        foreach ($res as $option) {
            $options .= "<option value='" . $option['id'] . "'>" . $option['subcategory_name'] . "</option>";
        }
    }
    echo $options;
}
//7. get_junior_subcategories_of_category - ajax dropdown menu options 
if (isset($_POST['get_junior_subcategories_of_category']) && $_POST['get_junior_subcategories_of_category'] != '') {
    $id = $_POST['category_id'];
    if (empty($id)) {
        echo '<option value="">Select Sub Category</option>';
        return false;
    }
    $sql = 'SELECT * FROM `junior_subcategory` WHERE `maincat_id`=' . $id . ' ORDER BY row_order + 0 ASC';

    $db->sql($sql);
    $res = $db->getResult();

    if (isset($_POST['sortable']) && $_POST['sortable'] == 'sortable') {
        $options = '';
        foreach ($res as $category) {
            if (!empty($category["image"])) {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/subcategory/$category[image]' height=30 > " . $category["subcategory_name"] . "</li>";
            } else {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/logo-half.png' height=30 > " . $category["subcategory_name"] . "</li>";
            }
        }
    } else {
        $options = '<option value="">Select Sub Category</option>';
        foreach ($res as $option) {
            $options .= "<option value='" . $option['id'] . "'>" . $option['subcategory_name'] . "</option>";
        }
    }
    echo $options;
}

// 29. get_categories_of_language - ajax dropdown menu options 
if (isset($_POST['get_categories_of_language']) && $_POST['get_categories_of_language'] != '') {
    $id = $_POST['language_id'];
    $type = (isset($_POST['type'])) ? $_POST['type'] : 1;
    if (empty($id)) {
        echo '<option value="">Select Category</option>';
        return false;
    }
    $sql = 'SELECT * FROM `category` WHERE `language_id`=' . $id . ' AND `type`=' . $type . ' ORDER BY row_order + 0 ASC';
    $db->sql($sql);
    $res = $db->getResult();

    if (isset($_POST['sortable']) && $_POST['sortable'] == 'sortable') {
        $options = '';
        foreach ($res as $category) {

            if (!empty($category["image"])) {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/category/$category[image]' height=30 > " . $category["category_name"] . "</li>";
            } else {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/logo-half.png' height=30 > " . $category["category_name"] . "</li>";
            }
        }
    } else {
        $options = '<option value="">Select Category</option>';
        foreach ($res as $option) {
            $options .= "<option value='" . $option['id'] . "'>" . $option['category_name'] . "</option>";
        }
    }
    echo $options;
}

// 29 get_junior_categories_of_language 
if (isset($_POST['get_junior_categories_of_language']) && $_POST['get_junior_categories_of_language'] !== '') {
    $id = $_POST['language_id'];
    $type = (isset($_POST['type'])) ? $_POST['type'] : 1;
    if (empty($id)) {
        echo '<option value="">Select Category</option>';
        return false;
    }
    $sql = 'SELECT * FROM `junior_category` WHERE `language_id`=' . $id . ' AND `type`=' . $type . ' ORDER BY row_order + 0 ASC';
    $db->sql($sql);
    $res = $db->getResult();

    if (isset($_POST['sortable']) && $_POST['sortable'] == 'sortable') {
        $options = '';
        foreach ($res as $category) {

            if (!empty($category["image"])) {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/category/$category[image]' height=30 > " . $category["category_name"] . "</li>";
            } else {
                $options .= "<li id='" . $category["id"] . "'><big>" . $category["row_order"] . ".</big> &nbsp;<img src='images/logo-half.png' height=30 > " . $category["category_name"] . "</li>";
            }
        }
    } else {
        $options = '<option value="">Select Category</option>';
        foreach ($res as $option) {
            $options .= "<option value='" . $option['id'] . "'>" . $option['category_name'] . "</option>";
        }
    }
    echo $options;
}

// 33. get_selected_date - Date options 
if (isset($_POST['get_selected_date']) && !empty($_POST['get_selected_date']) && $_POST['language_id'] != "") {
    $selected_date = $db->escapeString($_POST['selected_date']);
    $language_id = $db->escapeString($_POST['language_id']);

    $sql = "SELECT * from daily_quiz WHERE date_published='$selected_date' AND language_id= '$language_id'";
    $db->sql($sql);
    $res = $db->getResult();
    $html = "";

    if (!empty($res)) {
        foreach ($res as $row) {
            $language_id = $row['language_id'];
        }
        $questions = $response = array();
        $questions = $res[0]['questions_id'];
        $sql = "SELECT `id`, `question` FROM `question` WHERE `id` IN (" . $questions . ") ORDER BY FIELD(id," . $questions . ")";
        $db->sql($sql);
        $res = $db->getResult();
        foreach ($res as $question) {
            $html .= "<li id=" . $question['id'] . " class='ui-state-default ui-sortable-handle'>" . $question['id'] . ". " . $question['question'] . "<a class='btn btn-danger btn-xs remove-row pull-right'>x</a></li>";
        }
        $response['error'] = false;
        $response['language_id'] = $language_id;
        $response['questions_list'] = $html;
    } else {
        //        $html .= "<li id='' class='ui-state-default ui-sortable-handle'>There are no questions added today<a class='btn btn-danger btn-xs remove-row pull-right'>x</a></li>";
        $response['error'] = false;
        $response['questions_list'] = $html;
        $response['language_id'] = '';
    }
    print_r(json_encode($response));
}

// 33. get_selected_date for junior - Date options 
if (isset($_POST['get_selected_date_junior']) && !empty($_POST['get_selected_date_junior']) && $_POST['language_id'] != "") {
    $selected_date = $db->escapeString($_POST['selected_date']);
    $language_id = $db->escapeString($_POST['language_id']);

    $sql = "SELECT * from daily_junior_quiz WHERE date_published='$selected_date' AND language_id= '$language_id'";
    $db->sql($sql);
    $res = $db->getResult();
    $html = "";

    if (!empty($res)) {
        foreach ($res as $row) {
            $language_id = $row['language_id'];
        }
        $questions = $response = array();
        $questions = $res[0]['questions_id'];
        $sql = "SELECT `id`, `junior_question` FROM `question` WHERE `id` IN (" . $questions . ") ORDER BY FIELD(id," . $questions . ")";
        $db->sql($sql);
        $res = $db->getResult();
        foreach ($res as $question) {
            $html .= "<li id=" . $question['id'] . " class='ui-state-default ui-sortable-handle'>" . $question['id'] . ". " . $question['question'] . "<a class='btn btn-danger btn-xs remove-row pull-right'>x</a></li>";
        }
        $response['error'] = false;
        $response['language_id'] = $language_id;
        $response['questions_list'] = $html;
    } else {
        //        $html .= "<li id='' class='ui-state-default ui-sortable-handle'>There are no questions added today<a class='btn btn-danger btn-xs remove-row pull-right'>x</a></li>";
        $response['error'] = false;
        $response['questions_list'] = $html;
        $response['language_id'] = '';
    }
    print_r(json_encode($response));
}

if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
    echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
    return false;
}
// 1.add_category
if (isset($_POST['name']) && isset($_POST['add_category'])) {
    $type = $db->escapeString($_POST['type']);
    $name = $db->escapeString($_POST['name']);
    $plan = isset($_POST['category_plan']) ? $db->escapeString($_POST['category_plan']) : NULL;
    $amount = isset($_POST['category_amount']) ? $db->escapeString($_POST['category_amount']) : 0;
    if ($plan === "Paid" && $amount <= 0) {
        echo '<label class="alert alert-danger">Amount must be greater than 0</label>';
        return false;
    }

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $filename = '';
    // common image file extensions
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        if (!is_dir('images/category')) {
            mkdir('images/category', 0777, true);
        }

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'images/category/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }
    $sql = "INSERT INTO `category` (`language_id`, `category_name`, `type`, `image`,`plan`,`amount`, `row_order`) VALUES ('" . $language_id . "','" . $name . "','" . $type . "','" . $filename . "','" . $plan . "','" . $amount . "','1')";
    $db->sql($sql);

    echo '<label class="alert alert-success">Category created successfully!</label>';
}

//add_junior_cateory
if (isset($_POST['name']) && isset($_POST['junior_category'])) {
    $type = $db->escapeString($_POST['type']);
    $name = $db->escapeString($_POST['name']);
    $plan = isset($_POST['category_plan']) ? $db->escapeString($_POST['category_plan']) : NULL;
    $amount = isset($_POST['category_amount']) ? $db->escapeString($_POST['category_amount']) : 0;
    if ($plan === "Paid" && $amount <= 0) {
        echo '<label class="alert alert-danger">Amount must be greater than 0</label>';
        return false;
    }

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $filename = '';
    // common image file extensions
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        if (!is_dir('images/category')) {
            mkdir('images/category', 0777, true);
        }

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'images/category/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }
    $sql = "INSERT INTO `junior_category` (`language_id`, `category_name`, `type`, `image`,`plan`,`amount`, `row_order`,`status`) VALUES ('" . $language_id . "','" . $name . "','" . $type . "','" . $filename . "','" . $plan . "','" . $amount . "','1',1)";
    $db->sql($sql);

    echo '<label class="alert alert-success">Category created successfully!</label>';
}





//2. update_category
if (isset($_POST['category_id']) && isset($_POST['update_category'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['category_id'];
    $name = $db->escapeString($_POST['name']);
    $amount = $_POST['update_category_amount'];
    $plan = $_POST['update_category_plan'];
    if ($plan === "Paid" && $amount <= 0) {
        echo '<label class="alert alert-danger">Amount must be greater than 0</label>';
        return false;
    }

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        if (!is_dir('images/category')) {
            mkdir('images/category', 0777, true);
        }
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);
        // common image file extensions
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        $target_path = 'images/category/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }

        if ($image_url != "images/logo-half.png" && file_exists($image_url)) {
            unlink($image_url);
        }
        $sql = "UPDATE category SET `image`='" . $filename . "' WHERE `id`=" . $id;
        $db->sql($sql);
    }

    $sql = "UPDATE `category` SET `category_name`='" . $name . "' , `plan`='" . $plan . "',`amount`='" . $amount . "'";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql .= " WHERE `id`=" . $id;
    $db->sql($sql);

    if ($fn->is_language_mode_enabled()) {
        $sql1 = "UPDATE subcategory SET `language_id`='" . $language_id . "' WHERE `maincat_id`=" . $id;
        $db->sql($sql1);

        $sql2 = "UPDATE question SET `language_id`='" . $language_id . "' WHERE `category`=" . $id;
        $db->sql($sql2);

        $sql3 = "UPDATE tbl_learning SET `language_id`='" . $language_id . "' WHERE `category`=" . $id;
        $db->sql($sql3);

        $sql4 = "UPDATE tbl_maths_question SET `language_id`='" . $language_id . "' WHERE `category`=" . $id;
        $db->sql($sql4);
    }

    echo "<p class='alert alert-success'>Category updated successfully!</p>";
}

//2. update_junior_category
if (isset($_POST['category_id']) && isset($_POST['update_junior_category'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['category_id'];
    $name = $db->escapeString($_POST['name']);
    $amount = $_POST['update_category_amount'];
    $plan = $_POST['update_category_plan'];
    if ($plan === "Paid" && $amount <= 0) {
        echo '<label class="alert alert-danger">Amount must be greater than 0</label>';
        return false;
    }

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        if (!is_dir('images/category')) {
            mkdir('images/category', 0777, true);
        }
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);
        // common image file extensions
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        $target_path = 'images/category/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }

        if ($image_url != "images/logo-half.png" && file_exists($image_url)) {
            unlink($image_url);
        }
        $sql = "UPDATE junior_category SET `image`='" . $filename . "' WHERE `id`=" . $id;
        $db->sql($sql);
    }

    $sql = "UPDATE `junior_category` SET `category_name`='" . $name . "' , `plan`='" . $plan . "',`amount`='" . $amount . "'";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql .= " WHERE `id`=" . $id;
    $db->sql($sql);

    if ($fn->is_language_mode_enabled()) {
        $sql1 = "UPDATE junior_subcategory SET `language_id`='" . $language_id . "' WHERE `maincat_id`=" . $id;
        $db->sql($sql1);

        $sql2 = "UPDATE junior_question SET `language_id`='" . $language_id . "' WHERE `category`=" . $id;
        $db->sql($sql2);

        $sql3 = "UPDATE tbl_learning SET `language_id`='" . $language_id . "' WHERE `category`=" . $id;
        $db->sql($sql3);

        $sql4 = "UPDATE tbl_maths_question SET `language_id`='" . $language_id . "' WHERE `category`=" . $id;
        $db->sql($sql4);
    }

    echo "<p class='alert alert-success'>Category updated successfully!</p>";
}


//3. delete_category
if (isset($_GET['delete_category']) && $_GET['delete_category'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];
    $sql = 'DELETE FROM `category` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }

        // select sub category images & delete it
        $sql = 'SELECT `image` FROM `subcategory` WHERE `maincat_id`=' . $id;
        $db->sql($sql);
        $sub_category_images = $db->getResult();
        if (!empty($sub_category_images)) {
            foreach ($sub_category_images as $image) {
                if (!empty($image['image']) && file_exists('images/subcategory/' . $image['image'])) {
                    unlink('images/subcategory/' . $image['image']);
                }
            }
        }
        $sql = 'DELETE FROM `subcategory` WHERE `maincat_id`=' . $id;
        $db->sql($sql);

        $sql = 'SELECT `image` FROM `question` WHERE `category`=' . $id;
        $db->sql($sql);
        $question_images = $db->getResult();
        if (!empty($question_images)) {
            foreach ($question_images as $image) {
                if (!empty($image['image']) && file_exists('images/questions/' . $image['image'])) {
                    unlink('images/questions/' . $image['image']);
                }
            }
        }
        $sql = 'DELETE FROM `question` WHERE `category`=' . $id;
        $db->sql($sql);

        $sql2 = 'SELECT `id` FROM `tbl_learning` WHERE `category`=' . $id;
        $db->sql($sql2);
        $question_images2 = $db->getResult();
        if (!empty($question_images2)) {
            $learning_id = $question_images2[0]['id'];
            $sql = 'DELETE FROM `tbl_learning_question` WHERE `learning_id`=' . $learning_id;
            $db->sql($sql);
        }
        $sql2 = 'DELETE FROM `tbl_learning` WHERE `category`=' . $id;
        $db->sql($sql2);

        $sql3 = 'SELECT `image` FROM `tbl_maths_question` WHERE `category`=' . $id;
        $db->sql($sql3);
        $question_images3 = $db->getResult();
        if (!empty($question_images3)) {
            foreach ($question_images3 as $image3) {
                if (!empty($image3['image']) && file_exists('images/maths-question/' . $image3['image'])) {
                    unlink('images/maths-question/' . $image3['image']);
                }
            }
        }
        $sql3 = 'DELETE FROM `tbl_maths_question` WHERE `category`=' . $id;
        $db->sql($sql3);

        echo 1;
    } else {
        echo 0;
    }
}

//3. delete_junior_category
if (isset($_GET['delete_junior_category']) && $_GET['delete_junior_category'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];
    $sql = 'DELETE FROM `junior_category` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }

        // select sub category images & delete it
        $sql = 'SELECT `image` FROM `junior_subcategory` WHERE `maincat_id`=' . $id;
        $db->sql($sql);
        $sub_category_images = $db->getResult();
        if (!empty($sub_category_images)) {
            foreach ($sub_category_images as $image) {
                if (!empty($image['image']) && file_exists('images/subcategory/' . $image['image'])) {
                    unlink('images/subcategory/' . $image['image']);
                }
            }
        }
        $sql = 'DELETE FROM `junior_subcategory` WHERE `maincat_id`=' . $id;
        $db->sql($sql);

        $sql = 'SELECT `image` FROM `junior_question` WHERE `category`=' . $id;
        $db->sql($sql);
        $question_images = $db->getResult();
        if (!empty($question_images)) {
            foreach ($question_images as $image) {
                if (!empty($image['image']) && file_exists('images/questions/' . $image['image'])) {
                    unlink('images/questions/' . $image['image']);
                }
            }
        }
        $sql = 'DELETE FROM `junior_question` WHERE `category`=' . $id;
        $db->sql($sql);

        $sql2 = 'SELECT `id` FROM `tbl_learning` WHERE `category`=' . $id;
        $db->sql($sql2);
        $question_images2 = $db->getResult();
        if (!empty($question_images2)) {
            $learning_id = $question_images2[0]['id'];
            $sql = 'DELETE FROM `tbl_learning_question` WHERE `learning_id`=' . $learning_id;
            $db->sql($sql);
        }
        $sql2 = 'DELETE FROM `tbl_learning` WHERE `category`=' . $id;
        $db->sql($sql2);

        $sql3 = 'SELECT `image` FROM `tbl_maths_question` WHERE `category`=' . $id;
        $db->sql($sql3);
        $question_images3 = $db->getResult();
        if (!empty($question_images3)) {
            foreach ($question_images3 as $image3) {
                if (!empty($image3['image']) && file_exists('images/maths-question/' . $image3['image'])) {
                    unlink('images/maths-question/' . $image3['image']);
                }
            }
        }
        $sql3 = 'DELETE FROM `tbl_maths_question` WHERE `category`=' . $id;
        $db->sql($sql3);

        echo 1;
    } else {
        echo 0;
    }
}

//4. add_subcategory
if (isset($_POST['name']) && isset($_POST['add_subcategory'])) {
    $name = $db->escapeString($_POST['name']);
    $maincat_id = $db->escapeString($_POST['maincat_id']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;

    $filename = '';
    // common image file extensions
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        if (!is_dir('images/subcategory')) {
            mkdir('images/subcategory', 0777, true);
        }
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'images/subcategory/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }

    $sql = "INSERT INTO `subcategory` (`language_id`,`maincat_id`,`subcategory_name`, `image`,`row_order`) VALUES ('" . $language_id . "','" . $maincat_id . "','" . $name . "','" . $filename . "','0')";
    $db->sql($sql);

    echo '<label class="alert alert-success">Sub Category created successfully!</label>';
}
//4 junior-sub-category

if (isset($_POST['name']) && isset($_POST['add_junior_subcategory'])) {
    $name = $db->escapeString($_POST['name']);
    $maincat_id = $db->escapeString($_POST['maincat_id']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;

    $filename = '';
    // common image file extensions
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        if (!is_dir('images/subcategory')) {
            mkdir('images/subcategory', 0777, true);
        }
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'images/subcategory/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }

    $sql = "INSERT INTO `junior_subcategory` (`language_id`,`maincat_id`,`subcategory_name`, `image`,`row_order`) VALUES ('" . $language_id . "','" . $maincat_id . "','" . $name . "','" . $filename . "','0')";
    $db->sql($sql);

    echo '<label class="alert alert-success">Sub Category created successfully!</label>';
}

//5. update_subcategory
if (isset($_POST['subcategory_id']) && isset($_POST['update_subcategory'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['subcategory_id'];
    $name = $db->escapeString($_POST['name']);
    $maincat_id = $db->escapeString($_POST['maincat_id']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;

    $status = $db->escapeString($_POST['status']);
    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        if (!is_dir('images/subcategory')) {
            mkdir('images/subcategory', 0777, true);
        }
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);

        // common image file extensions
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        $target_path = 'images/subcategory/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if ($image_url != "images/logo-half.png" && file_exists($image_url)) {
            // if its not half logo image
            unlink($image_url);
        }
        $sql = "UPDATE subcategory SET `image`='" . $filename . "' WHERE `id`=" . $id;
        $db->sql($sql);
    }

    $sql = "UPDATE subcategory SET `maincat_id`='" . $maincat_id . "', `subcategory_name`='" . $name . "', `status`='" . $status . "' ";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql .= " WHERE `id`=" . $id;
    $db->sql($sql);

    $sql1 = "UPDATE question SET `category`='" . $maincat_id . "' ";
    $sql1 .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql1 .= " WHERE `subcategory` =" . $id;
    $db->sql($sql1);

    // $sql2 = "UPDATE tbl_learning SET `category`='" . $maincat_id . "' ";
    // $sql2 .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    // $sql2 .= " WHERE `subcategory` =" . $id;
    // $db->sql($sql2);

    $sql3 = "UPDATE tbl_maths_question SET `category`='" . $maincat_id . "' ";
    $sql3 .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql3 .= " WHERE `subcategory` =" . $id;
    $db->sql($sql3);

    echo "<p class='alert alert-success'>Sub category updated successfully!</p>";
}

//5. update_junior_subcategory
if (isset($_POST['subcategory_id']) && isset($_POST['update_junior_subcategory'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['subcategory_id'];
    $name = $db->escapeString($_POST['name']);
    $maincat_id = $db->escapeString($_POST['maincat_id']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;

    $status = $db->escapeString($_POST['status']);
    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        if (!is_dir('images/subcategory')) {
            mkdir('images/subcategory', 0777, true);
        }
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);

        // common image file extensions
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        $target_path = 'images/subcategory/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if ($image_url != "images/logo-half.png" && file_exists($image_url)) {
            // if its not half logo image
            unlink($image_url);
        }
        $sql = "UPDATE junior_subcategory SET `image`='" . $filename . "' WHERE `id`=" . $id;
        $db->sql($sql);
    }

    $sql = "UPDATE junior_subcategory SET `maincat_id`='" . $maincat_id . "', `subcategory_name`='" . $name . "', `status`='" . $status . "' ";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql .= " WHERE `id`=" . $id;
    $db->sql($sql);

    $sql1 = "UPDATE junior_question SET `category`='" . $maincat_id . "' ";
    $sql1 .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql1 .= " WHERE `subcategory` =" . $id;
    $db->sql($sql1);

    // $sql2 = "UPDATE tbl_learning SET `category`='" . $maincat_id . "' ";
    // $sql2 .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    // $sql2 .= " WHERE `subcategory` =" . $id;
    // $db->sql($sql2);

    $sql3 = "UPDATE tbl_maths_question SET `category`='" . $maincat_id . "' ";
    $sql3 .= ($fn->is_language_mode_enabled()) ? ", `language_id` = " . $language_id . " " : "";
    $sql3 .= " WHERE `subcategory` =" . $id;
    $db->sql($sql3);

    echo "<p class='alert alert-success'>Sub category updated successfully!</p>";
}

//6. delete_subcategory
if (isset($_GET['delete_subcategory']) && $_GET['delete_subcategory'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `subcategory` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }

        $sql = 'SELECT `image` FROM `question` WHERE `subcategory`=' . $id;
        $db->sql($sql);
        $question_images = $db->getResult();
        if (!empty($question_images)) {
            foreach ($question_images as $image) {
                if (!empty($image['image']) && file_exists('images/questions/' . $image['image'])) {
                    unlink('images/questions/' . $image['image']);
                }
            }
        }
        $sql = 'DELETE FROM `question` WHERE `subcategory`=' . $id;
        $db->sql($sql);

        $sql2 = 'SELECT `image` FROM `tbl_maths_question` WHERE `subcategory`=' . $id;
        $db->sql($sql2);
        $question_images2 = $db->getResult();
        if (!empty($question_images2)) {
            foreach ($question_images2 as $image2) {
                if (!empty($image2['image']) && file_exists('images/maths-question/' . $image2['image'])) {
                    unlink('images/maths-question/' . $image2['image']);
                }
            }
        }
        $sql2 = 'DELETE FROM `tbl_maths_question` WHERE `subcategory`=' . $id;
        $db->sql($sql2);

        echo 1;
    } else {
        echo 0;
    }
}

//6. delete_junior_subcategory
if (isset($_GET['delete_junior_subcategory']) && $_GET['delete_junior_subcategory'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `junior_subcategory` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }

        $sql = 'SELECT `image` FROM `junior_question` WHERE `subcategory`=' . $id;
        $db->sql($sql);
        $question_images = $db->getResult();
        if (!empty($question_images)) {
            foreach ($question_images as $image) {
                if (!empty($image['image']) && file_exists('images/questions/' . $image['image'])) {
                    unlink('images/questions/' . $image['image']);
                }
            }
        }
        $sql = 'DELETE FROM `junior_question` WHERE `subcategory`=' . $id;
        $db->sql($sql);

        $sql2 = 'SELECT `image` FROM `tbl_maths_question` WHERE `subcategory`=' . $id;
        $db->sql($sql2);
        $question_images2 = $db->getResult();
        if (!empty($question_images2)) {
            foreach ($question_images2 as $image2) {
                if (!empty($image2['image']) && file_exists('images/maths-question/' . $image2['image'])) {
                    unlink('images/maths-question/' . $image2['image']);
                }
            }
        }
        $sql2 = 'DELETE FROM `tbl_maths_question` WHERE `subcategory`=' . $id;
        $db->sql($sql2);

        echo 1;
    } else {
        echo 0;
    }
}

//8. add_question
if (isset($_POST['question']) && isset($_POST['add_question'])) {
    $question = $db->escapeString($_POST['question']);
    echo "<script>console.log(" . json_encode($question) . ");</script>";
    $category = $db->escapeString($_POST['category']);
    $subcategory = (empty($_POST['subcategory'])) ? 0 : $db->escapeString($_POST['subcategory']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $question_type = $db->escapeString($_POST['question_type']);
    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    $e = ($fn->is_option_e_mode_enabled()) ? (($question_type == 1) ? $db->escapeString($_POST['e']) : "") : "";
    $level = $db->escapeString($_POST['level']);
    $answer = $db->escapeString($_POST['answer']);
    $note = $db->escapeString($_POST['note']);

    $filename = $full_path = '';
    // common image file extensions
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        if (!is_dir('images/questions')) {
            mkdir('images/questions', 0777, true);
        }

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'images/questions/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }

    $sql = "INSERT INTO `question`(`category`, `subcategory`, `language_id`, `image`, `question`, `question_type`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `level`, `answer`, `note`) VALUES 
	('" . $category . "','" . $subcategory . "','" . $language_id . "','" . $filename . "','" . $question . "','" . $question_type . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $level . "','" . $answer . "','" . $note . "')";

    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Question created successfully!</label>';
}

//8. add_junior_question

if (isset($_POST['question']) && isset($_POST['add_junior_question'])) {
    $question = $db->escapeString($_POST['question']);
    echo "<script>console.log(" . json_encode($question) . ");</script>";
    $category = $db->escapeString($_POST['category']);
    $subcategory = (empty($_POST['subcategory'])) ? 0 : $db->escapeString($_POST['subcategory']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $question_type = $db->escapeString($_POST['question_type']);
    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    $e = ($fn->is_option_e_mode_enabled()) ? (($question_type == 1) ? $db->escapeString($_POST['e']) : "") : "";
    $level = $db->escapeString($_POST['level']);
    $answer = $db->escapeString($_POST['answer']);
    $note = $db->escapeString($_POST['note']);

    $filename = $full_path = '';
    // common image file extensions
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        if (!is_dir('images/questions')) {
            mkdir('images/questions', 0777, true);
        }

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'images/questions/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }

    $sql = "INSERT INTO `junior_question`(`category`, `subcategory`, `language_id`, `image`, `question`, `question_type`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `level`, `answer`, `note`) VALUES 
	('" . $category . "','" . $subcategory . "','" . $language_id . "','" . $filename . "','" . $question . "','" . $question_type . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $level . "','" . $answer . "','" . $note . "')";

    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Question created successfully!</label>';
}


//9. update_question
if (isset($_POST['question_id']) && isset($_POST['update_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['question_id'];

    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);

        // common image file extensions
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if (!is_dir('images/questions')) {
            mkdir('images/questions', 0777, true);
        }
        $target_path = 'images/questions/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if (!empty($image_url) && file_exists($image_url)) {
            unlink($image_url);
        }
        $sql = "UPDATE `question` SET `image`='" . $filename . "' where `id`=" . $id;
        $db->sql($sql);
    }

    $question = $db->escapeString($_POST['question']);
    $category = $db->escapeString($_POST['category']);
    $subcategory = (empty($_POST['subcategory'])) ? 0 : $db->escapeString($_POST['subcategory']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $question_type = $db->escapeString($_POST['edit_question_type']);
    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    if ($fn->is_option_e_mode_enabled()) {
        $e = ($question_type == 1) ? $db->escapeString($_POST['e']) : "";
    }
    $level = $db->escapeString($_POST['level']);
    $answer = $db->escapeString($_POST['answer']);
    $note = $db->escapeString($_POST['note']);

    $sql = "Update `question` set `question`='" . $question . "', `category`='" . $category . "', `subcategory`='" . $subcategory . "',`question_type`='" . $question_type . "',`optiona`='" . $a . "',`optionb`='" . $b . "' ,`optionc`='" . $c . "' ,`optiond`='" . $d . "', `answer`='" . $answer . "' ,`level`='" . $level . "',`note`='" . $note . "'";
    $sql .= ($fn->is_option_e_mode_enabled()) ? ",`optione`='" . $e . "'" : "";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id`=" . $language_id : "";
    $sql .= " where `id`=" . $id;
    $db->sql($sql);

    echo "<p class='alert alert-success'>Question updated successfully!</p>";
}

//9. update_junior_question
if (isset($_POST['question_id']) && isset($_POST['update_junior_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['question_id'];

    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);

        // common image file extensions
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if (!is_dir('images/questions')) {
            mkdir('images/questions', 0777, true);
        }
        $target_path = 'images/questions/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if (!empty($image_url) && file_exists($image_url)) {
            unlink($image_url);
        }
        $sql = "UPDATE `junior_question` SET `image`='" . $filename . "' where `id`=" . $id;
        $db->sql($sql);
    }

    $question = $db->escapeString($_POST['question']);
    $category = $db->escapeString($_POST['category']);
    $subcategory = (empty($_POST['subcategory'])) ? 0 : $db->escapeString($_POST['subcategory']);
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $question_type = $db->escapeString($_POST['edit_question_type']);
    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    if ($fn->is_option_e_mode_enabled()) {
        $e = ($question_type == 1) ? $db->escapeString($_POST['e']) : "";
    }
    $level = $db->escapeString($_POST['level']);
    $answer = $db->escapeString($_POST['answer']);
    $note = $db->escapeString($_POST['note']);

    $sql = "Update `junior_question` set `question`='" . $question . "', `category`='" . $category . "', `subcategory`='" . $subcategory . "',`question_type`='" . $question_type . "',`optiona`='" . $a . "',`optionb`='" . $b . "' ,`optionc`='" . $c . "' ,`optiond`='" . $d . "', `answer`='" . $answer . "' ,`level`='" . $level . "',`note`='" . $note . "'";
    $sql .= ($fn->is_option_e_mode_enabled()) ? ",`optione`='" . $e . "'" : "";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id`=" . $language_id : "";
    $sql .= " where `id`=" . $id;
    $db->sql($sql);

    echo "<p class='alert alert-success'>Question updated successfully!</p>";
}


//10. delete_question
if (isset($_GET['delete_question']) && $_GET['delete_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `question` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }
        echo 1;
    } else {
        echo 0;
    }
}
//10. delete_junior_question
if (isset($_GET['delete_junior_question']) && $_GET['delete_junior_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `junior_question` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }
        echo 1;
    } else {
        echo 0;
    }
}

//11. send_notifications - send notifications to users
if (isset($_POST['title']) && isset($_POST['send_notifications'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $sql = 'select `fcm_key` from `tbl_fcm_key` where id=1';
    $db->sql($sql);
    $res = $db->getResult();

    define('API_ACCESS_KEY', $res[0]['fcm_key']);

    //creating a new push
    $title = $db->escapeString($_POST['title']);
    $message = $db->escapeString($_POST['message']);
    $users = $db->escapeString($_POST['users']);
    $type = $db->escapeString($_POST['type']);

    $maxlevel = $no_of = "0";
    $maincat_id = "0";
    $language_id = "0";
    $category_type = "0";
    if ($type == 'category') {
        $maincat_id = $db->escapeString($_POST['maincat_id']);

        $sql = "select 	type as category_type, language_id FROM category WHERE id = " . $maincat_id;
        $db->sql($sql);
        $res = $db->getResult();
        $language_id = $res[0]['language_id'];
        $category_type = $res[0]['category_type'];

        $sql1 = "select max(`level`) as `maxlevel` FROM question WHERE category = " . $maincat_id;
        $db->sql($sql1);
        $res1 = $db->getResult();
        $maxlevel = $res1[0]['maxlevel'];

        $sql2 = "SELECT count(`id`) as no_of from subcategory s WHERE s.maincat_id = " . $maincat_id . " and s.status = 1 ";
        $db->sql($sql2);
        $res2 = $db->getResult();
        $no_of = $res2[0]['no_of'];
    }

    if ($users == 'all') {
        $sql = "select `fcm_id` from `users` ";
        $db->sql($sql);
        $res = $db->getResult();
        $fcm_ids = array();
        foreach ($res as $fcm_id) {
            $fcm_ids[] = $fcm_id['fcm_id'];
        }
    } elseif ($users == 'selected') {
        $selected_list = $_POST['selected_list'];
        if (empty($selected_list)) {
            $response['error'] = true;
            $response['message'] = 'Please Select the users from the table';
            echo json_encode($response);
            return false;
        }
        $fcm_ids = array();
        $fcm_ids = explode(",", $selected_list);
    }

    $registrationIDs = $fcm_ids;

    $include_image = (isset($_POST['include_image']) && $_POST['include_image'] == 'on') ? TRUE : FALSE;
    if ($include_image) {
        if (!is_dir('images/notifications')) {
            mkdir('images/notifications', 0777, true);
        }
        // common image file extensions
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'images/notifications/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $sql = "INSERT INTO `notifications`(`title`,`message`,`users`,`type`,`type_id`,`image`) VALUES 
			('" . $title . "','" . $message . "','" . $users . "','" . $type . "'," . $maincat_id . ",'" . $filename . "')";
    } else {
        $sql = "INSERT INTO `notifications`(`title`,`message`,`users`,`type`,`type_id`,`image`) VALUES 
			('" . $title . "','" . $message . "','" . $users . "','" . $type . "'," . $maincat_id . ",'')";
    }

    $db->sql($sql);
    $newMsg = array();
    $fcmMsg = array();

    //first check if the push has an image with it
    if ($include_image) {
        $fcmMsg = array(
            'title' => $title,
            'body' => $message,
            'image' => DOMAIN_URL . $full_path,
            'type' => $type,
            'type_id' => $maincat_id,
            'language_id' => $language_id,
            'maxlevel' => $maxlevel,
            'no_of' => $no_of,
            'category_type' => $category_type
        );
        // $newMsg['data'] = $fcmMsg;
    } else {
        //if the push don't have an image give null in place of image
        $fcmMsg = array(
            'title' => $title,
            'body' => $message,
            'image' => "no_image",
            'type' => $type,
            'type_id' => $maincat_id,
            'language_id' => $language_id,
            'maxlevel' => $maxlevel,
            'no_of' => $no_of,
            'category_type' => $category_type
        );
        // $newMsg['data'] = $fcmMsg;
    }
    // $notification_msg = array(
    //     'title' => $title,
    //     'body' => $message,
    // );
    $registrationIDs_chunks = array_chunk($registrationIDs, 1000);

    $success = $failure = 0;

    foreach ($registrationIDs_chunks as $registrationIDs) {
        $fcmFields = array(
            // 'to' => $singleID,
            'registration_ids' => $registrationIDs, // expects an array of ids
            'priority' => 'high',
            'notification' => $fcmMsg,
            'data' => $fcmMsg,
        );

        $headers = array(
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, 1);

        $success += $result['success'];
        $failure += $result['failure'];
    }
    // echo json_encode($fcmFields);
    echo '<p class="alert alert-success">Notification Sent Successfully</p>';
}

// 12. delete_notification
if (isset($_POST['id']) && isset($_POST['delete_notification'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM `notifications` WHERE `id`=" . $id;
    if ($db->sql($sql)) {
        if (isset($_POST['image']) && $_POST['image'] != '') {
            $image = 'images/notifications/' . $_POST['image'];
            unlink($image);
        }
        echo 1;
    } else
        echo 0;
}

// 13. update_fcm_server_key()
if (isset($_POST['update_fcm_server_key'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $fcm_key = $db->escapeString($_POST['fcm_key']);
    $update_fcm_server_key_id = $db->escapeString($_POST['update_fcm_server_key_id']);
    if (empty($_POST['update_fcm_server_key_id'])) {
        $sql = "INSERT INTO tbl_fcm_key (fcm_key) VALUES ('" . $fcm_key . "')";
        $db->sql($sql);
        $res = $db->getResult();
        echo "<p class='alert alert-success'>FCM Key Inserted Successfully!</p><br>";
    } else {
        $sql = "Update `tbl_fcm_key` set `fcm_key`='" . $fcm_key . "' where `id`=" . $update_fcm_server_key_id;
        $db->sql($sql);
        $res = $db->getResult();
        echo "<p class='alert alert-success'>FCM Key Updated Successfully!</p><br>";
    }
}

// 14. delete_question_report
if (isset($_GET['delete_question_report']) && $_GET['delete_question_report'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];

    $sql = 'DELETE FROM `question_reports` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 15. import_questions - import questions to database from a CSV file
if (isset($_POST['import_questions']) && $_POST['import_questions'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $count = $count1 = 0;
    $filename = $_FILES["questions_file"]["tmp_name"];
    $file_extension = pathinfo($_FILES["questions_file"]["name"], PATHINFO_EXTENSION);
    if ($_FILES["questions_file"]["size"] > 0 && $file_extension == "csv") {
        $file = fopen($filename, "r");

        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if (count($emapData) > 2) {
                $emapData[0] = $db->escapeString($emapData[0]); //category
                $emapData[1] = (empty($db->escapeString($emapData[1]))) ? "0" : $db->escapeString($db->escapeString($emapData[1])); //subcategory
                $emapData[2] = ($fn->is_language_mode_enabled()) ? $db->escapeString($emapData[2]) : "0";   //language_id
                $emapData[3] = $db->escapeString(trim($emapData[3]));   //question_type
                $emapData[4] = $db->escapeString($emapData[4]);     //question
                $emapData[5] = $db->escapeString($emapData[5]);    // optiona
                $emapData[6] = $db->escapeString($emapData[6]);    // optionb
                $emapData[7] = $db->escapeString($emapData[7]);    // optionc
                $emapData[8] = $db->escapeString($emapData[8]);    // optiond
                $emapData[9] = (empty($db->escapeString($emapData[9]))) ? "" : $db->escapeString($emapData[9]);  // optione
                $emapData[10] = $db->escapeString(trim($emapData[10]));  //answer
                $emapData[11] = $db->escapeString($emapData[11]);       //level
                $emapData[12] = $db->escapeString($emapData[12]);      // note
                $count++;
                if ($count > 1) {
                    if ($emapData[3] == '1') {
                        if ($emapData[0] != '' && $emapData[1] != '' && $emapData[2] != '' && !empty($emapData[3]) && $emapData[4] != '' && $emapData[5] != '' && $emapData[6] != '' && $emapData[7] != '' && $emapData[8] != '' && !empty($emapData[10]) && $emapData[11] != '') {
                            $empty_value_found = true;
                        } else {
                            $empty_value_found = false;
                            echo '<p class="text-danger">Please Check ' . $count . ' row</p>';
                            break;
                        }
                    } else if ($emapData[3] == '2') {
                        if ($emapData[0] != '' && $emapData[1] != '' && $emapData[2] != '' && !empty($emapData[3]) && $emapData[4] != '' && $emapData[5] != '' && $emapData[6] != '' && !empty($emapData[10]) && $emapData[11] != '') {
                            $empty_value_found = true;
                        } else {
                            $empty_value_found = false;
                            echo '<p class="text-danger">Please Check ' . $count . ' row</p>';
                            break;
                        }
                    } else {
                        $empty_value_found = false;
                        break;
                    }
                }
            }
        }
        fclose($file);
        if ($empty_value_found == TRUE) {
            $file = fopen($filename, "r");
            while (($emapData1 = fgetcsv($file, 10000, ",")) !== FALSE) {
                if (count($emapData1) > 2) {
                    $emapData1[0] = $db->escapeString($emapData1[0]);
                    $emapData1[1] = (empty($db->escapeString($emapData1[1]))) ? "0" : $db->escapeString($db->escapeString($emapData1[1]));
                    $emapData1[2] = ($fn->is_language_mode_enabled()) ? $db->escapeString($emapData1[2]) : "0";
                    $emapData1[3] = $db->escapeString($emapData1[3]);
                    $emapData1[4] = $db->escapeString($emapData1[4]);
                    $emapData1[5] = $db->escapeString($emapData1[5]);
                    $emapData1[6] = $db->escapeString($emapData1[6]);
                    $emapData1[7] = $db->escapeString($emapData1[7]);
                    $emapData1[8] = $db->escapeString($emapData1[8]);
                    $emapData1[9] = (empty($db->escapeString($emapData1[9]))) ? "" : $db->escapeString($emapData1[9]);
                    $emapData1[10] = $db->escapeString(trim($emapData1[10]));
                    $emapData1[11] = $db->escapeString($emapData1[11]);
                    $emapData1[12] = $db->escapeString($emapData1[12]);
                    $count1++;
                    if ($count1 > 1) {
                        if (count($emapData1) > 2) {
                            $sql = "INSERT INTO `question`(`category`, `subcategory`, `language_id`, `image`, `question_type`, `question`,`optiona`, `optionb`, `optionc`, `optiond`,  `optione`, `answer`, `level`, `note`) VALUES 
						('$emapData1[0]','$emapData1[1]','$emapData1[2]','','$emapData1[3]','$emapData1[4]','$emapData1[5]','$emapData1[6]','$emapData1[7]','$emapData1[8]','$emapData1[9]','$emapData1[10]','$emapData1[11]','$emapData1[12]')";
                            $db->sql($sql);
                        }
                    }
                }
            }
            fclose($file);
            echo "<p class='alert alert-success'>CSV file is successfully imported!</p>";
        } else {
            echo "<p class='alert alert-danger'>Please fill all the data in CSV file!</p>";
        }
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p>";
    }
}

// 15. import_junior_questions - import questions to database from a CSV file
if (isset($_POST['import_junior_questions']) && $_POST['import_junior_questions'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $count = $count1 = 0;
    $filename = $_FILES["questions_file"]["tmp_name"];
    $file_extension = pathinfo($_FILES["questions_file"]["name"], PATHINFO_EXTENSION);
    if ($_FILES["questions_file"]["size"] > 0 && $file_extension == "csv") {
        $file = fopen($filename, "r");

        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if (count($emapData) > 2) {
                $emapData[0] = $db->escapeString($emapData[0]); //category
                $emapData[1] = (empty($db->escapeString($emapData[1]))) ? "0" : $db->escapeString($db->escapeString($emapData[1])); //subcategory
                $emapData[2] = ($fn->is_language_mode_enabled()) ? $db->escapeString($emapData[2]) : "0";   //language_id
                $emapData[3] = $db->escapeString(trim($emapData[3]));   //question_type
                $emapData[4] = $db->escapeString($emapData[4]);     //question
                $emapData[5] = $db->escapeString($emapData[5]);    // optiona
                $emapData[6] = $db->escapeString($emapData[6]);    // optionb
                $emapData[7] = $db->escapeString($emapData[7]);    // optionc
                $emapData[8] = $db->escapeString($emapData[8]);    // optiond
                $emapData[9] = (empty($db->escapeString($emapData[9]))) ? "" : $db->escapeString($emapData[9]);  // optione
                $emapData[10] = $db->escapeString(trim($emapData[10]));  //answer
                $emapData[11] = $db->escapeString($emapData[11]);       //level
                $emapData[12] = $db->escapeString($emapData[12]);      // note
                $count++;
                if ($count > 1) {
                    if ($emapData[3] == '1') {
                        if ($emapData[0] != '' && $emapData[1] != '' && $emapData[2] != '' && !empty($emapData[3]) && $emapData[4] != '' && $emapData[5] != '' && $emapData[6] != '' && $emapData[7] != '' && $emapData[8] != '' && !empty($emapData[10]) && $emapData[11] != '') {
                            $empty_value_found = true;
                        } else {
                            $empty_value_found = false;
                            echo '<p class="text-danger">Please Check ' . $count . ' row</p>';
                            break;
                        }
                    } else if ($emapData[3] == '2') {
                        if ($emapData[0] != '' && $emapData[1] != '' && $emapData[2] != '' && !empty($emapData[3]) && $emapData[4] != '' && $emapData[5] != '' && $emapData[6] != '' && !empty($emapData[10]) && $emapData[11] != '') {
                            $empty_value_found = true;
                        } else {
                            $empty_value_found = false;
                            echo '<p class="text-danger">Please Check ' . $count . ' row</p>';
                            break;
                        }
                    } else {
                        $empty_value_found = false;
                        break;
                    }
                }
            }
        }
        fclose($file);
        if ($empty_value_found == TRUE) {
            $file = fopen($filename, "r");
            while (($emapData1 = fgetcsv($file, 10000, ",")) !== FALSE) {
                if (count($emapData1) > 2) {
                    $emapData1[0] = $db->escapeString($emapData1[0]);
                    $emapData1[1] = (empty($db->escapeString($emapData1[1]))) ? "0" : $db->escapeString($db->escapeString($emapData1[1]));
                    $emapData1[2] = ($fn->is_language_mode_enabled()) ? $db->escapeString($emapData1[2]) : "0";
                    $emapData1[3] = $db->escapeString($emapData1[3]);
                    $emapData1[4] = $db->escapeString($emapData1[4]);
                    $emapData1[5] = $db->escapeString($emapData1[5]);
                    $emapData1[6] = $db->escapeString($emapData1[6]);
                    $emapData1[7] = $db->escapeString($emapData1[7]);
                    $emapData1[8] = $db->escapeString($emapData1[8]);
                    $emapData1[9] = (empty($db->escapeString($emapData1[9]))) ? "" : $db->escapeString($emapData1[9]);
                    $emapData1[10] = $db->escapeString(trim($emapData1[10]));
                    $emapData1[11] = $db->escapeString($emapData1[11]);
                    $emapData1[12] = $db->escapeString($emapData1[12]);
                    $count1++;
                    if ($count1 > 1) {
                        if (count($emapData1) > 2) {
                            $sql = "INSERT INTO `junior_question`(`category`, `subcategory`, `language_id`, `image`, `question_type`, `question`,`optiona`, `optionb`, `optionc`, `optiond`,  `optione`, `answer`, `level`, `note`) VALUES 
						('$emapData1[0]','$emapData1[1]','$emapData1[2]','','$emapData1[3]','$emapData1[4]','$emapData1[5]','$emapData1[6]','$emapData1[7]','$emapData1[8]','$emapData1[9]','$emapData1[10]','$emapData1[11]','$emapData1[12]')";
                            $db->sql($sql);
                        }
                    }
                }
            }
            fclose($file);
            echo "<p class='alert alert-success'>CSV file is successfully imported!</p>";
        } else {
            echo "<p class='alert alert-danger'>Please fill all the data in CSV file!</p>";
        }
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p>";
    }
}


// 16. update_category_order
if (isset($_POST['update_category_order']) && $_POST['update_category_order'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id_ary = explode(",", $_POST["row_order"]);
    for ($i = 0; $i < count($id_ary); $i++) {
        $sql = "UPDATE category SET row_order='" . $i . "' WHERE id=" . $id_ary[$i];
        $db->sql($sql);
        $res = $db->getResult();
    }
    echo "<p class='alert alert-success'>Category order updated!</p>";
}

// 16. update_junior_category_order
if (isset($_POST['update_junior_category_order']) && $_POST['update_junior_category_order'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id_ary = explode(",", $_POST["row_order"]);
    for ($i = 0; $i < count($id_ary); $i++) {
        $sql = "UPDATE junior_category SET row_order='" . $i . "' WHERE id=" . $id_ary[$i];
        $db->sql($sql);
        $res = $db->getResult();
    }
    echo "<p class='alert alert-success'>Category order updated!</p>";
}


// 17. update_subcategory_order
if (isset($_POST['update_subcategory_order']) && $_POST['update_subcategory_order'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id_ary = explode(",", $_POST["row_order_2"]);
    for ($i = 0; $i < count($id_ary); $i++) {
        $sql = "UPDATE subcategory SET row_order='" . $i . "' WHERE id=" . $id_ary[$i];
        $db->sql($sql);
        $res = $db->getResult();
    }
    echo "<p class='alert alert-success'>Subcategory order updated!</p>";
}

// 17. update_junior_subcategory_order
if (isset($_POST['update_junior_subcategory_order']) && $_POST['update_junior_subcategory_order'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id_ary = explode(",", $_POST["row_order_2"]);
    for ($i = 0; $i < count($id_ary); $i++) {
        $sql = "UPDATE junior_subcategory SET row_order='" . $i . "' WHERE id=" . $id_ary[$i];
        $db->sql($sql);
        $res = $db->getResult();
    }
    echo "<p class='alert alert-success'>Subcategory order updated!</p>";
}


// 18. update_policy()
if (isset($_POST['update_policy'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $message = $db->escapeString($_POST['message']);
    $sql = "select * from `settings` where `type`='privacy_policy'";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $sql = "Update `settings` set `message`='" . $message . "' where `type`='privacy_policy'";
    } else {
        $sql = "INSERT INTO `settings`(`type`, `message`, `status`) VALUES ('privacy_policy','" . $message . "',1)";
    }

    $db->sql($sql);
    $res = $db->getResult();
    echo "<p class='alert alert-success'>Privacy policy updated Successfully!</p><br>";
}

// 19. update_terms()
if (isset($_POST['update_terms'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $message = $db->escapeString($_POST['message']);
    $sql = "select * from `settings` where `type`='update_terms'";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $sql = "Update `settings` set `message`='" . $message . "' where `type`='update_terms'";
    } else {
        $sql = "INSERT INTO `settings`(`type`, `message`, `status`) VALUES ('update_terms','" . $message . "',1)";
    }
    $db->sql($sql);
    $res = $db->getResult();
    echo "<p class='alert alert-success'>Terms and conditions updated Successfully!</p><br>";
}

// 20. update_user()
if (isset($_POST['user_id']) && isset($_POST['update_user'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['user_id'];
    $status = $db->escapeString($_POST['status']);
    $sql = "Update users set `status`='" . $status . "' where `id`=" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    echo "<p class='alert alert-success'>User Status updated!</p>";
}

// 21. add_admin_form
if (isset($_POST["add_admin"]) && !empty($_POST["add_admin"]) && $_POST['add_admin'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $username = $db->escapeString($_POST['username']);
    $role = $db->escapeString($_POST['role']);
    $password = $db->escapeString($_POST['password']);
    $password = md5($password);
    $sql = "SELECT auth_username FROM authenticate WHERE auth_username='" . $username . "'";
    $db->sql($sql);
    $res = $db->getResult();
    if ($res) {
        echo "<p class='alert alert-warning'>$username is already exists.</p>";
    } else {
        $data = array('auth_username' => $username, 'auth_pass' => $password, 'role' => $role, 'app_passcode' => '0', 'android_key' => '0', 'status' => '0');
        $db->insert('authenticate', $data);
        $res = $db->getResult();
        if ($res) {
            echo "<p class='alert alert-success'>" . $username . " added as " . $role . "!</p>";
        } else {
            echo "<p class='alert alert-danger'>Admin registration is failed. try again.</p>";
        }
    }
}

// 22. update_admin
if (isset($_POST['update_admin']) && !empty($_POST['update_admin']) && $_POST['update_admin'] == 1 && !empty($_POST['update_admin_id'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $update_admin_id = $db->escapeString($_POST['update_admin_id']);
    $update_username = $db->escapeString($_POST['update_username']);
    $update_role = $db->escapeString($_POST['update_role']);
    $sql = "UPDATE authenticate SET auth_username='" . $update_username . "',role='" . $update_role . "' WHERE auth_username='" . $update_admin_id . "'";
    $db->sql($sql);
    $res = $db->getResult();
    if ($res) {
        echo "<p class='alert alert-danger'>$update_username is not updated.</p>";
    } else {
        echo "<p class='alert alert-success'>$update_username is successfully updated.</p>";
    }
}

// 23. delete_admin
if (isset($_POST['delete_admin']) && !empty($_POST['id']) && $_POST['delete_admin'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $db->escapeString($_POST['id']);
    $sql = "DELETE FROM `authenticate` WHERE `auth_username`='" . $id . "'";
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 24. system_configurations
if (isset($_POST['app_link']) && isset($_POST['system_configurations'])) {

    $date = $db->escapeString(date('Y-m-d'));
    if (!empty($_POST['system_configurations_id'])) {
        $_POST['system_timezone_gmt'] = preg_replace('/\s+/', '', $_POST['system_timezone_gmt']);
        $_POST['system_timezone_gmt'] = ($_POST['system_timezone_gmt'] == '00:00') ? "+" . $_POST['system_timezone_gmt'] : $_POST['system_timezone_gmt'];
        $sql = "UPDATE settings SET message='" . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "' WHERE type='system_configurations'";
    } else {
        $sql = "INSERT INTO settings (type,message,status) VALUES ('system_configurations','" . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "','1')";
    }
    $db->sql($sql);
    $res = $db->getResult();
    echo "<p class='alert alert-success'>Settings Saved!</p>";
}

// 25. delete_multiple
if (isset($_GET['delete_multiple']) && $_GET['delete_multiple'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $ids = $db->escapeString($_GET['ids']);
    $table = $db->escapeString($_GET['sec']);
    $is_image = $_GET['is_image'];

    if ($is_image) {
        $path = array(
            'category' => 'images/category/',
            'subcategory' => 'images/subcategory/',
            'question' => 'images/questions/',
            'notifications' => 'images/notifications/',
            'contest' => 'images/contest/',
            'contest_questions' => 'images/contest-question/',
            'tbl_maths_question' => 'images/maths-question/',
        );

        $sql = "select `image` from " . $table . " where id in ( " . $ids . " )";
        $db->sql($sql);
        $res = $db->getResult();
        foreach ($res as $image) {
            if (!empty($image['image']) && file_exists($path[$table] . $image['image'])) {
                unlink($path[$table] . $image['image']);
            }
        }
    }

    $sql = "DELETE FROM `" . $table . "` WHERE `id` in ( " . $ids . " ) ";
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 25. delete_junior_multiple
if (isset($_GET['delete_junior_multiple']) && $_GET['delete_junior_multiple'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $ids = $db->escapeString($_GET['ids']);
    $table = $db->escapeString($_GET['sec']);
    $is_image = $_GET['is_image'];

    if ($is_image) {
        $path = array(
            'junior_category' => 'images/category/',
            'junior_subcategory' => 'images/subcategory/',
            'junior_question' => 'images/questions/',
            'notifications' => 'images/notifications/',
            'contest' => 'images/contest/',
            'contest_questions' => 'images/contest-question/',
            'tbl_maths_question' => 'images/maths-question/',
        );

        $sql = "select `image` from " . $table . " where id in ( " . $ids . " )";
        $db->sql($sql);
        $res = $db->getResult();
        foreach ($res as $image) {
            if (!empty($image['image']) && file_exists($path[$table] . $image['image'])) {
                unlink($path[$table] . $image['image']);
            }
        }
    }

    $sql = "DELETE FROM `" . $table . "` WHERE `id` in ( " . $ids . " ) ";
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 26. add_language
if (isset($_POST['name']) && isset($_POST['add_language'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $name = $db->escapeString($_POST['name']);
    $sql = "SELECT `language` FROM `languages` WHERE `language`='" . $name . "'";
    $db->sql($sql);
    $language = $db->getResult();
    if (empty($language)) {
        $sql = "INSERT INTO `languages` (`language`,`status`) VALUES ('" . $name . "','1')";
        $db->sql($sql);
        echo '<label class="alert alert-success">Language created successfully!</label>';
    } else {
        echo '<label class="alert alert-danger">Language is already created</label>';
    }
}

// 27. update_language
if (isset($_POST['language_id']) && isset($_POST['update_language'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $language_id = $db->escapeString($_POST['language_id']);
    $name = $db->escapeString($_POST['name']);
    $status = $db->escapeString($_POST['status']);
    $sql = "UPDATE `languages` SET `language`='" . $name . "',`status`='" . $status . "' WHERE `id` = " . $language_id;
    if ($db->sql($sql)) {
        echo "<p class='alert alert-success'>Language updated successfully!</p>";
    } else {
        echo "<p class='alert alert-danger'>Language not updated!</p>";
    }
}

// 28. delete_language
if (isset($_GET['delete_language']) && $_GET['delete_language'] == '1') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $db->escapeString($_GET['id']);
    $sql = 'DELETE FROM `languages` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 30. update_about_us()
if (isset($_POST['update_about_us'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $message = $db->escapeString($_POST['message']);
    $sql = "select * from `settings` where `type`='about_us'";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $sql = "Update `settings` set `message`='" . $message . "' where `type`='about_us'";
    } else {
        $sql = "INSERT INTO `settings`(`type`, `message`, `status`) VALUES ('about_us','" . $message . "',1)";
    }

    $db->sql($sql);
    $res = $db->getResult();
    echo "<p class='alert alert-success'>About us updated successfully!</p><br>";
}

// 31. update_instructions()
if (isset($_POST['update_instructions'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $message = $db->escapeString($_POST['message']);
    $sql = "select * from `settings` where `type`='instructions'";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $sql = "Update `settings` set `message`='" . $message . "' where `type`='instructions'";
    } else {
        $sql = "INSERT INTO `settings`(`type`, `message`, `status`) VALUES ('instructions','" . $message . "',1)";
    }
    $db->sql($sql);
    $res = $db->getResult();

    echo "<p class='alert alert-success'>Instructions updated successfully!</p><br>";
}

// 32. update_daily_quiz_order
if (isset($_POST['question_ids']) && isset($_POST['update_daily_quiz_order']) && isset($_POST['language_id'])) {
    $language_id = $db->escapeString($_POST['language_id']);
    $question_ids = $db->escapeString($_POST['question_ids']);
    $date_published = $db->escapeString($_POST['daily_quiz_date']);

    $sql = "SELECT * FROM daily_quiz WHERE date_published = '$date_published' AND language_id='$language_id'";
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        $sql1 = "UPDATE daily_quiz SET `questions_id`='$question_ids',`language_id`='$language_id' WHERE `id`=" . $res[0]['id'];
    } else {
        $sql1 = "INSERT INTO `daily_quiz` (`language_id`,`questions_id`,`date_published`) VALUES ('$language_id','$question_ids',STR_TO_DATE('$date_published', '%Y-%m-%d'))";
    }
    $db->sql($sql1);
    echo "<p class='alert alert-success'> Saved </p>";
}
// 32. update_daily_quiz_order_junior
if (isset($_POST['question_ids']) && isset($_POST['update_daily_quiz_order_junior']) && isset($_POST['language_id'])) {
    $language_id = $db->escapeString($_POST['language_id']);
    $question_ids = $db->escapeString($_POST['question_ids']);
    $date_published = $db->escapeString($_POST['daily_quiz_date']);

    $sql = "SELECT * FROM daily_quiz_junior WHERE date_published = '$date_published' AND language_id='$language_id'";
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        $sql1 = "UPDATE daily_quiz_junior SET `questions_id`='$question_ids',`language_id`='$language_id' WHERE `id`=" . $res[0]['id'];
    } else {
        $sql1 = "INSERT INTO `daily_quiz_junior` (`language_id`,`questions_id`,`date_published`) VALUES ('$language_id','$question_ids',STR_TO_DATE('$date_published', '%Y-%m-%d'))";
    }
    $db->sql($sql1);
    echo "<p class='alert alert-success'> Saved </p>";
}

// 34. add_contest()
if (isset($_POST['name']) && isset($_POST['add_contest'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $name = $db->escapeString($_POST['name']);
    $start_date = $db->escapeString($_POST['start_date']);
    $end_date = $db->escapeString($_POST['end_date']);
    $description = $db->escapeString($_POST['description']);
    $entry = $db->escapeString($_POST['entry']);
    $contest_type = $db->escapeString($_POST['contest_type']);
    $status = 0;

    $file = explode(".", strtolower($_FILES["image"]["name"]));
    $extension = end($file);
    if (!(in_array($extension, $allowedExts))) {
        echo "<p class='alert alert-danger'>Image type is invalid!</p>";
        return false;
    }
    $target_path = 'images/contest/';
    if (!is_dir($target_path)) {
        mkdir($target_path, 0777, true);
    }

    $filename = microtime(true) . '.' . strtolower($extension);
    $full_path = $target_path . "" . $filename;
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
        echo "<p class='alert alert-danger'>Image type is invalid!</p>";
        return false;
    }

    $sql = "INSERT INTO `contest` (`name`, `start_date`, `end_date`, `description`, `image`, `entry`,`prize_status`, `date_created`,`status`,`contest_type`) VALUES
	('" . $name . "','" . $start_date . "','" . $end_date . "','" . $description . "','" . $filename . "','" . $entry . "','0','" . $toDateTime . "','" . $status . "','" . $contest_type . "')";

    $db->sql($sql);
    $insert_id = $db->insert_id();
    $points = implode(',', array_filter($_POST['points']));
    $points1 = explode(',', $points);
    $winner = $_POST['winner'];
    $count = count($points1);
    for ($i = 0; $i < $count; $i++) {
        $sql1 = "INSERT INTO `contest_prize` (`contest_id`, `top_winner`, `points`) VALUES
	('" . $insert_id . "','" . $winner[$i] . "','" . $points1[$i] . "')";

        $db->sql($sql1);
    }
    echo '<label class="alert alert-success">Contest created successfully!</label>';
}

// 35. delete_contest()
if (isset($_GET['delete_contest']) && $_GET['delete_contest'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `contest` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        /* delete questions */
        $sql = 'SELECT FROM `contest_questions` WHERE `contest_id`=' . $id;
        $db->sql($sql);
        $questions_images = $db->getResult();

        if (!empty($questions_images)) {
            foreach ($questions_images as $img) {
                if (!empty($img['image']) && file_exists('images/contest-question/' . $img['image'])) {
                    unlink('images/contest-question/' . $img['image']);
                }
            }
        }

        /* delete leaderboard */
        $sql = 'DELETE FROM `contest_leaderboard` WHERE `contest_id`=' . $id;
        $db->sql($sql);
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }
        echo 1;
    } else {
        echo 0;
    }
}

// 36. update_contest()
if (isset($_POST['contest_id']) && isset($_POST['update_contest'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['contest_id'];
    $name = $db->escapeString($_POST['name']);
    $description = $db->escapeString($_POST['description']);
    $start_date = $db->escapeString($_POST['start_date']);
    $end_date = $db->escapeString($_POST['end_date']);
    $entry = $db->escapeString($_POST['entry']);

    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);

        $file = explode(".", strtolower($_FILES["image"]["name"]));
        $extension = end($file);
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        $target_path = 'images/contest/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if (!empty($image_url) && file_exists($image_url)) {
            unlink($image_url);
        }

        $sql = "Update `contest` set `image`='" . $filename . "' where `id`=" . $id;
        $db->sql($sql);
    }

    $sql = "Update contest set `name`='" . $name . "', `description`='" . $description . "', `start_date`='" . $start_date . "', `end_date`='" . $end_date . "', `entry`='" . $entry . "' where `id`=" . $id;

    $db->sql($sql);
    echo "<p class='alert alert-success'>Contest updated successfully!</p>";
}

// 37. update_contest_status()
if (isset($_POST['update_id']) && isset($_POST['update_contest_status'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['update_id'];
    $status = $db->escapeString($_POST['status']);

    $sql = 'SELECT *  FROM `contest_questions` WHERE `contest_id`=' . $id;
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        $sql = "UPDATE `contest` SET `status`='" . $status . "' WHERE `id`=" . $id;
        $db->sql($sql);
        echo "<p class='alert alert-success'>Status updated successfully!</p>";
    } else {
        echo "<p class='alert alert-danger'>No enought question for active</p>";
    }
}

// 38. add_contest_prize()
if (isset($_POST['contest_id']) && isset($_POST['add_contest_prize'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $contest_id = $db->escapeString($_POST['contest_id']);
    $points = $db->escapeString($_POST['points']);
    $winner = $db->escapeString($_POST['winner']);

    $sql = "INSERT INTO `contest_prize` (`contest_id`, `top_winner`, `points`) VALUES ('" . $contest_id . "','" . $winner . "','" . $points . "')";
    $db->sql($sql);

    echo '<label class="alert alert-success">Prize created successfully!</label>';
}

// 39. update_contest_prize()
if (isset($_POST['prize_id']) && isset($_POST['update_contest_prize'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['prize_id'];
    $points = $db->escapeString($_POST['points']);
    $winner = $db->escapeString($_POST['winner']);

    $sql = "Update `contest_prize` set `points`='" . $points . "' where `id`=" . $id;
    $db->sql($sql);

    echo "<p class='alert alert-success'>Prize updated successfully!</p>";
}

// 40. delete_contest_prize()
if (isset($_GET['delete_contest_prize']) && $_GET['delete_contest_prize'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];

    $sql = 'DELETE FROM `contest_prize` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 41. add_contest_question()
if (isset($_POST['question']) && isset($_POST['add_contest_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $question = $db->escapeString($_POST['question']);
    $contest_id = $db->escapeString($_POST['contest_id']);
    $question_type = $db->escapeString($_POST['question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    $e = ($fn->is_option_e_mode_enabled()) ? (($question_type == 1) ? $db->escapeString($_POST['e']) : "") : "";
    $answer = $db->escapeString($_POST['answer']);
    $note = $db->escapeString($_POST['note']);

    $filename = '';
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $target_path = 'images/contest-question/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }
    $sql = "INSERT INTO `contest_questions`(`contest_id`,`image` , `question`, `question_type`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `answer`,`note`) VALUES ('" . $contest_id . "','" . $filename . "','" . $question . "','" . $question_type . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $answer . "','" . $note . "')";

    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Question created successfully!</label>';
}

// 42. update_contest_question()
if (isset($_POST['question_id']) && isset($_POST['update_contest_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['question_id'];
    $question = $db->escapeString($_POST['question']);
    $quiz_id = $db->escapeString($_POST['contest_id']);
    $question_type = $db->escapeString($_POST['edit_question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    if ($fn->is_option_e_mode_enabled()) {
        $e = ($question_type == 1) ? $db->escapeString($_POST['e']) : "";
    }
    $answer = $db->escapeString($_POST['answer']);
    $update_note = $db->escapeString($_POST['edit_note']);

    if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
        $target_path = 'images/contest-question/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777);
        }
        //image isn't empty and update the image
        $image_url = $db->escapeString($_POST['image_url']);

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            echo '<p class="alert alert-danger">Image type is invalid</p>';
            return false;
        }
        if (!empty($image_url) && file_exists($image_url)) {
            unlink($image_url);
        }

        $sql = "Update category set `image`='" . $filename . "' where `id`=" . $id;
        $db->sql($sql);
    }
    $sql = "Update `contest_questions` set `question`='" . $question . "', `contest_id`='" . $quiz_id . "',`question_type`='" . $question_type . "',`optiona`='" . $a . "',`optionb`='" . $b . "' ,`optionc`='" . $c . "' ,`optiond`='" . $d . "',`answer`='" . $answer . "',`note`='" . $update_note . "'";
    $sql .= ($fn->is_option_e_mode_enabled()) ? ",`optione`='" . $e . "'" : "";
    $sql .= " where `id`=" . $id;
    $db->sql($sql);
    echo "<p class='alert alert-success'>Question updated successfully!</p>";
}

// 43. delete_contest_question()
if (isset($_GET['delete_contest_question']) && $_GET['delete_contest_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `contest_questions` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }
        echo 1;
    } else {
        echo 0;
    }
}

// 44. import_contest_questions() - import questions to database from a CSV file
if (isset($_POST['import_contest_questions']) && $_POST['import_contest_questions'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $count = $count1 = 0;
    $filename = $_FILES["questions_file"]["tmp_name"];
    $file_extension = pathinfo($_FILES["questions_file"]["name"], PATHINFO_EXTENSION);
    if ($_FILES["questions_file"]["size"] > 0 && $file_extension == "csv") {
        $file = fopen($filename, "r");

        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if (count($emapData) > 2) {
                $emapData[0] = $db->escapeString($emapData[0]); //contest_id
                $emapData[1] = $db->escapeString(trim($emapData[1]));   //question_type
                $emapData[2] = $db->escapeString($emapData[2]);     //question
                $emapData[3] = $db->escapeString($emapData[3]);    // optiona
                $emapData[4] = $db->escapeString($emapData[4]);    // optionb
                $emapData[5] = $db->escapeString($emapData[5]);    // optionc
                $emapData[6] = $db->escapeString($emapData[6]);    // optiond
                $emapData[7] = (empty($db->escapeString($emapData[7]))) ? "" : $db->escapeString($emapData[7]);  // optione
                $emapData[8] = $db->escapeString(trim($emapData[8]));  //answer
                $emapData[9] = $db->escapeString($emapData[9]);       //note
                $count++;
                if ($count > 1) {
                    if ($emapData[1] == '1') {
                        if (!empty($emapData[0]) && !empty($emapData[1]) && !empty($emapData[2]) && $emapData[3] != '' && $emapData[4] != '' && $emapData[5] != '' && $emapData[6] != '' && $emapData[8] != '') {
                            $empty_value_found = true;
                        } else {
                            $empty_value_found = false;
                            echo '<p class="text-danger">Please Check ' . $count . ' row</p>';
                            break;
                        }
                    } else if ($emapData[1] == '2') {
                        if (!empty($emapData[0]) && !empty($emapData[1]) && !empty($emapData[2]) && $emapData[3] != '' && $emapData[4] != '' && $emapData[8] != '') {
                            $empty_value_found = true;
                        } else {
                            $empty_value_found = false;
                            echo '<p class="text-danger">Please Check ' . $count . ' row</p>';
                            break;
                        }
                    } else {
                        $empty_value_found = false;
                        break;
                    }
                }
            }
        }
        fclose($file);
        if ($empty_value_found == TRUE) {
            $file = fopen($filename, "r");
            while (($emapData1 = fgetcsv($file, 10000, ",")) !== FALSE) {
                if (count($emapData1) > 2) {
                    $emapData1[0] = $db->escapeString($emapData1[0]); //contest_id
                    $emapData1[1] = $db->escapeString(trim($emapData1[1]));   //question_type
                    $emapData1[2] = $db->escapeString($emapData1[2]);     //question
                    $emapData1[3] = $db->escapeString($emapData1[3]);    // optiona
                    $emapData1[4] = $db->escapeString($emapData1[4]);    // optionb
                    $emapData1[5] = $db->escapeString($emapData1[5]);    // optionc
                    $emapData1[6] = $db->escapeString($emapData1[6]);    // optiond
                    $emapData1[7] = (empty($db->escapeString($emapData1[7]))) ? "" : $db->escapeString($emapData1[7]);  // optione
                    $emapData1[8] = $db->escapeString(trim($emapData1[8]));  //answer
                    $emapData1[9] = $db->escapeString($emapData1[9]);       //note
                    $count1++;
                    if ($count1 > 1) {
                        if (count($emapData1) > 2) {
                            $sql = "INSERT INTO `contest_questions`(`contest_id`, `image`, `question_type`, `question`,`optiona`, `optionb`, `optionc`, `optiond`,  `optione`, `answer`, `note`) VALUES 
						('$emapData1[0]','','$emapData1[1]','$emapData1[2]','$emapData1[3]','$emapData1[4]','$emapData1[5]','$emapData1[6]','$emapData1[7]','$emapData1[8]','$emapData1[9]')";
                            $db->sql($sql);
                        }
                    }
                }
            }
            fclose($file);
            echo "<p class='alert alert-success'>CSV file is successfully imported!</p>";
        } else {
            echo "<p class='alert alert-danger'>Please fill all the data in CSV file!</p>";
        }
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p>";
    }
}

// 45. battle_settings()
if (isset($_POST['web_firebase_settings']) && isset($_POST['databaseURL'])) {

    $setting = [
        'apiKey',
        'authDomain',
        'databaseURL',
        'projectId',
        'storageBucket',
        'messagingSenderId',
        'appId',
        'client_id_google',
        'app_id_fb'
    ];
    foreach ($setting as $row) {
        $sql = "SELECT * FROM settings WHERE type='" . $row . "' LIMIT 1";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $sql1 = "UPDATE settings SET message='" . $_POST[$row] . "' WHERE type='" . $row . "' ";
        } else {
            $sql1 = "INSERT INTO settings (type,message,status) VALUES ('" . $row . "','" . $_POST[$row] . "','1')";
        }
        $db->sql($sql1);
    }

    echo "<p class='alert alert-success'>Settings Saved!</p>";
}

// 46. add_learning
if (isset($_POST['title']) && isset($_POST['add_learning'])) {
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $category = $db->escapeString($_POST['category']);
    $title = $db->escapeString($_POST['title']);
    $video_id = ($db->escapeString($_POST['video_id'])) ? $db->escapeString($_POST['video_id']) : '';
    $detail = $db->escapeString($_POST['detail']);
    $filename = "";
    if ($_FILES['pdf_file']['error'] == 0 && $_FILES['pdf_file']['size'] > 0) {
        // Define allowed file types
        $allowedTypes = ['pdf'];

        // Get the file extension
        $extension = pathinfo($_FILES["pdf_file"]["name"], PATHINFO_EXTENSION);

        // Check if the file extension is not in the allowed types
        if (!in_array(strtolower($extension), $allowedTypes)) {

            echo "<label class='alert alert-danger'>Invalid file type. Only PDF files are allowed.</label>";
            return false;
        }

        // Rest of your code for file handling
        if (!is_dir('pdf_files')) {
            mkdir('pdf_files', 0777, true);
        }
        $target_path = 'pdf_files/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . $filename;
        if (!move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Failed to upload the file.';
            echo json_encode($response);
            return false;
        }
    }

    $sql = "INSERT INTO `tbl_learning` ( `category`, `language_id`, `title`, `video_id`, `detail`,pdf_file,`status`) VALUES ('" . $category . "','" . $language_id . "','" . $title . "','" . $video_id . "','" . $detail . "','" . $filename . "','0')";
    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Learning created successfully!</label>';
}
// 46. add_junior_learning
if (isset($_POST['title']) && isset($_POST['add_junior_learning'])) {
    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $category = $db->escapeString($_POST['category']);
    $title = $db->escapeString($_POST['title']);
    $video_id = ($db->escapeString($_POST['video_id'])) ? $db->escapeString($_POST['video_id']) : '';
    $detail = $db->escapeString($_POST['detail']);
    $filename = "";
    if ($_FILES['pdf_file']['error'] == 0 && $_FILES['pdf_file']['size'] > 0) {
        // Define allowed file types
        $allowedTypes = ['pdf'];

        // Get the file extension
        $extension = pathinfo($_FILES["pdf_file"]["name"], PATHINFO_EXTENSION);

        // Check if the file extension is not in the allowed types
        if (!in_array(strtolower($extension), $allowedTypes)) {

            echo "<label class='alert alert-danger'>Invalid file type. Only PDF files are allowed.</label>";
            return false;
        }

        // Rest of your code for file handling
        if (!is_dir('pdf_files')) {
            mkdir('pdf_files', 0777, true);
        }
        $target_path = 'pdf_files/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . $filename;
        if (!move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Failed to upload the file.';
            echo json_encode($response);
            return false;
        }
    }

    $sql = "INSERT INTO `tbl_junior_learning` ( `category`, `language_id`, `title`, `video_id`, `detail`,pdf_file,`status`) VALUES ('" . $category . "','" . $language_id . "','" . $title . "','" . $video_id . "','" . $detail . "','" . $filename . "','0')";
    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Learning created successfully!</label>';
}

// 47. update_question
if (isset($_POST['learning_id']) && isset($_POST['update_learning'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['learning_id'];

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $category = $db->escapeString($_POST['category']);
    $title = $db->escapeString($_POST['title']);
    $video_id = ($db->escapeString($_POST['video_id'])) ? $db->escapeString($_POST['video_id']) : '';
    $detail = $db->escapeString($_POST['detail']);

    $filename = "";





    if ($_FILES['edit_pdf_file']['error'] == 0 && $_FILES['edit_pdf_file']['size'] > 0) {

        if (!is_dir('pdf_files')) {
            mkdir('pdf_files', 0777, true);
        }
        $extension = pathinfo($_FILES["edit_pdf_file"]["name"])['extension'];




        $allowedTypes = ['pdf'];

        // Get the file extension
        $extension = pathinfo($_FILES["edit_pdf_file"]["name"], PATHINFO_EXTENSION);

        // Check if the file extension is not in the allowed types
        if (!in_array(strtolower($extension), $allowedTypes)) {

            echo "<label class='alert alert-danger'>Invalid file type. Only PDF files are allowed.</label>";
            return false;
        }

        if (!(in_array($extension, $allowedType))) {
            $response['error'] = true;
            $response['message'] = 'type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'pdf_files/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["edit_pdf_file"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $image_url = $db->escapeString($_POST['edit_pdf']);


        if (file_exists($image_url)) {

            unlink($image_url);
        }
    }
    $sql = "Update `tbl_learning` set `category`='" . $category . "', `title`='" . $title . "', `video_id`='" . $video_id . "', `detail`='" . $detail . "',`pdf_file`='" . $filename . "'";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id`=" . $language_id : "";
    $sql .= " where `id`=" . $id;
    $db->sql($sql);

    echo "<p class='alert alert-success'>Learning updated successfully!</p>";
}

// 47. update_junior_question
if (isset($_POST['learning_id']) && isset($_POST['update_junior_learning'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['learning_id'];

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $category = $db->escapeString($_POST['category']);
    $title = $db->escapeString($_POST['title']);
    $video_id = ($db->escapeString($_POST['video_id'])) ? $db->escapeString($_POST['video_id']) : '';
    $detail = $db->escapeString($_POST['detail']);

    $filename = "";





    if ($_FILES['edit_pdf_file']['error'] == 0 && $_FILES['edit_pdf_file']['size'] > 0) {

        if (!is_dir('pdf_files')) {
            mkdir('pdf_files', 0777, true);
        }
        $extension = pathinfo($_FILES["edit_pdf_file"]["name"])['extension'];




        $allowedTypes = ['pdf'];

        // Get the file extension
        $extension = pathinfo($_FILES["edit_pdf_file"]["name"], PATHINFO_EXTENSION);

        // Check if the file extension is not in the allowed types
        if (!in_array(strtolower($extension), $allowedTypes)) {

            echo "<label class='alert alert-danger'>Invalid file type. Only PDF files are allowed.</label>";
            return false;
        }

        if (!(in_array($extension, $allowedType))) {
            $response['error'] = true;
            $response['message'] = 'type is invalid';
            echo json_encode($response);
            return false;
        }
        $target_path = 'pdf_files/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["edit_pdf_file"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $image_url = $db->escapeString($_POST['edit_pdf']);


        if (file_exists($image_url)) {

            unlink($image_url);
        }
    }
    $sql = "Update `tbl_junior_learning` set `category`='" . $category . "', `title`='" . $title . "', `video_id`='" . $video_id . "', `detail`='" . $detail . "',`pdf_file`='" . $filename . "'";
    $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id`=" . $language_id : "";
    $sql .= " where `id`=" . $id;
    $db->sql($sql);

    echo "<p class='alert alert-success'>Learning updated successfully!</p>";
}


// 48. update_learning_status
if (isset($_POST['learning_status_id']) && isset($_POST['update_learning_status'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['learning_status_id'];
    $status = $db->escapeString($_POST['status']);
    if ($status == 1 || $status == '1') {
        $sql = 'SELECT id FROM `tbl_learning_question` WHERE `learning_id`=' . $id;
        $db->sql($sql);
        $res = $db->getResult();
        if (empty($res)) {
            echo "<p class='alert alert-danger'>No enought question for active Learning!</p>";
        } else {
            $sql = "Update `tbl_learning` set `status`='" . $status . "' where `id`=" . $id;
            $db->sql($sql);
            echo "<p class='alert alert-success'>Learning status updated successfully!</p>";
        }
    } else {
        $sql = "Update `tbl_learning` set `status`='" . $status . "' where `id`=" . $id;
        $db->sql($sql);
        echo "<p class='alert alert-success'>Learning status updated successfully!</p>";
    }
}

// 48. update_junior_learning_status
if (isset($_POST['learning_status_id']) && isset($_POST['update_junior_learning_status'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['learning_status_id'];
    $status = $db->escapeString($_POST['status']);
    if ($status == 1 || $status == '1') {
        $sql = 'SELECT id FROM `tbl_junior_learning_question` WHERE `learning_id`=' . $id;
        $db->sql($sql);
        $res = $db->getResult();
        if (empty($res)) {
            echo "<p class='alert alert-danger'>No enought question for active Learning!</p>";
        } else {
            $sql = "Update `tbl_junior_learning` set `status`='" . $status . "' where `id`=" . $id;
            $db->sql($sql);
            echo "<p class='alert alert-success'>Learning status updated successfully!</p>";
        }
    } else {
        $sql = "Update `tbl_junior_learning` set `status`='" . $status . "' where `id`=" . $id;
        $db->sql($sql);
        echo "<p class='alert alert-success'>Learning status updated successfully!</p>";
    }
}


// 49. delete_question
if (isset($_GET['delete_learning']) && $_GET['delete_learning'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $pdf = $_GET['pdf'];


    $sql = 'DELETE FROM `tbl_learning` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        $sql = 'DELETE FROM `tbl_learning_question` WHERE `learning_id`=' . $id;
        $db->sql($sql);
        if (file_exists($pdf)) {

            unlink($pdf);
        }

        echo 1;
    } else {
        echo 0;
    }
}
// 49. delete_junior_question
if (isset($_GET['delete_junior_learning']) && $_GET['delete_junior_learning'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $pdf = $_GET['pdf'];


    $sql = 'DELETE FROM `tbl_junior_learning` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        $sql = 'DELETE FROM `tbl_junior_learning_question` WHERE `learning_id`=' . $id;
        $db->sql($sql);
        if (file_exists($pdf)) {

            unlink($pdf);
        }

        echo 1;
    } else {
        echo 0;
    }
}


// 50. add_learning_question
if (isset($_POST['question']) && isset($_POST['add_learning_question'])) {
    $question = $db->escapeString($_POST['question']);
    $learning_id = $db->escapeString($_POST['learning_id']);

    $question_type = $db->escapeString($_POST['question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    $e = ($fn->is_option_e_mode_enabled()) ? (($question_type == 1) ? $db->escapeString($_POST['e']) : "") : "";
    $answer = $db->escapeString($_POST['answer']);

    $sql = "INSERT INTO `tbl_learning_question`(`learning_id`, `question`, `question_type`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `answer`) VALUES 
	('" . $learning_id . "','" . $question . "','" . $question_type . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $answer . "')";

    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Question created successfully!</label>';
}
// 50. add_junior_learning_question
if (isset($_POST['question']) && isset($_POST['add_junior_learning_question'])) {
    $question = $db->escapeString($_POST['question']);
    $learning_id = $db->escapeString($_POST['learning_id']);

    $question_type = $db->escapeString($_POST['question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    $e = ($fn->is_option_e_mode_enabled()) ? (($question_type == 1) ? $db->escapeString($_POST['e']) : "") : "";
    $answer = $db->escapeString($_POST['answer']);

    $sql = "INSERT INTO `tbl_junior_learning_question`(`learning_id`, `question`, `question_type`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `answer`) VALUES 
	('" . $learning_id . "','" . $question . "','" . $question_type . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $answer . "')";

    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Question created successfully!</label>';
}


// 51. update_learning_question
if (isset($_POST['question_id']) && isset($_POST['update_learning_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['question_id'];
    $question = $db->escapeString($_POST['question']);
    $question_type = $db->escapeString($_POST['edit_question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    if ($fn->is_option_e_mode_enabled()) {
        $e = ($question_type == 1) ? $db->escapeString($_POST['e']) : "";
    }
    $answer = $db->escapeString($_POST['answer']);
    $sql = "UPDATE `tbl_learning_question` set `question`='" . $question . "',`question_type`='" . $question_type . "',`optiona`='" . $a . "',`optionb`='" . $b . "' ,`optionc`='" . $c . "' ,`optiond`='" . $d . "', `answer`='" . $answer . "'";
    $sql .= ($fn->is_option_e_mode_enabled()) ? ",`optione`='" . $e . "'" : "";
    $sql .= " WHERE `id`=" . $id;
    $db->sql($sql);

    echo "<p class='alert alert-success'>Question updated successfully!</p>";
}

// 51. update_junior_learning_question
if (isset($_POST['question_id']) && isset($_POST['update_junior_learning_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['question_id'];
    $question = $db->escapeString($_POST['question']);
    $question_type = $db->escapeString($_POST['edit_question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    if ($fn->is_option_e_mode_enabled()) {
        $e = ($question_type == 1) ? $db->escapeString($_POST['e']) : "";
    }
    $answer = $db->escapeString($_POST['answer']);
    $sql = "UPDATE `tbl_junior_learning_question` set `question`='" . $question . "',`question_type`='" . $question_type . "',`optiona`='" . $a . "',`optionb`='" . $b . "' ,`optionc`='" . $c . "' ,`optiond`='" . $d . "', `answer`='" . $answer . "'";
    $sql .= ($fn->is_option_e_mode_enabled()) ? ",`optione`='" . $e . "'" : "";
    $sql .= " WHERE `id`=" . $id;
    $db->sql($sql);

    echo "<p class='alert alert-success'>Question updated successfully!</p>";
}



// 52. delete_learning_question
if (isset($_GET['delete_learning_question']) && $_GET['delete_learning_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];

    $sql = 'DELETE FROM `tbl_learning_question` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 52. delete_junior_learning_question
if (isset($_GET['delete_junior_learning_question']) && $_GET['delete_junior_learning_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];

    $sql = 'DELETE FROM `tbl_junior_learning_question` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

// 53. add_maths_question()
if (isset($_POST['question']) && isset($_POST['add_maths_question'])) {
    $question = $db->escapeString($_POST['question']);

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $category = $db->escapeString($_POST['category']);
    $subcategory = (empty($_POST['subcategory'])) ? 0 : $db->escapeString($_POST['subcategory']);

    $question_type = $db->escapeString($_POST['question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    $e = ($fn->is_option_e_mode_enabled()) ? (($question_type == 1) ? $db->escapeString($_POST['e']) : "") : "";
    $answer = $db->escapeString($_POST['answer']);
    $note = $db->escapeString($_POST['note']);

    $filename = $full_path = '';

    if (isset($_POST['question_id'])) {
        $id = $_POST['question_id'];

        if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
            $target_path = 'images/maths-question/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }

            //image isn't empty and update the image
            $image_url = $db->escapeString($_POST['image_url']);

            // common image file extensions
            $extension = pathinfo($_FILES["image"]["name"])['extension'];
            if (!(in_array($extension, $allowedExts))) {
                echo '<p class="alert alert-danger">Image type is invalid</p>';
                return false;
            }
            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = $target_path . "" . $filename;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
                echo '<p class="alert alert-danger">Image type is invalid</p>';
                return false;
            }
            if (!empty($image_url) && file_exists($image_url)) {
                unlink($image_url);
            }
            $sql = "UPDATE `tbl_maths_question` SET `image`='" . $filename . "' WHERE `id`=" . $id;
            $db->sql($sql);
        }
        $sql = "UPDATE `tbl_maths_question` SET `question`='" . $question . "', `category`='" . $category . "', `subcategory`='" . $subcategory . "',`question_type`='" . $question_type . "',`optiona`='" . $a . "',`optionb`='" . $b . "' ,`optionc`='" . $c . "' ,`optiond`='" . $d . "', `answer`='" . $answer . "', `note`='" . $note . "'";
        $sql .= ($fn->is_option_e_mode_enabled()) ? ",`optione`='" . $e . "'" : "";
        $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id`=" . $language_id : "";
        $sql .= " WHERE `id`=" . $id;
        if ($db->sql($sql)) {
            // Return a JSON response with a success message and redirect URL
            echo json_encode(['success' => true, 'redirect' => 'maths-questions-view.php']);
        } else {
            // Return a JSON response with an error message
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }
        exit();
        exit();
    } else {
        // common image file extensions
        if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
            $target_path = 'images/maths-question/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }

            $extension = pathinfo($_FILES["image"]["name"])['extension'];
            if (!(in_array($extension, $allowedExts))) {
                $response['error'] = true;
                $response['message'] = 'Image type is invalid';
                echo json_encode($response);
                return false;
            }

            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = $target_path . "" . $filename;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
                $response['error'] = true;
                $response['message'] = 'Image type is invalid';
                echo json_encode($response);
                return false;
            }
        }

        $sql = "INSERT INTO `tbl_maths_question` (`category`, `subcategory`, `language_id`, `image`, `question`, `question_type`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `answer`, `note`) VALUES 
        ('" . $category . "','" . $subcategory . "','" . $language_id . "','" . $filename . "','" . $question . "','" . $question_type . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $answer . "','" . $note . "')";

        if ($db->sql($sql)) {
            // Return a JSON response with a success message and redirect URL
            echo json_encode(['success' => true, 'redirect' => 'maths-questions-view.php']);
        } else {
            // Return a JSON response with an error message
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }
        exit();
    }
    // echo $sql;
    // echo '<label class="alert alert-success">Question created successfully!</label>';
}

// 53. add_maths_junior_question()
if (isset($_POST['question']) && isset($_POST['add_maths_junior_question'])) {
    $question = $db->escapeString($_POST['question']);

    $language_id = ($fn->is_language_mode_enabled()) ? $db->escapeString($_POST['language_id']) : 0;
    $category = $db->escapeString($_POST['category']);
    $subcategory = (empty($_POST['subcategory'])) ? 0 : $db->escapeString($_POST['subcategory']);

    $question_type = $db->escapeString($_POST['question_type']);

    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = ($question_type == 1) ? $db->escapeString($_POST['c']) : "";
    $d = ($question_type == 1) ? $db->escapeString($_POST['d']) : "";
    $e = ($fn->is_option_e_mode_enabled()) ? (($question_type == 1) ? $db->escapeString($_POST['e']) : "") : "";
    $answer = $db->escapeString($_POST['answer']);
    $note = $db->escapeString($_POST['note']);

    $filename = $full_path = '';

    if (isset($_POST['question_id'])) {
        $id = $_POST['question_id'];

        if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0) {
            $target_path = 'images/maths-question/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }

            //image isn't empty and update the image
            $image_url = $db->escapeString($_POST['image_url']);

            // common image file extensions
            $extension = pathinfo($_FILES["image"]["name"])['extension'];
            if (!(in_array($extension, $allowedExts))) {
                echo '<p class="alert alert-danger">Image type is invalid</p>';
                return false;
            }
            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = $target_path . "" . $filename;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
                echo '<p class="alert alert-danger">Image type is invalid</p>';
                return false;
            }
            if (!empty($image_url) && file_exists($image_url)) {
                unlink($image_url);
            }
            $sql = "UPDATE `tbl_maths_junior_question` SET `image`='" . $filename . "' WHERE `id`=" . $id;
            $db->sql($sql);
        }
        $sql = "UPDATE `tbl_maths_junior_question` SET `question`='" . $question . "', `category`='" . $category . "', `subcategory`='" . $subcategory . "',`question_type`='" . $question_type . "',`optiona`='" . $a . "',`optionb`='" . $b . "' ,`optionc`='" . $c . "' ,`optiond`='" . $d . "', `answer`='" . $answer . "', `note`='" . $note . "'";
        $sql .= ($fn->is_option_e_mode_enabled()) ? ",`optione`='" . $e . "'" : "";
        $sql .= ($fn->is_language_mode_enabled()) ? ", `language_id`=" . $language_id : "";
        $sql .= " WHERE `id`=" . $id;
        $db->sql($sql);
        header("location:maths-junior-questions-view.php");
        exit();
    } else {
        // common image file extensions
        if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
            $target_path = 'images/maths-question/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }

            $extension = pathinfo($_FILES["image"]["name"])['extension'];
            if (!(in_array($extension, $allowedExts))) {
                $response['error'] = true;
                $response['message'] = 'Image type is invalid';
                echo json_encode($response);
                return false;
            }

            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = $target_path . "" . $filename;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
                $response['error'] = true;
                $response['message'] = 'Image type is invalid';
                echo json_encode($response);
                return false;
            }
        }

        $sql = "INSERT INTO `tbl_maths_junior_question` (`category`, `subcategory`, `language_id`, `image`, `question`, `question_type`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `answer`, `note`) VALUES 
        ('" . $category . "','" . $subcategory . "','" . $language_id . "','" . $filename . "','" . $question . "','" . $question_type . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $answer . "','" . $note . "')";

        // $db->sql($sql);
        // $res = $db->getResult();
        if ($db->sql($sql)) {
            // Return a JSON response with a success message and redirect URL
            echo json_encode(['success' => true, 'redirect' => 'maths-junior-questions-view.php']);
        } else {
            // Return a JSON response with an error message
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }
        exit();
    }
    // echo $sql;
    // echo '<label class="alert alert-success">Question created successfully!</label>';
}


// 54. delete_maths_question
if (isset($_GET['delete_maths_question']) && $_GET['delete_maths_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `tbl_maths_question` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }
        echo 1;
    } else {
        echo 0;
    }
}
// 54. delete_junior_maths_question
if (isset($_GET['delete_maths_junior_question']) && $_GET['delete_maths_junior_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_GET['id'];
    $image = $_GET['image'];

    $sql = 'DELETE FROM `tbl_maths_junior_question` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        if (!empty($image) && file_exists($image)) {
            unlink($image);
        }
        echo 1;
    } else {
        echo 0;
    }
}

// 53. update_system()
if (isset($_POST['update_system'])) {

    if (isset($_POST['purchase_code']) && isset($_POST['quiz_url'])) {
        if (!empty($_POST['purchase_code']) && !empty($_POST['quiz_url'])) {
            $purchase_code = $db->escapeString($_POST['purchase_code']);
            $quiz_url = $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://wrteam.in/validator/quiz_online_validator?purchase_code=' . $purchase_code . '&domain_url=' . $quiz_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl);
            $response = json_decode($response, 1);
            curl_close($curl);
            if ($response["error"] == false) {
                if ($_FILES['file']['error'] == 0 && $_FILES['file']['size'] > 0) {
                    $target_path = getcwd() . DIRECTORY_SEPARATOR;
                    if (!is_dir('tmp')) {
                        mkdir('tmp', 0777, true);
                    }
                    $allowedExts = array("zip", "ZIP", "rar", "RAR", "7zip", "7ZIP");
                    $extension = pathinfo($_FILES["file"]["name"])['extension'];
                    if ((in_array($extension, $allowedExts))) {
                        $target_path1 = $target_path . '/tmp';
                        $filePath = $target_path . '/' . $_FILES["file"]["name"];
                        $filePath1 = $target_path1 . $_FILES["file"]["name"];
                        if (move_uploaded_file($_FILES["file"]["tmp_name"], $filePath1)) {
                            $zip = new ZipArchive();
                            $zipFile = $zip->open($filePath1);
                            if ($zipFile === true) {
                                $zip->extractTo($target_path1);
                                $zip->close();

                                unlink($filePath1);

                                $ver_file1 = $target_path1 . '/version_info.php';
                                $source_path1 = $target_path1 . '/source_code.zip';
                                $sql_file1 = $target_path1 . '/database.sql';
                                if (file_exists($ver_file1) && file_exists($source_path1) && file_exists($sql_file1)) {
                                    $ver_file = $target_path . '/version_info.php';
                                    $source_path = $target_path . '/source_code.zip';
                                    $sql_file = $target_path . '/database.sql';
                                    if (rename($ver_file1, $ver_file) && rename($source_path1, $source_path) && rename($sql_file1, $sql_file)) {
                                        DeleteDir($target_path1);

                                        $version_file = require_once($ver_file);
                                        $db->sql("select * from `settings` where type='quiz_version'");
                                        $res = $db->getResult();
                                        $current_version = (!empty($res)) ? $res[0]['message'] : '';

                                        if ($current_version == $version_file['current_version']) {
                                            $zip1 = new ZipArchive();
                                            $zipFile1 = $zip1->open($source_path);
                                            if ($zipFile1 === true) {
                                                $zip1->extractTo($target_path);
                                                $zip1->close();
                                                if (file_exists($sql_file)) {
                                                    $lines = file($sql_file);
                                                    for ($i = 0; $i < count($lines); $i++) {
                                                        if (!empty($lines[$i])) {
                                                            $db->sql($lines[$i]);
                                                        }
                                                    }
                                                }
                                                unlink($source_path);
                                                unlink($ver_file);
                                                unlink($sql_file);
                                                $db->sql("UPDATE settings SET message='" . $version_file['update_version'] . "' WHERE type='quiz_version'");
                                                $result = '<label class="alert alert-success">System update successfully.!</label>';
                                            } else {
                                                unlink($source_path);
                                                unlink($ver_file);
                                                unlink($sql_file);
                                                DeleteDir($target_path1);
                                                $result = "<label class='alert alert-danger'>Something wrong, please try again.!<lable>";
                                            }
                                        } else if ($current_version == $version_file['update_version']) {
                                            unlink($source_path);
                                            unlink($ver_file);
                                            unlink($sql_file);
                                            DeleteDir($target_path1);
                                            $result = "<label class='alert alert-danger'>System is already updated.!<lable>";
                                        } else {
                                            unlink($source_path);
                                            unlink($ver_file);
                                            unlink($sql_file);
                                            DeleteDir($target_path1);
                                            $result = "<label class='alert alert-danger'>Your version is $current_version, Please update nearest version first.<lable>";
                                        } //                                
                                    } else {
                                        DeleteDir($target_path1);
                                        $result = "<label class='alert alert-danger'>Invalid file, please try again.!<lable>";
                                    }
                                } else {
                                    DeleteDir($target_path1);
                                    $result = "<label class='alert alert-danger'>Invalid file, please try again.!<lable>";
                                }
                            } else {
                                DeleteDir($target_path1);
                                $result = "<label class='alert alert-danger'>Something wrong, please try again.!<lable>";
                            }
                        } else {
                            $result = "<label class='alert alert-danger'>file type is invalid, Only zip allow.!<lable>";
                        }
                    } else {
                        $result = "<label class='alert alert-danger'>file type is invalid, Only zip allow.!<lable>";
                    }
                } else {
                    $result = "<label class='alert alert-danger'>Only zip allow, please try again.!<lable>";
                }
            } else {
                $result = "<label class='alert alert-danger'>" . $response["message"] . "</lable>";
            }
        } else {
            $result = "<label class='alert alert-danger'>Purchase code required </lable>";
        }
    } else {
        $result = "<label class='alert alert-danger'>Purchase code required </lable>";
    }
    echo $result;
}
//54.add_coin
if (isset($_POST['user']) && isset($_POST['add_coin'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $id = $_POST['user'];
    $get_coin = $_POST['coin'];
    $coin = $db->escapeString($_POST['add_coin']);


    $total_coin =  $coin + $get_coin;

    $sql = "Update users set `coins`='" . $total_coin . "' where `id`=" . $id;
    $db->sql($sql);
    $res = $db->getResult();


    echo "<p class='alert alert-success'>Coins given to user!</p>";
}
function DeleteDir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    $dir_sec = $dir . "/" . $object;
                    if (is_dir($dir_sec)) {
                        rmdir($dir_sec);
                    }
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rmdir($dir);
    }
}












// 50. update_category_status
if (isset($_POST['category_status_id']) && isset($_POST['update_category_status'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['category_status_id'];
    $status = $db->escapeString($_POST['status']);

    $sql = "Update `category` set `status`='" . $status . "' where `id`=" . $id;
    $db->sql($sql);
    echo "<p class='alert alert-success'>Category status updated successfully!</p>";
}
// 50. update_junior_category_status
if (isset($_POST['category_status_id']) && isset($_POST['update_junior_category_status'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['category_status_id'];
    $status = $db->escapeString($_POST['status']);

    $sql = "Update `junior_category` set `status`='" . $status . "' where `id`=" . $id;
    $db->sql($sql);
    echo "<p class='alert alert-success'>Category status updated successfully!</p>";
}


// 51. add exam module
if (isset($_POST['title']) && isset($_POST['add_exam_module'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $title = $db->escapeString($_POST['title']);
    $date = $db->escapeString($_POST['date']);
    $language = $db->escapeString($_POST['language']);
    $key = $db->escapeString($_POST['key']);
    $duration = $db->escapeString($_POST['duration']);
    $status = 0;




    $sql = "INSERT INTO `exam_module` (`title`, `date`, `language_id`, `exam_key`, `duration`, `status`) VALUES
	('" . $title . "','" . $date . "','" . $language . "','" . $key . "','" . $duration . "','" . $status . "')";

    $db->sql($sql);

    echo '<label class="alert alert-success">Contest created successfully!</label>';
}
// 51. add exam module
if (isset($_POST['title']) && isset($_POST['add_exam_module'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $title = $db->escapeString($_POST['title']);
    $date = $db->escapeString($_POST['date']);
    $language = $db->escapeString($_POST['language']);
    $key = $db->escapeString($_POST['key']);
    $duration = $db->escapeString($_POST['duration']);
    $status = 0;




    $sql = "INSERT INTO `exam_module` (`title`, `date`, `language_id`, `exam_key`, `duration`, `status`) VALUES
	('" . $title . "','" . $date . "','" . $language . "','" . $key . "','" . $duration . "','" . $status . "')";

    $db->sql($sql);

    echo '<label class="alert alert-success">Contest created successfully!</label>';
}
// 52. add exam question
if (isset($_POST['marks']) && isset($_POST['add_exam_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $exam_id = $db->escapeString($_POST['exam_id']);
    $question = $db->escapeString($_POST['question']);
    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = !empty($_POST['c']) ? $db->escapeString($_POST['c']) : '';
    $d = !empty($_POST['d']) ? $db->escapeString($_POST['d']) : '';
    $e = !empty($_POST['e']) ? $db->escapeString($_POST['e']) : '';
    $answer = $db->escapeString($_POST['answer']);
    $marks = $db->escapeString($_POST['marks']);

    $filename = '';
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $target_path = 'images/contest-question/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }


    $sql = "INSERT INTO `exam_questions` (`exam_id`, `question`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `answer`, `marks`, `image`) VALUES
	('" . $exam_id . "','" . $question . "','" . $a . "','" . $b . "','" . $c . "','" . $d . "','" . $e . "','" . $answer . "','" . $marks . "','" . $filename . "')";

    $db->sql($sql);
    $res = $db->getResult();
    echo '<label class="alert alert-success">Question created successfully!</label>';
}

// 53. update exam module status
if (isset($_POST['update_id']) && isset($_POST['update_exam_status'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['update_id'];
    $status = $db->escapeString($_POST['status']);

    $sql = 'SELECT *  FROM `exam_questions` WHERE `exam_id`=' . $id;
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        $sql = "UPDATE `exam_module` SET `status`='" . $status . "' WHERE `id`=" . $id;
        $db->sql($sql);
        echo "<p class='alert alert-success'>Status updated successfully!</p>";
    } else {
        echo "<p class='alert alert-danger'>No enought question for active</p>";
    }
}


// 54.update_exam_module()
if (isset($_POST['exam_id']) && isset($_POST['update_exam_module'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_POST['exam_id'];
    $title = $db->escapeString($_POST['title']);
    $date = $db->escapeString($_POST['date']);
    $key = $db->escapeString($_POST['key']);
    $duration = $db->escapeString($_POST['duration']);



    $sql = "UPDATE exam_module set `title`='" . $title . "', `date`='" . $date . "', `exam_key`='" . $key . "', `duration`='" . $duration . "' where `id`=" . $id;

    $db->sql($sql);
    echo "<p class='alert alert-success'>Contest updated successfully!</p>";
}


// 55. delete_exam_module()
if (isset($_GET['id']) && $_GET['delete_exam_module'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }


    $id = $_GET['id'];

    $sql_questions = 'DELETE FROM `exam_questions` WHERE `exam_id`=' . $id;
    $db->sql($sql_questions);

    $sql = 'DELETE FROM `exam_module` WHERE `id`=' . $id;
    if ($db->sql($sql)) {

        echo 1;
    } else {
        echo 0;
    }
}

// 56.update_exam_question()
if (isset($_POST['question_id']) && isset($_POST['update_exam_question'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $exam_id = $db->escapeString($_POST['exam_id']);
    $question = $db->escapeString($_POST['question']);
    $a = $db->escapeString($_POST['a']);
    $b = $db->escapeString($_POST['b']);
    $c = !empty($_POST['c']) ? $db->escapeString($_POST['c']) : '';
    $d = !empty($_POST['d']) ? $db->escapeString($_POST['d']) : '';
    $e = !empty($_POST['e']) ? $db->escapeString($_POST['e']) : '';
    $answer = $db->escapeString($_POST['answer']);
    $marks = $db->escapeString($_POST['marks']);
    $id = $db->escapeString($_POST['question_id']);
    $question_type = $db->escapeString($_POST['question_type']);

    if ($question_type == 2) {
        $c = '';
        $d = '';
        $e = '';
    }

    $filename = '';
    if ($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $target_path = 'images/contest-question/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }

        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        if (!(in_array($extension, $allowedExts))) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
            $response['error'] = true;
            $response['message'] = 'Image type is invalid';
            echo json_encode($response);
            return false;
        }
    }


    $sql = "UPDATE `exam_questions` SET `exam_id`='" . $exam_id . "', `question`='" . $question . "', `optiona`='" . $a . "', `optionb`='" . $b . "', `optionc`='" . $c . "', `optiond`='" . $d . "', `optione`='" . $e . "', `answer`='" . $answer . "', `marks`='" . $marks . "', `image`='" . $filename . "', `question_type`='" . $question_type . "' WHERE `id`=" . $id;

    $db->sql($sql);
    // $res = $db->getResult();
    echo '<label class="alert alert-success">Question updated successfully!</label>';
}


// 57. delete_exam_question()
if (isset($_GET['id']) && $_GET['delete_exam_question'] != '') {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }


    $id = $_GET['id'];

    $sql_questions = 'DELETE FROM `exam_questions` WHERE `id`=' . $id;



    if ($db->sql($sql_questions)) {

        echo 1;
    } else {
        echo 0;
    }
}
// 58. delete_multiple_questions

if (isset($_POST['delete_multiple_questions']) && isset($_POST['question_ids'])) {
    $question_ids = $_POST['question_ids'];

    // Convert array to comma-separated string for SQL IN clause
    $ids = implode(',', array_map('intval', $question_ids));

    // Delete questions
    $sql = "DELETE FROM exam_questions WHERE id IN ($ids)";

    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
    exit();
}

// 59. import_exam_question()
if (isset($_POST['import_exam_question']) && isset($_POST['exam_id'])) {
    $exam_id = $_POST['exam_id'];

    if (empty($exam_id)) {
        echo json_encode(['error' => true, 'message' => 'Please select an exam module']);
        exit();
    }

    if ($_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");

        // Skip header row
        $header = fgetcsv($handle);

        // Convert header to lowercase and trim for consistent comparison
        $header = array_map(function ($item) {
            return strtolower(trim($item));
        }, $header);

        // Get column indexes
        $columns = array(
            'question' => array_search('question', $header),
            'optiona' => array_search('optiona', $header),
            'optionb' => array_search('optionb', $header),
            'optionc' => array_search('optionc', $header),
            'optiond' => array_search('optiond', $header),
            'answer' => array_search('answer', $header),
            'marks' => array_search('marks', $header),
            'question_type' => array_search('question_type', $header)
        );

        // Validate required columns exist
        foreach ($columns as $key => $index) {
            if ($index === false) {
                echo json_encode(['error' => true, 'message' => "Missing column: $key"]);
                exit();
            }
        }

        $success_count = 0;
        $error_count = 0;
        $line = 2; // Start from line 2 (after header)

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Get values from correct columns
            $question = $db->escapeString($data[$columns['question']]);
            $optiona = $db->escapeString($data[$columns['optiona']]);
            $optionb = $db->escapeString($data[$columns['optionb']]);
            $optionc = $db->escapeString($data[$columns['optionc']]);
            $optiond = $db->escapeString($data[$columns['optiond']]);
            $answer = $db->escapeString($data[$columns['answer']]);
            $marks = $db->escapeString($data[$columns['marks']]);
            $question_type = $db->escapeString($data[$columns['question_type']]);

            // Validate required fields
            if (
                empty($question) || empty($optiona) || empty($optionb) ||
                empty($answer) || empty($marks) || empty($question_type)
            ) {
                $error_count++;
                continue;
            }

            // For question_type 2 (true/false), clear options C and D
            if ($question_type == '2') {
                $optionc = '';
                $optiond = '';
            }

            $sql = "INSERT INTO exam_questions (exam_id, question, optiona, optionb, optionc, optiond, answer, marks, question_type) 
                    VALUES ('$exam_id', '$question', '$optiona', '$optionb', '$optionc', '$optiond', '$answer', '$marks', '$question_type')";

            if ($db->sql($sql)) {
                $success_count++;
            } else {
                $error_count++;
            }
            $line++;
        }

        fclose($handle);

        echo json_encode([
            'error' => false,
            'success' => $success_count,
            'failed' => $error_count,
            'message' => "$success_count questions imported successfully."
        ]);
        exit();
    } else {
        echo json_encode(['error' => true, 'message' => 'Error uploading file']);
        exit();
    }
}


if (isset($_POST['import_junior_csv']) && $_POST['import_junior_csv'] == 1) {




    if (!isset(
        $_POST['category'],
        $_POST['subcategory'],
        $_FILES['questions'],
        $_POST['question_type'],




    )) {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
        error_log(json_encode($response));
        return false;
    }

    // Check if file upload was successful - FIXED logic
    if (!isset($_FILES['questions']) || $_FILES['questions']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'error' => 'true',
            'message' => 'No file uploaded or upload error'
        ]);
        exit;
    }

    $csvFile = $_FILES['questions']['tmp_name'];

    // Open and read the CSV file
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        $header = fgetcsv($handle);
        $questions = [];
        while (($data = fgetcsv($handle)) !== FALSE) {
            $questions[] = [
                'question' => $data[0],
                'option_a' => $data[1],
                'option_b' => $data[2],
                'option_c' => $data[3],
                'option_d' => $data[4],
                'option_e' => isset($data[5]) ? $data[5] : '', // Optional field for option E
                'answer' => $data[6],
                'level' => isset($data[7]) ? $data[7] : '', // Optional level field
                'image' => isset($data[8]) ? $data[8] : '', // Optional image field
                'language_id' => isset($data[9]) ? $data[9] : '', // Optional language field
            ];
        }
        fclose($handle);

        $category = htmlspecialchars($_POST['category']);
        $subcategory = htmlspecialchars($_POST['subcategory']);
        $question_type = htmlspecialchars($_POST['question_type']);

        // FIXED: Move this outside the loop

        foreach ($questions as $value) {
            try {
                $question = $db->escapeString($value['question']); // FIXED: Added escaping
                $optiona = $db->escapeString($value['option_a']);
                $optionb = $db->escapeString($value['option_b']);
                $optionc = $db->escapeString($value['option_c']);
                $optiond = $db->escapeString($value['option_d']);
                $optione = $db->escapeString($value['option_e']);
                $level = $db->escapeString($value['level']);
                $image = $db->escapeString($value['image']);
                $answer = $db->escapeString($value['answer']);
                $language_id = $db->escapeString($value['language_id']);

                $sql_2 = "INSERT INTO junior_question
                          (question, optiona, optionb, optionc, optiond, answer, subcategory,category, question_type,level,image,optione,language_id) 
                          VALUES 
                          ('$question', '$optiona', '$optionb', '$optionc', '$optiond', '$answer', '$subcategory','$category','$question_type','$level','$image','$optione','$language_id')";

                if (!$db->sql($sql_2)) {
                    throw new Exception('Failed to insert question');
                }
            } catch (Exception $e) {
                error_log("Error processing question: " . $e->getMessage());
                echo json_encode([
                    'error' => 'true',
                    'message' => 'Error processing questions: ' . $e->getMessage()
                ]);
                exit;
            }
        }

        echo json_encode(['error' => 'false', 'success' => true]);
        exit;
    } else {
        echo json_encode([
            'error' => 'true',
            'message' => 'Failed to read CSV file'
        ]);
        exit;
    }
};
