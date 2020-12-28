<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../account/_auth.php';
require __DIR__ . '/../classes/_course.php';

if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

echo json_encode(
    [
        "success" => 1,
        "data" => Course::getUsersEnrolledOnCourse($_GET['courseId'], $_GET['pageIndex'])
    ]
);
die;