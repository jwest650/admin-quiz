<?php

/*
  API v7.0.7
  Quiz Online - WRTeam.in
  WRTeam Developers
 */
session_start();
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//header("Content-Type: multipart/form-data");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

include('library/crud.php');
include('library/functions.php');

$db = new Database();
$db->connect();

$fn = new Functions();
$config = $fn->get_configurations();

include_once('library/verify-token.php');

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
$response = array();
$access_key = "6808";

$toDate = date('Y-m-d');
$toDateTime = date('Y-m-d H:i:s');


if (isset($_POST['access_key']) && isset($_POST['get_teacher_questions']) && $_POST['get_teacher_questions'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }
    $success = true;
    if (isset($_POST['teacher_id'])) {
        $category_id = $db->escapeString($_POST['category_id']);
        $teacher_id = $db->escapeString($_POST['teacher_id']);
        $sql = "SELECT * FROM teacher_questions WHERE category_id = '$category_id' AND teacher_id = '$teacher_id'";
        if (!$db->sql($sql)) {
            $success = false;
        }

        if ($success) {
            $res = $db->getResult();
            $response['error'] = "false";
            $response['message'] = "Questions fetched successfully";
            $response['data'] = $res;
        } else {

            $response['error'] = "true";
            $response['message'] = "Failed to get  questions";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "No questions available";
    }

    print_r(json_encode($response));
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['create_questions']) && $_POST['create_questions'] == 1) {



    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }


    $category_id = '';
    if (isset($_POST['category_id'])) {
        $category_id = $db->escapeString($_POST['category_id']);
    };
    if (!isset($_POST['category_id'])) {
        $db->sql("SET FOREIGN_KEY_CHECKS=0");
    }
    $teacher_id = $db->escapeString($_POST['teacher_id']); // Added missing semicolon
    $sql = "DELETE FROM teacher_questions WHERE category_id='' AND teacher_id = $teacher_id";
    $db->sql($sql);


    // Then insert questions
    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        $success = true;
        foreach ($_POST['questions'] as $question) {
            // Escape all input values
            $question_text = $db->escapeString($question['question']);
            $question_type = $db->escapeString($question['question_type']);
            $optiona = isset($question['optiona']) ? $db->escapeString($question['optiona']) : "";
            $optionb = isset($question['optionb']) ? $db->escapeString($question['optionb']) : "";
            $optionc = isset($question['optionc']) ? $db->escapeString($question['optionc']) : "";
            $optiond = isset($question['optiond']) ? $db->escapeString($question['optiond']) : "";
            $optione = isset($question['optione']) ? $db->escapeString($question['optione']) : "";
            $points = (int)$question['points'];
            $answer = $db->escapeString($question['answer']);
            $time = $question['time'];


            $sql = "INSERT INTO teacher_questions (
                     category_id, question, question_type, 
                    optiona, optionb, optionc, optiond, optione, 
                    points, answer, time,teacher_id
                ) VALUES (
                    '$category_id','$question_text', '$question_type',
                    '$optiona', '$optionb', '$optionc', '$optiond', '$optione',
                    $points, '$answer', $time,$teacher_id
                )";

            if (!$db->sql($sql)) {
                $success = false;
                break;
            }
        }

        if ($success) {
            $response['error'] = "false";
            $response['message'] = "Questions added successfully";
            $response['category_id'] = $category_id;
            $db->sql("SET FOREIGN_KEY_CHECKS=1");
        } else {

            $response['error'] = "true";
            $response['message'] = "Failed to add questions";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "No questions provided or invalid format";
    }
    $db->sql("SET FOREIGN_KEY_CHECKS=1");
    print_r(json_encode($response));
    return false;
}



if (isset($_POST['access_key']) && isset($_POST['create_category']) && $_POST['create_category'] == 1) {



    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }


    // First create category
    if (isset($_POST['name'])) {

        $name = $db->escapeString($_POST['name']);
        $subject = $db->escapeString($_POST['subject']);
        $grade = $db->escapeString($_POST['grade']);
        $visibility = $db->escapeString($_POST['visibility']);
        $teacher_id = $db->escapeString($_POST['teacher_id']);
        $language = $db->escapeString($_POST['language']);

        $check_duplicate = "SELECT id FROM teacher_category 
                          WHERE name = '$name' 
                          AND subject = '$subject'
                          AND grade = '$grade'
                          AND teacher_id = '$teacher_id'";
        $db->sql($check_duplicate);
        $duplicate = $db->getResult();

        if (!empty($duplicate)) {
            $response['error'] = "true";
            $response['message'] = "Category already exists";
            print_r(json_encode($response));
            return false;
        }

        $filename = '';
        // Handle image upload
        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
            if (!is_dir('images/teacher_category')) {
                mkdir('images/teacher_category', 0777, true);
            }

            $extension = pathinfo($_FILES["category_image"]["name"])['extension'];
            if (!(in_array($extension, $allowedExts))) {
                $response['error'] = "true";
                $response['message'] = "Image type is invalid";
                print_r(json_encode($response));
                return false;
            }
            $filename = microtime(true) . '.' . strtolower($extension);
            $full_path = 'images/teacher_category/' . $filename;
            if (!move_uploaded_file($_FILES["category_image"]["tmp_name"], $full_path)) {
                $response['error'] = "true";
                $response['message'] = "Image upload failed";
                print_r(json_encode($response));
                return false;
            }
        }
        $db->sql("SET FOREIGN_KEY_CHECKS=0");
        // Insert category
        $sql = "INSERT INTO teacher_category (name, subject, grade, visibility, image, teacher_id,language) 
                VALUES ('$name', '$subject', '$grade', '$visibility', '$filename', '$teacher_id',$language)";

        if ($db->sql($sql)) {
            // Get the last inserted ID using a direct query
            $db->sql("SELECT LAST_INSERT_ID() as id");
            $result = $db->getResult();
            $category_id = $result[0]['id'];

            $sql_1 = "UPDATE teacher_questions 
                      SET category_id = '$category_id' 
                      WHERE teacher_id = '$teacher_id' 
                      AND (category_id = '' OR category_id IS NULL)";

            if ($db->sql($sql_1)) {
                // Get the final result with the new category data
                $sql_select = "SELECT * FROM teacher_category WHERE id = '$category_id'";
                $db->sql($sql_select);
                $final_result = $db->getResult();

                $response['error'] = "false";
                $response['message'] = "Category updated successfully";
                $response['data'] = $final_result;
            } else {
                $response['error'] = "true";
                $response['message'] = "Failed to update category";
            }
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to execute query";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "provide all data";
    }
    print_r(json_encode($response));
}
