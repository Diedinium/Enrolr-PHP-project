<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../account/_auth.php';
require __DIR__ . '/../classes/_course.php';

// If user is not autenticated, return error.
if (!$account->getAuthenticated()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

// If searchMinDate is not a valid date, throw an error.
if (!empty($_GET['searchMinDate'])) {
    try {
        $testDate = new DateTime($_GET['searchMinDate']);
    }
    catch (Exception $ex) {
        echo json_encode(["success" => 0, "message" => "Something went wrong while processing search dates for this request."]);
        die;
    }
}

// If searchMaxDate is not a valid date, throw an error.
if (!empty($_GET['searchMaxDate'])) {
    try {
        $testDate = new DateTime($_GET['searchMaxDate']);
    }
    catch (Exception $ex) {
        echo json_encode(["success" => 0, "message" => "Something went wrong while processing search dates for this request."]);
        die;
    }
}

// Get upcoming courses and return array as JSON.
echo json_encode(
    [
        "success" => 1,
        "isAdmin" => $account->getIsAdmin(),
        "data" => Course::getUpcomingCourses($account->getId(), $_GET['pageIndex'], $_GET['searchMinDate'], $_GET['searchMaxDate'], $_GET['searchTitle'])
    ]
);
die;
