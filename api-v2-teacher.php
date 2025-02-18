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


            $sql = "INSERT INTO teacher_questions (
                     category_uid, question, question_type, 
                    optiona, optionb, optionc, optiond, optione, 
                    points, answer, time,teacher_id
                ) VALUES (
                    '$category_uid','$question_text', '$question_type',
                    '$optiona', '$optionb', '$optionc', '$optiond', '$optione',
                    $points, '$answer','$time',$teacher_id
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
}


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
    $success = true;

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
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true); // Create the directory if it doesn't exist
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
}

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
}


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
}


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
}


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


    if ($_POST['teacher_id']) {

        $uids = json_decode($_POST['list'], true);

        $idList = implode(',', array_map('intval', $uids));
        $teacher_id = $db->escapeString($_POST['teacher_id']);

        $sql = "SELECT tc.*, COUNT(tq.category_uid) AS question_count
        FROM teacher_category tc
        LEFT JOIN teacher_questions tq ON tq.category_uid = tc.uid AND tc.teacher_id = tq.teacher_id
        WHERE tc.teacher_id = '$teacher_id' AND tc.uid IN ($idList)
		   GROUP BY tq.category_uid
     	ORDER BY tc.id DESC
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
    } else {
        $response['error'] = "true";
        $response['message'] = "Pass all field";
        print_r(json_encode($response));
        return false;
    }

    print_r(json_encode($response));
    return false;
}

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






    $sql = "SELECT tc.*, COUNT(tq.category_uid) AS question_count
FROM teacher_category tc
LEFT JOIN teacher_questions tq ON tq.category_uid = tc.uid AND tc.teacher_id = tq.teacher_id WHERE
tc.publish='true' AND tc.visibility='public'
GROUP BY tc.uid
ORDER BY tc.likes DESC,tc.views DESC";




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
}

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
}





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
}

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
}





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
}


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
}

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
}

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
	
	if(isset($_POST['category'],$_POST['subcategory'],$_FILES['questions'],$_POST['teacher_id'])){
		
		 if (isset($_FILES['questions']) && $_FILES['questions']['error'] === UPLOAD_ERR_OK) {
        $csvFile = $_FILES['questions']['tmp_name'];
        
        // Open and read the CSV file
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            // Skip the header row
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
			
			$category=htmlspecialchars($_POST['category']);
				$subcategory=htmlspecialchars($_POST['subcategory']);
			$teacher_id=htmlspecialchars($_POST['teacher_id']);
			$image="";
			
			if(isset($_FILES['image'])){
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
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true); // Create the directory if it doesn't exist
            }
            $dest_path = $uploadFileDir . $fileName;
            $image_url = $base_url . $dest_path;

            // Move the file to the specified directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
               $image=$image_url;
            } else {
                $response['error'] = "true";
                $response['message'] = "upload error";
				exit;
            }
				
				
			}
			
            
            
           	$sql="INSERT INTO teacher_school_quiz_category (category_name,image,teacher_id) VALUES ('$category','$image','$teacher_id')";
			if($db->sql($sql)){
			$category_id =$db->insert_id();
			$sql_1="INSERT INTO teacher_school_quiz_subcategory (subcategory_name,category_id) VALUES ('$subcategory','$category_id')";
			$db->sql($sql_1);
			
				
			foreach ($questions as $value) {
                try {
                    $question = $value['question'];
                    $optiona = $value['option_a'];
                    $optionb = $value['option_b'];
                    $optionc = $value['option_c'];
                    $optiond = $value['option_d'];
                    $answer = $value['answer'];
					
            $sql_2="INSERT INTO teacher_school_quiz_questions (question,optiona,optionb,optionc,optiond,answer,category_id) VALUES 				('$question','$optiona','$optionb','$optionc','$optiond','$answer','$category_id')";
                    // Execute the SQL query and check for errors
                    if (!$db->sql($sql_2)) {
                        echo json_encode([
                            'error' => 'true',
                        ]);
                        exit;
                    }
                } catch (Exception $e) {
                    // Log the error and return error response
                    error_log("Error processing question: " . $e->getMessage());
                    echo json_encode([
                        'error' => 'true',
                        'message' => 'Error processing questions: ' . $e->getMessage()
                    ]);
                    exit;
                }
          		
        }
        
        echo json_encode(['error' => 'false','success' => true]);
				exit;
	}
				
	 } else {
            echo json_encode([
                'error' => 'true',
                'message' => 'Failed to upload csv'
            ]);
			exit;
        }
			 
			 
			 
    } else {
        echo json_encode([
            'error' => 'true',
            'message' => 'No file uploaded or upload error'
        ]);
			 exit;
    }


	} else {
        $response['error'] = "true";
        $response['message'] = "pass all fields";
    }
	 print_r(json_encode($response));
    exit;
}


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
	if(isset($_POST['teacher_id'])){
		$teacher_id =$_POST['teacher_id'];
		$sql="SELECT * from teacher_school_quiz_category WHERE teacher_id ='$teacher_id'";
			
		  $result = $db->sql($sql);
    if ($result) {
        $res = $db->getResult();
		$count =$db->numRows($res);
		if($count){
		 $response['error'] = "false";
      
        $response['data'] = $count;
		}else{
		 $response['error'] = "true";
      
        $response['message'] = 'No question available';
		}
       
        
       
    } else {
        $response['error'] = "true";
        $response['message'] = "Database error occurred";
    }
	
	}else{
		  $response['error'] = "true";
        $response['message'] = "pass all fields";
	}
	
	 print_r(json_encode($response));
    exit;
	
}

