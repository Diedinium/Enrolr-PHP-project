<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../classes/_course.php';
require __DIR__ . '/../account/_auth.php';

// Return error if user is not authenticated or is not an admin.
if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $userId = $_POST['userId'];
    $courseId = $_POST['courseId'];

    try {
        Course::unenrolUser($courseId, $userId);

        echo json_encode(["success" => 1, "message" => "Enrollment successfully removed."]);
        die;
    }
    catch (Exception $ex) {
        echo json_encode(['success' => 0, 'message' => $ex->getMessage()]);
        die;
    }
}