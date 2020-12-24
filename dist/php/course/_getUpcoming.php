<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../account/_auth.php';
require __DIR__ . '/../classes/_course.php';

if (!$account->getAuthenticated()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

echo json_encode(
    [
        "success" => 1,
        "isAdmin" => $account->getIsAdmin(),
        "data" => Course::getUpcomingCourses($account->getId(), $_GET['pageIndex'])
    ]
);
die;
