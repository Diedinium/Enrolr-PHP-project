<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../account/_auth.php';

// Return error if user is not authenticated
if (!$account->getAuthenticated()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

// Return different error if admin is attempting to enrol on course
if ($account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "Admins are not able to enrol on courses, please use a normal user account."]);
    die;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $courseId = $_POST['id'];

    try {
        $account->enroll($courseId);

        echo json_encode(["success" => 1, "message" => "Succesfully enrolled on course."]);
        die;
    }
    catch (Exception $ex) {
        echo json_encode(['success' => 0, 'message' => $ex->getMessage()]);
        die;
    }
}