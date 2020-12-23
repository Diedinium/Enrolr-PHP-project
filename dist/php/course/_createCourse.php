<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../classes/_course.php';
require __DIR__ . '/../account/_auth.php';

if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $title = $_POST['createTitle'];
    $date = $_POST['createDate'];
    $duration = $_POST['createDuration'];
    $maxAttendees = $_POST['createMaxAttendees'];
    $description = $_POST['createDescription'];
    $link = $_POST['createLink'];
    $location = $_POST['createLocation'];
    try {
        $result = Course::createCourse($title, $date, $duration, $maxAttendees, $description, $link, $location);

        echo json_encode(["success" => 1, "message" => "Course with id of $result succesfully created."]);
        die;
    }
    catch (Exception $ex) {
        echo json_encode(['success' => 0, 'message' => $ex->getMessage()]);
        die;
    }
}