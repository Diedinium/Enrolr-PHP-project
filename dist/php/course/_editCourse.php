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
    if (!isset($_POST['editId'])) {
        echo json_encode(['success' => 0, 'message' => 'Something went wrong, this request could not be processed']);
        die;
    }

    $courseId = $_POST['editId'];
    $title = $_POST['editTitle'];
    $date = $_POST['editDate'];
    $duration = $_POST['editDuration'];
    $maxAttendees = $_POST['editMaxAttendees'];
    $description = $_POST['editDescription'];
    $link = $_POST['editLink'];
    $location = $_POST['editLocation'];
    try {
        Course::editCourse($courseId, $title, $date, $duration, $maxAttendees, $description, $link, $location);

        echo json_encode(["success" => 1, "message" => "Course with id of $courseId succesfully updated."]);
        die;
    }
    catch (Exception $ex) {
        echo json_encode(['success' => 0, 'message' => $ex->getMessage()]);
        die;
    }
}