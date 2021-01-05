<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../account/_auth.php';
require __DIR__ . '/../classes/_course.php';

// Return error if user is not authenticated or is an admin
if (!$account->getAuthenticated() || $account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

// Get past enrolments and return array as JSON.
echo json_encode(
    [
        "success" => 1,
        "data" => Course::getPastEnrolments($_GET['pageIndex'])
    ]
);
die;