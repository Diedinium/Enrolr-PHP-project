<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../account/_auth.php';
require __DIR__ . '/../classes/_course.php';

if (!$account->getAuthenticated()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if (!empty($_GET['searchMinDate'])) {
    try {
        $testDate = new DateTime($_GET['searchMinDate']);
    }
    catch (Exception $ex) {
        echo json_encode(["success" => 0, "message" => "Something went wrong while processing search dates for this request."]);
        die;
    }
}

if (!empty($_GET['searchMaxDate'])) {
    try {
        $testDate = new DateTime($_GET['searchMaxDate']);
    }
    catch (Exception $ex) {
        echo json_encode(["success" => 0, "message" => "Something went wrong while processing search dates for this request."]);
        die;
    }
}

echo json_encode(
    [
        "success" => 1,
        "isAdmin" => $account->getIsAdmin(),
        "data" => Course::getPastCourses($account->getId(), $_GET['pageIndex'], $_GET['searchPastMinDate'], $_GET['searchPastMaxDate'], $_GET['searchPastTitle'])
    ]
);
die;