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



$db->sql("SET NAMES 'utf8'");
$response = array();
$access_key = "6808";

$toDate = date('Y-m-d');
$toDateTime = date('Y-m-d H:i:s');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', './error.log');
error_reporting(E_ALL);


if (isset($_POST['access_key'], $_POST['get_teacher_questions']) && $_POST['get_teacher_questions'] == 1) {

    if (!verify_token()) {
        $response = [
            'error' => "true",
            'message' => "Invalid token",
        ];
        echo json_encode($response);
        return false;
    }

    if ($access_key !== $_POST['access_key']) {
        $response = [
            'error' => "true",
            'message' => "Invalid Access Key",
        ];
        echo json_encode($response);
        return false;
    }

    if (isset($_POST['category_uid'])) {
        $category_uid = $db->escapeString($_POST['category_uid']);


        // Fetch category data
        $categoryQuery = "SELECT 
    tc.*, 
    us.name AS user, 
    us.profile 
FROM 
    teacher_category tc
JOIN 
    users us 
    ON us.id = tc.teacher_id
WHERE 
    tc.uid = '$category_uid'";






        $categoryData = [];
        if ($db->sql($categoryQuery)) {
            $categoryData = $db->getResult();
        } else {
            $response = [
                'error' => "true",
                'message' => "Failed to fetch category data",
            ];
            echo json_encode($response);
            return false;
        }

        // Fetch questions data
        $questionsQuery = "
            SELECT 
                tq.*
               
            FROM 
                teacher_questions tq
            
            WHERE 
                tq.category_uid = '$category_uid'";

        if ($_POST['schuffle']) {
            $questionsQuery .= "  ORDER BY RAND()";
        }

        $questionsData = [];
        if ($db->sql($questionsQuery)) {
            $questionsData = $db->getResult();

            // Process user profile
            foreach ($questionsData as &$question) {
                if (isset($question['profile'])) {
                    if (!filter_var($question['profile'], FILTER_VALIDATE_URL)) {
                        $question['profile'] = !empty($question['profile'])
                            ? DOMAIN_URL . 'uploads/profile/' . $question['profile']
                            : '';
                    }
                }
            }
        } else {
            $response = [
                'error' => "true",
                'message' => "Failed to fetch questions",
            ];
            echo json_encode($response);
            return false;
        }

        // Combine results
        $response = [
            'error' => "false",
            'message' => "Data fetched successfully",
            'category' => $categoryData,
            'questions' => $questionsData,
        ];
    } else {
        $response = [
            'error' => "true",
            'message' => "Please provide all required fields",
        ];
    }

    echo json_encode($response);
    return false;
};


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









    // Then insert questions
    if (isset($_POST['questions']) && is_array($_POST['questions']) && isset($_POST['category_uid'])) {
        $category_uid = $db->escapeString($_POST['category_uid']);
        $teacher_id = (int)$db->escapeString($_POST['teacher_id']);

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
            $video_time = isset($question['video_time']) ? $db->escapeString($question['video_time']) : '';


            $sql = "INSERT INTO teacher_questions (
                     category_uid, question, question_type, 
                    optiona, optionb, optionc, optiond, optione, 
                    points, answer, time, teacher_id,video_time
                ) VALUES (
                    '$category_uid', '$question_text', '$question_type',
                    '$optiona', '$optionb', '$optionc', '$optiond', '$optione',
                    $points, '$answer', '$time', $teacher_id, '$video_time'
                )";
            if (!$db->sql($sql)) {
                $success = false;
                break;
            }
        }

        if ($success) {
            $response['error'] = "false";
            $response['message'] = "Questions added successfully";
            $response['category_uid'] = $category_uid;
        } else {

            $response['error'] = "true";
            $response['message'] = "Failed to add questions";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "No questions provided or invalid format";
    }

    print_r(json_encode($response));
    return false;
};



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
        $teacher_id = (int)$db->escapeString($_POST['teacher_id']);
        $quiz_type = isset($_POST['quiz_type']) ? $db->escapeString($_POST['quiz_type']) : '';
        $subject = isset($_POST['subject']) ? $db->escapeString($_POST['subject']) : '';
        $grade = isset($_POST['grade']) ? $db->escapeString($_POST['grade']) : '';
        $visibility = isset($_POST['visibility']) ? $db->escapeString($_POST['visibility']) : '';
        $language = isset($_POST['language']) ? $db->escapeString($_POST['language']) : '';

        $check_duplicate = "SELECT uid FROM teacher_category 
                          WHERE name = '$name' 
                          AND subject = '$subject'
                          AND grade = '$grade'
						  AND quiz_type ='$quiz_type'
                          AND teacher_id = '$teacher_id'
						  ";
        $db->sql($check_duplicate);
        $duplicate = $db->getResult();

        if (!empty($duplicate)) {
            $delete_duplicate = "DELETE FROM teacher_category  WHERE name = '$name' 
                          AND subject = '$subject'
                          AND grade = '$grade'
						    AND quiz_type ='$quiz_type'
                          AND teacher_id = '$teacher_id'";
            $db->sql($delete_duplicate);
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

        // Insert category
        $sql = "INSERT INTO teacher_category (name, subject, grade, visibility, image, teacher_id,language,quiz_type) 
                VALUES ('$name', '$subject', '$grade', '$visibility', '$filename', '$teacher_id','$language','$quiz_type')";

        if ($db->sql($sql)) {
            // Get the last inserted ID using a direct query
            $db->sql("SELECT uid FROM teacher_category WHERE teacher_id = '$teacher_id' AND name ='$name' ORDER BY id DESC LIMIT 1");
            $result = $db->getResult();
            $category_uid = $result[0];

            $response['error'] = "false";
            $response['message'] = "Success";
            $response['data'] = $category_uid;
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to execute query";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "provide all data";
    }
    print_r(json_encode($response));
};


if (isset($_POST['access_key']) && isset($_POST['update_category']) && $_POST['update_category'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }


    if ($_POST['category_uid'] && $_POST['teacher_id']) {

        $name = $db->escapeString($_POST['name']);
        $grade = $db->escapeString($_POST['grade']);
        $subject = $db->escapeString($_POST['subject']);
        $visibility = $db->escapeString($_POST['visibility']);
        $language = $db->escapeString($_POST['language']);
        $category_uid = $db->escapeString($_POST['category_uid']);

        $teacher_id = $db->escapeString($_POST['teacher_id']);

        // Handle the file upload
        if (isset($_FILES['image'])) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileSize = $_FILES['image']['size'];
            $fileType = $_FILES['image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Specify the directory where you want to save the uploaded file
            $base_url = 'https://admin.uquiz.xyz/';
            $uploadFileDir = 'images/teacher_category/';
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                $response['error'] = "true";
                $response['message'] = "file type not accepted";
                exit;
            }

            $dest_path = $uploadFileDir . $fileName;
            $image_url = $base_url . $dest_path;
            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $sql = "UPDATE teacher_category 
            SET name = '$name', grade = '$grade', subject = '$subject', 
                visibility = '$visibility', language = '$language', image='$image_url' 
            WHERE uid = '$category_uid' AND teacher_id = '$teacher_id'";
            } else {
                $response['error'] = "true";
                $response['message'] = "upload error";
            }
        } else {
            $sql = "UPDATE teacher_category 
            SET name = '$name', grade = '$grade', subject = '$subject', 
                visibility = '$visibility', language = '$language'  
            WHERE uid = '$category_uid' AND teacher_id = '$teacher_id'";
        }

        if ($db->sql($sql)) {
            $response['error'] = "false";
            $response['message'] = "Category updated successfully";
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to update category";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
};

if (isset($_POST['access_key']) && isset($_POST['publish_category']) && $_POST['publish_category'] == 1) {

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

    if ($_POST['publish'] && $_POST['category_uid'] && $_POST['teacher_id']) {
        $publish = $db->escapeString($_POST['publish']);

        $category_uid = $db->escapeString($_POST['category_uid']);
        $teacher_id = $db->escapeString($_POST['teacher_id']);

        $sql = "UPDATE teacher_category 
            SET publish = '$publish' 
            WHERE uid = '$category_uid' AND teacher_id = '$teacher_id'";

        if ($db->sql($sql)) {
            $response['error'] = "false";
            $response['message'] = "Category updated successfully";
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to update category";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['get_category_and_question_count']) && $_POST['get_category_and_question_count'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (isset($_POST['teacher_id'])) {

        $teacher_id = $db->escapeString($_POST['teacher_id']);
        $sql = "SELECT 
    teacher_category.uid AS category_uid,
    teacher_category.name AS category_name, 
	teacher_category.publish AS publish,
	teacher_category.image As image,
	teacher_category.grade As grade,
	teacher_category.subject As subject,
	teacher_category.teacher_id As teacher_id,
	teacher_category.created_at As created_at,
	teacher_category.quiz_type As quiz_type,
	user.profile,
	user.name AS user,
    COUNT(teacher_questions.id) AS questions_count
FROM teacher_category
LEFT JOIN teacher_questions 
    ON teacher_category.uid = teacher_questions.category_uid
	JOIN users user ON user.id =$teacher_id
WHERE teacher_category.teacher_id = $teacher_id
GROUP BY teacher_category.uid, teacher_category.name

";

        if ($db->sql($sql)) {
            $result = $db->getResult();
            $response['error'] = "false";
            $response['message'] = "fetched successfully";
            $response['data'] =  $result;
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to update category";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['get_category_by_uid']) && $_POST['get_category_by_uid'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (isset($_POST['category_uid'])) {

        $category_uid = $db->escapeString($_POST['category_uid']);
        $sql = "SELECT 
    tc.*, 
    us.name, 
    us.profile, 
    (SELECT COUNT(*) FROM teacher_questions tq WHERE tq.category_uid = tc.uid) AS question_count
FROM 
    teacher_category tc
JOIN 
    users us 
ON 
    us.id = tc.teacher_id
	
WHERE 
    tc.uid = '$category_uid';
";



        if ($db->sql($sql)) {
            $result = $db->getResult();
            if ($result[0]['profile']) {
                if (filter_var($result[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                    // Not a valid URL. Its a image only or empty
                    $result[0]['profile'] = (!empty($result[0]['profile'])) ? DOMAIN_URL . 'uploads/profile/' . $result[0]['profile'] : '';
                } else {
                    /* if it is a ur than just pass url as it is */
                    $result[0]['profile'] = $result[0]['profile'];
                }
            }


            $response['error'] = "false";
            $response['message'] = "fetched successfully";
            $response['data'] =  $result;
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to fetch category";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['delete_category']) && $_POST['delete_category'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }


    if ($_POST['category_uid'] && $_POST['teacher_id']) {


        $category_uid = $db->escapeString($_POST['category_uid']);
        $teacher_id = $db->escapeString($_POST['teacher_id']);

        $sql = "DELETE FROM teacher_category WHERE uid = '$category_uid' AND teacher_id = '$teacher_id'";

        if ($db->sql($sql)) {
            $response['error'] = "false";
            $response['message'] = "Category delete successfully";
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to delete category";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['recent_viewed']) && $_POST['recent_viewed'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }




    $uids = json_decode($_POST['list'], true);

    $idList = implode(',', array_map(function ($uid) use ($db) {
        return "'" .  trim($db->escapeString($uid)) . "'";
    }, $uids));

    if (empty($idList)) {
        die("Error: No category IDs provided.");
    }




    $sql = "SELECT 
            tc.*, 
            COUNT(tq.category_uid) AS question_count
        FROM 
            teacher_category tc
        LEFT JOIN 
            teacher_questions tq 
            ON tq.category_uid = tc.uid 
            AND tc.teacher_id = tq.teacher_id
        WHERE 
           
            tc.uid IN ($idList)
        GROUP BY 
            tc.uid 
        ORDER BY 
            tc.id DESC
        LIMIT 5";



    if ($db->sql($sql)) {
        $result = $db->getResult();
        $response['error'] = "false";
        $response['message'] = "fetched successfully";
        $response['data'] =  $result;
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to delete category";
    }


    print_r(json_encode($response));
    return false;
};

if (isset($_POST['access_key']) && isset($_POST['trends']) && $_POST['trends'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }




    $type = isset($_POST['type']) && $_POST['type'] != 'for-you' ? $_POST['type'] : "";

    if ($type === '' || $type === "''") {
        // Fetch all quiz types
        $sql = "SELECT tc.*, COUNT(tq.category_uid) AS question_count
FROM teacher_category tc
LEFT JOIN teacher_questions tq ON tq.category_uid = tc.uid AND tc.teacher_id = tq.teacher_id
WHERE tc.publish='true' AND tc.visibility='public'
GROUP BY tc.uid
ORDER BY tc.likes DESC, tc.views DESC";
    } else {
        // Fetch specific quiz type
        $sql = "SELECT tc.*, COUNT(tq.category_uid) AS question_count
FROM teacher_category tc
LEFT JOIN teacher_questions tq ON tq.category_uid = tc.uid AND tc.teacher_id = tq.teacher_id
WHERE tc.publish='true' AND tc.visibility='public' AND tc.quiz_type = '$type'
GROUP BY tc.uid
ORDER BY tc.likes DESC, tc.views DESC";
    }

    if ($db->sql($sql)) {
        $result = $db->getResult();
        $response['error'] = "false";
        $response['message'] = "fetched successfully";
        $response['data'] =  $result;
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch trends";
    }


    print_r(json_encode($response));
    return false;
};

if (isset($_POST['access_key']) && isset($_POST['get_questions']) && $_POST['get_questions'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }



    if ($_POST['category_uid']) {
        $category_uid = $_POST['category_uid'];

        $sql = "SELECT tq.*, tc.name, (SELECT COUNT(*) FROM teacher_questions WHERE category_uid = '$category_uid')  AS question_count
        FROM teacher_questions tq
        JOIN teacher_category tc
        ON tc.uid = tq.category_uid
        WHERE tq.category_uid = '$category_uid'
        GROUP BY tq.category_uid, tq.id
		";

        if ($db->sql($sql)) {
            $result = $db->getResult();
            $response['error'] = "false";
            $response['message'] = "fetched successfully";
            $response['data'] =  $result;
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to fetch questions";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "provide all fields";
    }




    print_r(json_encode($response));
    return false;
};





if (isset($_POST['access_key']) && isset($_POST['add_view']) && $_POST['add_view'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }




    if ($_POST['uid']) {
        $uid = $_POST['uid'];
        $sql = "SELECT views from teacher_category WHERE uid ='$uid'";
        if ($db->sql($sql)) {
            $result = $db->getResult();
            $number = (int)$result[0]['views'] + 1;
            $sql = "UPDATE teacher_category SET views ='$number' WHERE uid = '$uid'";
            if ($db->sql($sql)) {

                $response['error'] = "false";
                $response['message'] = "updated successfully";
            } else {
                $response['error'] = "true";
                $response['message'] = "Failed to update";
            }
        } else {
            $response['error'] = "true";
            $response['message'] = "cant find category";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }




    print_r(json_encode($response));
    return false;
};

if (isset($_POST['access_key']) && isset($_POST['get_searched']) && $_POST['get_searched'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }




    $search = isset($_POST['search_word']) ? trim($_POST['search_word']) : '';
    $quiz_type = isset($_POST['quiz_type']) ? trim($_POST['quiz_type']) : '';
    // Prepare the base query with common conditions
    $sql = "SELECT tc.*, COUNT(tq.id) AS question_count 
        FROM teacher_category tc 
        LEFT JOIN teacher_questions tq 
        ON tc.uid = tq.category_uid
        WHERE tc.publish = 'true' 
        AND tc.visibility = 'public'
	
		";

    if ($quiz_type) {
        $sql .= " AND tc.quiz_type ='$quiz_type'";
    }
    // Add search condition if search term is provided
    if ($search) {
        // Using a prepared statement to prevent SQL injection
        $sql .= " AND (tc.name LIKE '{$search}%' OR tc.name REGEXP '{$search}')";
    }


    $sql .= " GROUP BY tc.uid";




    if ($db->sql($sql)) {
        $result = $db->getResult();
        $response['error'] = "false";
        $response['message'] = "fetched successfully";
        $response['data'] =  $result;
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch questions";
    }






    print_r(json_encode($response));
    return false;
};





if (isset($_POST['access_key']) && isset($_POST['get_user_profile']) && $_POST['get_user_profile'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }







    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "SELECT profile,name FROM `users` WHERE id ='$id'";

        if ($db->sql($sql)) {
            $result = $db->getResult();

            if (!empty($result)) {
                if (filter_var($result[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                    // Not a valid URL. Its a image only or empty
                    $result[0]['profile'] = (!empty($result[0]['profile'])) ? DOMAIN_URL . 'uploads/profile/' . $result[0]['profile'] : '';
                } else {
                    /* if it is a ur than just pass url as it is */
                    $result[0]['profile'] = $result[0]['profile'];
                }


                $response['error'] = "false";
                $response['message'] = "fetched successfully";
                $response['data'] =  $result;
            } else {
                $response['error'] = "false";
                $response['message'] = "no user found";
            }
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to fetch";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['scoreboard']) && $_POST['scoreboard'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }







    if (isset($_POST['quiz_id']) && isset($_POST['user_id']) && isset($_POST['score']) && isset($_POST['incorrect'])) {
        $user_id = $_POST['user_id'];
        $quiz_id = trim($_POST['quiz_id']);
        $score = $_POST['score'];
        $incorrect = $_POST['incorrect'];
        $correct = $_POST['correct'];
        $sql = "INSERT INTO `teacher_quiz_scoreboard` (user_id, quiz_id, score, incorrect, correct)
        VALUES ('$user_id', '$quiz_id', '$score', '$incorrect', '$correct')
        ON DUPLICATE KEY UPDATE
        score = VALUES(score),
        incorrect = VALUES(incorrect),
        correct = VALUES(correct)";

        if ($db->sql($sql)) {
            $result = $db->getResult();
            $response['error'] = "false";
            $response['message'] = 'success';
        } else {
            $response['error'] = "true";
            $response['message'] = "error";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['student_score']) && $_POST['student_score'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }







    if (isset($_POST['assign_id']) && isset($_POST['score']) && isset($_POST['student_id'])  && isset($_POST['total_questions']) && isset($_POST['questions_answered'])) {
        $assign_id = $_POST['assign_id'];

        $student_id = trim($_POST['student_id']);
        $score = $_POST['score'];
        $duration = $_POST['duration'];
        $total_questions = $_POST['total_questions'];
        $questions_answered = $_POST['questions_answered'];

        $sql = "INSERT INTO `teacher_student_score` (
            assign_id, score, student_id, duration, total_questions, questions_answered
        ) VALUES ('$assign_id',  '$score', '$student_id', '$duration', '$total_questions', '$questions_answered')";

        if ($db->sql($sql)) {
            $result = $db->getResult();
            $response['error'] = false;
            $response['message'] = 'success';
        } else {
            $response['error'] = true;
            $response['message'] = "error";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['daily_rank']) && $_POST['daily_rank'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }







    if (isset($_POST['quiz_id']) && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $quiz_id = $_POST['quiz_id'];



        $sql = "SELECT *,
    RANK() OVER (ORDER BY score DESC) as user_rank,
    (SELECT COUNT(*) FROM teacher_quiz_scoreboard WHERE quiz_id = '$quiz_id') AS total_participants FROM 
    teacher_quiz_scoreboard 
	WHERE 
    quiz_id = '$quiz_id' 
    AND user_id = $user_id;";

        $sql_2 = "SELECT * FROM teacher_questions WHERE category_uid= '$quiz_id'";

        if ($db->sql($sql)) {
            $result = $db->getResult();
            $response['leaderboard']['error'] = "false";
            $response['leaderboard']['data'] = $result;
        } else {
            $response['leaderboard']['error'] = "true";
            $response['leaderboard']['message'] = "error";
        }

        if ($db->sql($sql_2)) {
            $result = $db->getResult();
            $response['questions']['error'] = "false";
            $response['questions']['data'] = $result;
        } else {
            $response['questions']['error'] = "true";
            $response['questions']['message'] = "error";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['class_rank']) && $_POST['class_rank'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }







    if (isset($_POST['quiz_id']) && isset($_POST['user_id']) && isset($_POST['assign_id'])) {
        $user_id = $_POST['user_id'];
        $quiz_id = $_POST['quiz_id'];
        $assign_id = $_POST['assign_id'];



        $sql = "WITH latest_attempt AS (
    SELECT t1.*
    FROM teacher_student_score t1
    JOIN (
        SELECT student_id, MAX(created_at) AS latest_time
        FROM teacher_student_score
        WHERE assign_id = '$assign_id'
        GROUP BY student_id
    ) t2
    ON t1.student_id = t2.student_id AND t1.created_at = t2.latest_time
    WHERE t1.assign_id = '$assign_id'
)
SELECT 
    latest_attempt.student_id,
    latest_attempt.score,
    RANK() OVER (ORDER BY latest_attempt.score DESC) AS user_rank,
    (SELECT COUNT(DISTINCT student_id) 
        FROM teacher_student_score 
        WHERE assign_id = '$assign_id') AS total_participants
FROM latest_attempt
ORDER BY latest_attempt.score DESC;
";

        $sql_2 = "SELECT * FROM teacher_questions WHERE category_uid= '$quiz_id'";

        if ($db->sql($sql)) {
            $result = $db->getResult();
            $response['leaderboard']['error'] = "false";
            $response['leaderboard']['data'] = $result;
        } else {
            $response['leaderboard']['error'] = "true";
            $response['leaderboard']['message'] = "error";
        }

        if ($db->sql($sql_2)) {
            $result = $db->getResult();
            $response['questions']['error'] = "false";
            $response['questions']['data'] = $result;
        } else {
            $response['questions']['error'] = "true";
            $response['questions']['message'] = "error";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    return false;
};


if (isset($_POST['access_key']) && isset($_POST['import_live_quiz_questions']) && $_POST['import_live_quiz_questions'] == 1) {
    if (!verify_token()) {
        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['category'], $_POST['subcategory'], $_FILES['questions'], $_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
        print_r(json_encode($response));
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
                'answer' => $data[5]
            ];
        }
        fclose($handle);

        $category = htmlspecialchars($_POST['category']);
        $subcategory = htmlspecialchars($_POST['subcategory']);
        $teacher_id = htmlspecialchars($_POST['teacher_id']);

        $sql = "INSERT INTO teacher_school_quiz_category (category_name,teacher_id) 
                VALUES ('$category','$teacher_id')";

        if (!$db->sql($sql)) {
            $response['error'] = "true";
            $response['message'] = "Failed to create category";
            print_r(json_encode($response));
            exit;
        }

        $category_id = $db->insert_id();
        $sql_1 = "INSERT INTO teacher_school_quiz_subcategory (subcategory_name, category_id) 
                  VALUES ('$subcategory', '$category_id')";

        if (!$db->sql($sql_1)) {
            $response['error'] = "true";
            $response['message'] = "Failed to create subcategory";
            print_r(json_encode($response));
            exit;
        }

        $subcategory_id = $db->insert_id(); // FIXED: Move this outside the loop

        foreach ($questions as $value) {
            try {
                $question = $db->escapeString($value['question']); // FIXED: Added escaping
                $optiona = $db->escapeString($value['option_a']);
                $optionb = $db->escapeString($value['option_b']);
                $optionc = $db->escapeString($value['option_c']);
                $optiond = $db->escapeString($value['option_d']);
                $answer = $db->escapeString($value['answer']);

                $sql_2 = "INSERT INTO teacher_school_quiz_questions 
                          (question, optiona, optionb, optionc, optiond, answer, subcategory_id) 
                          VALUES 
                          ('$question', '$optiona', '$optionb', '$optionc', '$optiond', '$answer', '$subcategory_id')";

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



if (isset($_POST['access_key']) && isset($_POST['check_old_questions']) && $_POST['check_old_questions'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }
    $response = array();
    if (isset($_POST['teacher_id'])) {
        $teacher_id = $_POST['teacher_id'];
        $sql = "SELECT * from teacher_school_quiz_category WHERE teacher_id ='$teacher_id'";

        $result = $db->sql($sql);
        if ($result) {
            $res = $db->getResult();
            $count = $db->numRows($res);
            if ($count) {
                $response['error'] = "false";

                $response['data'] = $count;
            } else {
                $response['error'] = "true";

                $response['message'] = 'no question available';
            }
        } else {
            $response['error'] = "true";
            $response['message'] = "Database error occurred";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    exit;
};

if (isset($_POST['access_key']) && isset($_POST['get_question']) && $_POST['get_question'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }
    $response = array();
    if (isset($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $sql = "SELECT * from teacher_questions WHERE id ='$edit_id'";

        $result = $db->sql($sql);
        if ($result) {
            $res = $db->getResult();

            $response['error'] = "false";

            $response['data'] = $res;
        } else {
            $response['error'] = "true";

            $response['message'] = 'no question available';
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    exit;
};


if (isset($_POST['access_key']) && isset($_POST['update_question']) && $_POST['update_question'] == 1) {

    if (!verify_token()) {
        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = true;
        $response['message'] = "Invalid Access Key";
        echo json_encode($response);
        return false;
    }

    if (!isset($_POST['edit_id'], $_POST['question'], $_POST['answer'], $_POST['time'], $_POST['points'], $_POST['question_type'])) {
        $response['error'] = true;
        $response['message'] = "Pass all required fields";
        error_log("Missing required fields in update_question request: " . json_encode($_POST));
        echo json_encode($response);
        return false;
    }

    $edit_id = (int) $_POST['edit_id']; // Ensure it's an integer
    $question =  $_POST['question'];
    $optiona = isset($_POST['optiona']) ? $_POST['optiona'] : "";
    $optionb = isset($_POST['optionb']) ?  $_POST['optionb'] : "";
    $optionc = isset($_POST['optionc']) ?  $_POST['optionc'] : "";
    $optiond = isset($_POST['optiond']) ?  $_POST['optiond'] : "";
    $optione = isset($_POST['optione']) ?  $_POST['optione'] : "";
    $points = (int) $_POST['points'];
    $answer = $_POST['answer'];
    $time = $_POST['time'];
    $question_type = (int) $_POST['question_type'];

    // Start SQL query
    $sql = "UPDATE teacher_questions SET question = '$question'";

    if ($question_type == 1) {
        $sql .= ", optiona = '$optiona', optionb = '$optionb', optionc = '$optionc', optiond = '$optiond', optione = '$optione'";
    } elseif ($question_type == 3) {
        $sql .= ", optiona = '$optiona', optionb = '$optionb'";
    }

    $sql .= ", answer = '$answer', time = '$time', points = $points WHERE id = $edit_id";

    // Execute query
    if ($db->sql($sql)) {
        $response['error'] = 'false';
        $response['message'] = "Question updated successfully";
    } else {
        $response['error'] = 'true';
        $response['message'] = "Failed to update question";
    }

    echo json_encode($response);
    exit;
}


if (isset($_POST['access_key']) && isset($_POST['update_interactive_question']) && $_POST['update_interactive_question'] == 1) {
    error_log("Update Interactive Question Request: ");
    if (!verify_token()) {
        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = true;
        $response['message'] = "Invalid Access Key";
        echo json_encode($response);
        return false;
    }

    if (!isset($_POST['edit_id'], $_POST['question'], $_POST['answer'], $_POST['time'], $_POST['points'], $_POST['question_type'], $_POST['video_time'])) {
        $response['error'] = true;
        $response['message'] = "Pass all required fields";
        echo json_encode($response);
        error_log("Missing required fields in update_interactive_question request: " . json_encode($_POST));

        return false;
    }

    $edit_id = (int) $_POST['edit_id']; // Ensure it's an integer
    $question = $db->escapeString($_POST['question']);
    $optiona = isset($_POST['optiona']) ? $db->escapeString($_POST['optiona']) : "";
    $optionb = isset($_POST['optionb']) ? $db->escapeString($_POST['optionb']) : "";
    $optionc = isset($_POST['optionc']) ? $db->escapeString($_POST['optionc']) : "";
    $optiond = isset($_POST['optiond']) ? $db->escapeString($_POST['optiond']) : "";
    $optione = isset($_POST['optione']) ? $db->escapeString($_POST['optione']) : "";
    $points = (int) $_POST['points'];
    $answer = $db->escapeString($_POST['answer']);
    $time = $db->escapeString($_POST['time']);
    $video_time = isset($_POST['video_time']) ? $db->escapeString($_POST['video_time']) : ''; // Optional field
    $question_type = (int) $_POST['question_type'];

    // Start SQL query
    $sql = "UPDATE teacher_questions SET question = '$question'";

    if ($question_type == 1) {
        $sql .= ", optiona = '$optiona', optionb = '$optionb', optionc = '$optionc', optiond = '$optiond', optione = '$optione'";
    } elseif ($question_type == 3) {
        $sql .= ", optiona = '$optiona', optionb = '$optionb'";
    }

    $sql .= ", answer = '$answer', time = '$time', points = '$points', video_time = '$video_time' WHERE id = $edit_id";

    // Execute query
    if ($db->sql($sql)) {
        $response['error'] = 'false';
        $response['message'] = "Question updated successfully";
    } else {
        $response['error'] = 'true';
        $response['message'] = "Failed to update question";
    }
    error_log("Update Interactive Question SQL: " . $sql);
    echo json_encode($response);
    exit;
};


if (isset($_POST['access_key']) && isset($_POST['delete_question']) && $_POST['delete_question'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }
    $response = array();
    if (isset($_POST['question_id'])) {
        $question_id = $_POST['question_id'];
        $sql = "DELETE from teacher_questions WHERE id ='$question_id'";

        $result = $db->sql($sql);
        if ($result) {


            $response['error'] = "false";
        } else {
            $response['error'] = "true";

            $response['message'] = 'not deleted';
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }

    print_r(json_encode($response));
    exit;
};




if (isset($_POST['access_key']) && isset($_POST['update_category_video_id']) && $_POST['update_category_video_id'] == 1) {
    if (!verify_token()) {
        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        echo json_encode($response);
        return false;
    }

    if (isset($_POST['category_uid'], $_POST['video_id'])) {
        $category_uid = $db->escapeString($_POST['category_uid']);
        $video_id = $db->escapeString($_POST['video_id']);


        $sql = "UPDATE teacher_category SET video_id = '$video_id' WHERE uid = '$category_uid'";
        if ($db->sql($sql)) {
            $response['error'] = "false";
            $response['message'] = "Category updated successfully";
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to update category";
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "Please provide category_uid, name, quiz_type, and teacher_id";
    }
    echo json_encode($response);
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['create_class']) && $_POST['create_class'] == 1) {



    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['name'])  || !isset($_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide name, guardian, and teacher_id";
        print_r(json_encode($response));
        return false;
    }

    $name = $db->escapeString($_POST['name']);
    $teacher_id =  $db->escapeString($_POST['teacher_id']);

    $sql = "INSERT INTO teacher_classes (name, teacher_id) VALUES ('$name', '$teacher_id')";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Class created successfully";
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to create class";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['fetch_classes']) && $_POST['fetch_classes'] == 1) {



    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide teacher_id";
        print_r(json_encode($response));
        return false;
    }


    $teacher_id =  $db->escapeString($_POST['teacher_id']);

    $sql = "SELECT 
    tc.*, 
    COUNT(tcs.id) AS student_count 
FROM 
    teacher_classes tc 
LEFT JOIN 
    teacher_class_students tcs 
    ON tcs.class_id = tc.id 
    AND tcs.teacher_id = tc.teacher_id 
WHERE 
    tc.teacher_id = '$teacher_id' 
GROUP BY 
    tc.id
";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Classes fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch classes";
    }
    print_r(json_encode($response));
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['add_student']) && $_POST['add_student'] == 1) {



    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['name'])  || !isset($_POST['teacher_id']) || !isset($_POST['class_id']) || !isset($_POST['guardian']) || !isset($_POST['user_id'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide name, guardian, teacher_id, class_id, and user_id";
        print_r(json_encode($response));
        return false;
    }

    $name = $db->escapeString($_POST['name']);
    $teacher_id =  $db->escapeString($_POST['teacher_id']);
    $class_id =  $db->escapeString($_POST['class_id']);
    $guardian =  $db->escapeString($_POST['guardian']);
    $user_id =  $db->escapeString($_POST['user_id']);

    $sql = "INSERT INTO teacher_class_students (name, teacher_id, class_id, guardian, user_id) VALUES ('$name', '$teacher_id', '$class_id', '$guardian', '$user_id')";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Student added successfully";
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to add student";
    }
    print_r(json_encode($response));
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['fetch_students_class_details']) && $_POST['fetch_students_class_details'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id']) || !isset($_POST['class_id'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide teacher_id and class_id";
        print_r(json_encode($response));
        return false;
    }

    $teacher_id =  $db->escapeString($_POST['teacher_id']);
    $class_id =  $db->escapeString($_POST['class_id']);

    $sql = "SELECT tcs.*, tc.name as class_name 
            FROM teacher_classes tc 
            LEFT JOIN teacher_class_students tcs 
            ON tc.id = tcs.class_id 
            WHERE tc.id = '$class_id' AND tc.teacher_id = '$teacher_id'";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Students fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch students";
    }
    print_r(json_encode($response));
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['fetch_students_from_classes']) && $_POST['fetch_students_from_classes'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id']) || !isset($_POST['classes'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide teacher_id and class_id";
        print_r(json_encode($response));
        return false;
    }

    $teacher_id =  $db->escapeString($_POST['teacher_id']);
    $classes_array = json_decode($_POST['classes'], true);

    $where = "WHERE teacher_id=$teacher_id";

    if (!empty($classes_array)) {
        $result = implode(", ", $classes_array);
        $where = "WHERE class_id IN ($result) AND teacher_id =$teacher_id";
    }

    $sql = "SELECT * FROM teacher_class_students $where";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Students fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch students";
    }
    print_r(json_encode($response));
    return false;
}





if (isset($_POST['access_key']) && isset($_POST['fetch_student']) && $_POST['fetch_student'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['user_id']) || !isset($_POST['class_id'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide user_id and class_id";
        print_r(json_encode($response));
        return false;
    }

    $user_id =  $db->escapeString($_POST['user_id']);
    $class_id =  $db->escapeString($_POST['class_id']);

    $sql = "SELECT teacher_class_students.*, tc.name as class_name, u.name as teacher_name FROM teacher_class_students JOIN teacher_classes tc ON teacher_class_students.class_id = tc.id JOIN users u ON u.id = teacher_class_students.teacher_id WHERE teacher_class_students.user_id = '$user_id' AND teacher_class_students.class_id = '$class_id'";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Student fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch student";
    }
    print_r(json_encode($response));
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['get_user']) && $_POST['get_user'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['user_id'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide user_id ";
        print_r(json_encode($response));
        return false;
    }

    $user_id =  $db->escapeString($_POST['user_id']);

    $sql = "SELECT name FROM  users  WHERE id = '$user_id'";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "User fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch student";
    }
    print_r(json_encode($response));
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['teacher_categories']) && $_POST['teacher_categories'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['user_id'])) {
        $response['error'] = "true";
        $response['message'] = "Please provide user_id ";
        print_r(json_encode($response));
        return false;
    }

    $user_id =  $db->escapeString($_POST['user_id']);

    $sql = "SELECT name FROM  users  WHERE id = '$user_id' visibility ='public' AND publish ='true'";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "User fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch student";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['explore_junior_categories']) && $_POST['explore_junior_categories'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }


    $sql = "SELECT name,uid,image FROM  teacher_category WHERE grade ='junior'";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch student";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['explore_senior_categories']) && $_POST['explore_senior_categories'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }


    $sql = "SELECT name,uid,image FROM  teacher_category WHERE grade ='senior' OR grade='higher'";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch student";
    }
    print_r(json_encode($response));
    return false;
}



if (isset($_POST['access_key']) && isset($_POST['get_categories']) && $_POST['get_categories'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['type'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $type = $_POST['type'];

    $sql = "SELECT name,uid,image FROM  teacher_category WHERE subject ='$type'";
    if ($db->sql($sql)) {
        $response['error'] = "false";
        $response['message'] = "Fetched successfully";
        $response['data'] = $db->getResult();
    } else {
        $response['error'] = "true";
        $response['message'] = "Failed to fetch student";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['get_category_with_questions']) && $_POST['get_category_with_questions'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['uid'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $uid = $_POST['uid'];
    $result = [];

    $sql = "SELECT tc.name,tc.uid,tc.image,us.name as teacher_name, us.profile FROM  teacher_category tc JOIN 
    users us 
    ON us.id = tc.teacher_id  WHERE tc.uid='$uid' AND tc.visibility ='public' AND tc.publish ='true'";

    $db->sql($sql);
    $result['category'] = $db->getResult();

    if (isset($result['category']['profile'])) {
        if (!filter_var($question['profile'], FILTER_VALIDATE_URL)) {
            $result['category']['profile'] = !empty($result['category']['profile'])
                ? DOMAIN_URL . 'uploads/profile/' . $result['category']['profile']
                : '';
        }
    }

    $sql = "SELECT COUNT(id) FROM  teacher_questions  WHERE category_uid='$uid' ";
    $db->sql($sql);
    $result['questions'] = $db->getResult();





    if (!empty($result)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",
            "data" => $result
        ]);
    } else if (empty($result)) {
        http_response_code(500);
        echo json_encode([
            "error" => false,
            "message" => "successfully",
            "data" => $result
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['get_library_category_info']) && $_POST['get_library_category_info'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $uid = $_POST['teacher_id'];
    $sql = "SELECT 
                (SELECT COUNT(*) FROM teacher_category WHERE teacher_id='$uid' AND is_copied=false) as created_by_me,
                (SELECT COUNT(*) FROM teacher_quiz_likes WHERE teacher_id='$uid') as like_by_me,
                (SELECT COUNT(*) FROM teacher_category WHERE teacher_id='$uid') as all_content";


    $db->sql($sql);

    $result = $db->getResult();

    if (!empty($result)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",
            "data" => $result
        ]);
    } else if (empty($result)) {
        http_response_code(500);
        echo json_encode([
            "error" => false,
            "message" => "successfully",
            "data" => $result
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['get_liked_quizzes']) && $_POST['get_liked_quizzes'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $sql = "SELECT 
    tql.teacher_id,
    tql.quiz_id,
    tc.uid,
    tc.name,
    tc.subject,
    tc.grade,
    tc.image,
    tc.quiz_type,
    COUNT(DISTINCT tq.id) AS question_count
FROM teacher_quiz_likes tql
JOIN teacher_category tc ON tc.uid = tql.quiz_id
LEFT JOIN teacher_questions tq ON tq.category_uid = tc.uid
WHERE tql.teacher_id = '$teacher_id'
GROUP BY tql.quiz_id;
";





    $db->sql($sql);

    $result = $db->getResult();

    if (!empty($result)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",
            "data" => $result
        ]);
    } else if (empty($result)) {
        http_response_code(500);
        echo json_encode([
            "error" => false,
            "message" => "successfully",
            "data" => $result
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['like_quiz']) && $_POST['like_quiz'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'], $_POST['quiz_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $quiz_id = $_POST['quiz_id'];
    $sql = "INSERT INTO teacher_quiz_likes (teacher_id, quiz_id) VALUES ('$teacher_id', '$quiz_id')
";









    try {
        $sql = "INSERT INTO teacher_quiz_likes (teacher_id, quiz_id) 
            VALUES ('$teacher_id', '$quiz_id')";
        $db->sql($sql);

        echo json_encode([
            "error" => false,
            "message" => "Quiz liked successfully"
        ]);
    } catch (Exception $e) {
        if ($db->errno == 1062) { // 1062 = Duplicate entry
            echo json_encode([
                "error" => true,
                "message" => "Quiz already liked"
            ]);
        } else {
            echo json_encode([
                "error" => true,
                "message" => "Error liking quiz: " . $db->error
            ]);
        }
    }
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['get_all_content']) && $_POST['get_all_content'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $sql = "SELECT tc.*,COUNT(tq.id) as question_count FROM teacher_category tc JOIN teacher_questions tq ON tq.category_uid =tc.uid WHERE tc.teacher_id ='$teacher_id' GROUP BY tc.id";


    $db->sql($sql);

    $result = $db->getResult();

    if (!empty($result)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",
            "data" => $result
        ]);
    } else if (empty($result)) {
        http_response_code(500);
        echo json_encode([
            "error" => false,
            "message" => "successfully",
            "data" => $result
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['get_collection']) && $_POST['get_collection'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $sql = "SELECT * FROM teacher_folders WHERE teacher_id='$teacher_id'";






    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",
            "data" => $db->getResult()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['create_collection']) && $_POST['create_collection'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'], $_POST['folder_name'], $_POST['quiz_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $folder_name = $db->escapeString($_POST['folder_name']);
    $quiz_id = $db->escapeString($_POST['quiz_id']);
    $sql = "INSERT INTO  teacher_folders (teacher_id,name) VALUES ('$teacher_id','$folder_name')";


    $db->sql($sql);
    $folder_id = $db->insert_id();
    $sql = "INSERT INTO  teacher_folder_quizzes (folder_id,quiz_id) VALUES ('$folder_id','$quiz_id')";



    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",

        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['add_to_collection']) && $_POST['add_to_collection'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['collections'], $_POST['quiz_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }

    $folder_ids = $_POST['collections'] ?? ''; // e.g. "1,2,3"
    $quiz_id = $db->escapeString($_POST['quiz_id'] ?? '');

    if (!empty($folder_ids) && !empty($quiz_id)) {
        $folder_ids = explode(',', $folder_ids);

        $success = true; // track if all inserts work

        foreach ($folder_ids as $folder_id) {
            $folder_id = $db->escapeString(trim($folder_id));

            if ($folder_id !== '') {
                $sql = "INSERT INTO teacher_folder_quizzes (folder_id, quiz_id) 
                    VALUES ('$folder_id', '$quiz_id')";

                if (!$db->sql($sql)) {
                    $success = false; // mark as failed if any insert fails
                }
            }
        }

        if ($success) {
            http_response_code(200);
            echo json_encode([
                "error" => false,
                "message" => "Inserted successfully"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "One or more inserts failed"
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "error" => true,
            "message" => "Invalid input"
        ]);
    }
}



if (isset($_POST['access_key']) && isset($_POST['copy_quiz']) && $_POST['copy_quiz'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['quiz_id'], $_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $uid = $_POST['quiz_id'];
    $teacher_id = $_POST['teacher_id'];
    $sql = "INSERT INTO teacher_category  (name, subject, grade, image, teacher_id, language, quiz_type, video_id,is_copied,visibility) 
        SELECT name, subject, grade,  image, $teacher_id, language, quiz_type, video_id,true,'private'
        FROM teacher_category 
        WHERE uid = '$uid'";

    $db->sql($sql);


    $new_quiz_id = "SELECT uid FROM teacher_category WHERE id = " . $db->insert_id();
    $db->sql($new_quiz_id);
    $new_quiz_id = $db->getResult();
    $new_quiz_id = $new_quiz_id[0]['uid'];


    $sql = "INSERT INTO teacher_questions (
                     category_uid, question, question_type, 
                    optiona, optionb, optionc, optiond, optione, 
                    points, answer, time, teacher_id,video_time
                ) 
        SELECT '$new_quiz_id', question, question_type, 
                    optiona, optionb, optionc, optiond, optione, 
                    points, answer, time, $teacher_id,video_time
        FROM teacher_questions 
        WHERE category_uid = '$uid'";




    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",

        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['create_collection_folder']) && $_POST['create_collection_folder'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'], $_POST['folder_name'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $folder_name = $db->escapeString($_POST['folder_name']);
    $sql = "INSERT INTO  teacher_folders (teacher_id,name) VALUES ('$teacher_id','$folder_name')";






    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",

        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['get_folder_quiz']) && $_POST['get_folder_quiz'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['folder_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $folder_id = $_POST['folder_id'];

    $sql = "SELECT quiz_id FROM teacher_folder_quizzes 
            
            WHERE folder_id='$folder_id'";

    $db->sql($sql);
    $result = $db->getResult();

    if (!empty($result)) {
        $quiz_ids = array_column($result, 'quiz_id');
        $quiz_ids_str = implode("','", $quiz_ids);

        $sql = "SELECT tc.*,COUNT(tq.id) as question_count FROM teacher_category tc JOIN teacher_questions tq ON tq.category_uid =tc.uid WHERE uid IN ('$quiz_ids_str') GROUP BY tc.id";

        if ($db->sql($sql)) {
            http_response_code(200);
            echo json_encode([
                "error" => false,
                "message" => "Fetched successfully",
                "data" => $db->getResult()

            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Failed to fetch data"
            ]);
        }
    } else {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "No quizzes found in this folder",
            "data" => []

        ]);
    }








    return false;
}


if (isset($_POST['access_key']) && isset($_POST['assign_quiz']) && $_POST['assign_quiz'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'], $_POST['category_uid'], $_POST['access_code'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }

    $teacher_id = $_POST['teacher_id'];
    $category_uid = $db->escapeString($_POST['category_uid']);
    $timer = isset($_POST['timer']) ? $db->escapeString($_POST['timer']) : 0;
    $attempts = isset($_POST['attempts']) ? $db->escapeString($_POST['attempts']) : 0;
    $show_answers = isset($_POST['show_answers']) ? $db->escapeString($_POST['show_answers']) : 'false';
    $schuffle_questions = isset($_POST['shuffle_questions']) ? $db->escapeString($_POST['shuffle_questions']) : 'false';
    $memes = isset($_POST['memes']) ? $db->escapeString($_POST['memes']) : 'false';
    $deadline = isset($_POST['deadline']) ? $db->escapeString($_POST['deadline']) : null;
    $start_time = isset($_POST['start_time']) ? $db->escapeString($_POST['start_time']) : null;
    $students = isset($_POST['students']) ? json_decode($_POST['students'], true) : [];
    $access_code = $db->escapeString($_POST['access_code']);


    $sql = "INSERT INTO teacher_assign (teacher_id, category_id, timer, attempt, show_answers, schuffle, memes, deadline,access_code,start_time) VALUES ('$teacher_id', '$category_uid', '$timer', '$attempts', '$show_answers', '$schuffle_questions', '$memes', '$deadline', '$access_code','$start_time')";

    if ($db->sql($sql)) {

        $assigned_quiz_id = $db->insert_id();
        $response['error'] = "false";
        $response['message'] = "Quiz assigned successfully";
        $response['assigned_quiz_id'] = $assigned_quiz_id;
    } else {
        $response['error'] = "true";

        $error_M = $db->getResult();

        $response['message'] = $error_M['error'] ?? "Failed to assign quiz";

        echo (json_encode($response));
        return false;
    }

    if (!empty($students)) {
        // Make sure you have a DB connection $db (PDO or mysqli)


        foreach ($students as $student) {
            // Protect against missing keys
            $class_id   = isset($student['class_id']) ? intval($student['class_id']) : 0;
            $student_id = isset($student['id']) ? intval($student['id']) : 0;

            if ($class_id > 0 && $student_id > 0) {
                $db->sql("INSERT INTO teacher_assigned_students (class_id, student_id,assign_id) VALUES ('$class_id', '$student_id','$assigned_quiz_id')");
            }
        }
    }

    echo (json_encode($response));
    return false;
}



if (isset($_POST['access_key']) && isset($_POST['edit_assign_quiz']) && $_POST['edit_assign_quiz'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'], $_POST['category_uid'], $_POST['id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $id = $_POST['id'];
    $teacher_id = $_POST['teacher_id'];
    $category_uid = $db->escapeString($_POST['category_uid']);
    $timer = isset($_POST['timer']) ? $db->escapeString($_POST['timer']) : 0;
    $attempts = isset($_POST['attempts']) ? $db->escapeString($_POST['attempts']) : 0;
    $show_answers = isset($_POST['show_answers']) ? $db->escapeString($_POST['show_answers']) : 'false';
    $schuffle_questions = isset($_POST['shuffle_questions']) ? $db->escapeString($_POST['shuffle_questions']) : 'false';
    $memes = isset($_POST['memes']) ? $db->escapeString($_POST['memes']) : 'false';
    $deadline = isset($_POST['deadline']) ? $db->escapeString($_POST['deadline']) : null;
    $students = isset($_POST['students']) ? json_decode($_POST['students'], true) : [];




    $sql = "UPDATE teacher_assign SET teacher_id='$teacher_id', category_id='$category_uid',timer='$timer', attempt='$attempts',  show_answers='$show_answers', schuffle='$schuffle_questions', memes='$memes',  deadline='$deadline' WHERE category_id='$category_uid' AND teacher_id='$teacher_id' AND id='$id'";



    if ($db->sql($sql)) {

        $response['error'] = "false";
        $response['message'] = "Quiz assigned successfully";
        $response['assigned_quiz_id'] = $assigned_quiz_id;
    } else {
        $response['error'] = "true";

        $error_M = $db->getResult();

        $response['message'] = $error_M['error'] ?? "Failed to assign quiz";

        echo (json_encode($response));
        return false;
    }

    if (!empty($students)) {
        // Make sure you have a DB connection $db (PDO or mysqli)


        foreach ($students as $student) {
            // Protect against missing keys
            $class_id   = isset($student['class_id']) ? intval($student['class_id']) : 0;
            $student_id = isset($student['id']) ? intval($student['id']) : 0;

            if ($class_id > 0 && $student_id > 0) {
                $db->sql("  INSERT INTO teacher_assigned_students (class_id, student_id, assign_id)
            VALUES ('$class_id', '$student_id', '$id')
            ON DUPLICATE KEY UPDATE 
                class_id = VALUES(class_id),
                student_id = VALUES(student_id),
                assign_id = VALUES(assign_id)");
            }
        }
    }

    echo (json_encode($response));
    return false;
}



if (isset($_POST['access_key']) && isset($_POST['assign_quiz_details']) && $_POST['assign_quiz_details'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'], $_POST['category_uid'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $category_uid = $db->escapeString($_POST['category_uid']);
    $sql = "SELECT ta.*,tc.name,COUNT(tq.id) as questions,us.name as teacher FROM teacher_assign ta
JOIN teacher_category tc 
    ON tc.uid = ta.category_id
JOIN teacher_questions tq 
    ON tq.category_uid = tc.uid
JOIN users us 
    ON us.id = ta.teacher_id 
     WHERE ta.teacher_id='$teacher_id' AND ta.category_id='$category_uid'";






    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Fetched successfully",
            "data" => $db->getResult()

        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}

if (isset($_POST['access_key']) && isset($_POST['end_assigned_quiz']) && $_POST['end_assigned_quiz'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['teacher_id'], $_POST['category_uid'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $teacher_id = $_POST['teacher_id'];
    $category_uid = $db->escapeString($_POST['category_uid']);
    $sql = "UPDATE teacher_assign SET deadline=NOW() WHERE teacher_id='$teacher_id' AND category_id='$category_uid'";






    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Update successfully",


        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to update data"
        ]);
    }
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['get_assignment']) && $_POST['get_assignment'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['student_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        return false;
    }
    $student_id = $_POST['student_id'];
    $sql = "SELECT tcs.*,tc.name,tc.image,tc.uid  as category_id,ta.id as assign_id
FROM teacher_class_students tcs
JOIN teacher_assigned_students tas ON tcs.id = tas.student_id
JOIN teacher_assign ta ON ta.id = tas.assign_id
AND ta.deadline > NOW()
join teacher_category tc ON tc.uid = ta.category_id
WHERE tcs.user_id = '$student_id'";






    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "fetched successfully",
            'data' => $db->getResult()


        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['fetch_report']) && $_POST['fetch_report'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (!isset($_POST['type'], $_POST['teacher_id'])) {
        $response['error'] = "true";
        $response['message'] = "missing field";
        print_r(json_encode($response));
        return false;
    }
    $type = $_POST['type'];
    $teacher_id = $_POST['teacher_id'];

    $sql = "SELECT ta.start_time,ta.access_code,ta.category_id,ta.deadline,tc.name,tc.quiz_type,COUNT(tas.assign_id) as students FROM teacher_assign ta
    JOIN teacher_assigned_students tas ON tas.assign_id = ta.id
JOIN teacher_category tc ON tc.uid = ta.category_id
WHERE ta.teacher_id = '$teacher_id'";


    if ($type == 'running') {
        $sql .= " AND ta.deadline > NOW() AND ta.start_time < NOW()";
    } elseif ($type == 'completed') {
        $sql .= " AND ta.deadline < NOW()";
    } elseif ($type == 'scheduled') {
        $sql .= " AND ta.deadline > NOW() AND ta.start_time > NOW()";
    }



    $sql .= " GROUP BY tas.assign_id";



    if ($db->sql($sql)) {
        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "fetched successfully",
            'data' => $db->getResult()


        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => "Failed to fetch data"
        ]);
    }
    return false;
}


if (isset($_POST['access_key']) && isset($_POST['get_category_and_assigned_details']) && $_POST['get_category_and_assigned_details'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (isset($_POST['quiz_id'], $_POST['assign_id'])) {

        $category_uid = $db->escapeString($_POST['quiz_id']);
        $assign_id = $db->escapeString($_POST['assign_id']);
        $sql = "SELECT 
    ta.*, 
    us.profile, 
    us.name AS teacher_name,
    tc.quiz_type
FROM teacher_assign ta
JOIN teacher_category tc 
    ON tc.uid = ta.category_id
JOIN users us 
    ON us.id = tc.teacher_id
WHERE ta.id='$assign_id' AND ta.category_id='$category_uid'
";

        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $sql_2 = "SELECT  COUNT(id) as count FROM teacher_student_score WHERE student_id =$id";

            if ($db->sql($sql_2)) {
                $count_result = $db->getResult();
                $response['attempt_count'] =  $count_result;
            }
        }

        if ($db->sql($sql)) {
            $result = $db->getResult();
            if ($result[0]['profile']) {
                if (filter_var($result[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                    // Not a valid URL. Its a image only or empty
                    $result[0]['profile'] = (!empty($result[0]['profile'])) ? DOMAIN_URL . 'uploads/profile/' . $result[0]['profile'] : '';
                } else {
                    /* if it is a ur than just pass url as it is */
                    $result[0]['profile'] = $result[0]['profile'];
                }
            }


            $response['error'] = false;
            $response['message'] = "fetched successfully";
            $response['data'] =  $result;
        } else {
            $response['error'] = "true";
            $response['message'] = "Failed to fetch category";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
};



if (isset($_POST['access_key']) && isset($_POST['report_quiz_details']) && $_POST['report_quiz_details'] == 1) {

    if (!verify_token()) {

        return false;
    }

    if ($access_key != $_POST['access_key']) {
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if (isset($_POST['assign_id'])) {

        $assign_id = $db->escapeString($_POST['assign_id']);

        $sql = "SELECT tss.*,us.name FROM teacher_student_score tss
        JOIN users us ON us.id = tss.student_id

        WHERE tss.assign_id=$assign_id";

        $sql_1 = "SELECT 
    COUNT(DISTINCT tas.student_id) AS total_assigned,
    COUNT(DISTINCT tss.student_id) AS total_completed,
    ROUND(
        (COUNT(DISTINCT tss.student_id) / COUNT(DISTINCT tas.student_id)) * 100, 2
    ) AS completion_rate,
    ROUND(AVG((tss.score / tss.total_questions) * 100), 2) AS average_score_percent,
    ROUND(MAX((tss.score / tss.total_questions) * 100), 2) AS max_score_percent,
    ROUND(MIN((tss.score / tss.total_questions) * 100), 2) AS min_score_percent
FROM teacher_assign ta
JOIN teacher_assigned_students tas 
    ON tas.assign_id = ta.id
LEFT JOIN teacher_student_score tss 
    ON tss.assign_id = ta.id 
WHERE ta.id = '$assign_id'";

        if ($db->sql($sql)) {
            $result = $db->getResult();

            $response['data'] =  $result;

            if ($db->sql($sql_1)) {
                $result_1 = $db->getResult();
                $response['summary'] =  $result_1;
            }
            $response['error'] = false;
            $response['message'] = "fetched successfully";
        } else {
            $response['error'] = true;
            $response['message'] = "Failed to fetch";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
};
