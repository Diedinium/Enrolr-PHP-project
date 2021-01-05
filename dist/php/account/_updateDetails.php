<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

// Redirect if user is not logged in
if (!$account->getAuthenticated()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Return error if necessary details are not provided.
    if (!isset($_POST['firstName']) || !isset($_POST['lastName']) || !isset($_POST['jobRole'])) {
        echo json_encode(["success" => 0, "message" => "Something went wrong while processing this request."]);
        die;
    }
    else {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $jobRole = $_POST['jobRole'];

        try {
            $account->updateDetails($firstName, $lastName, $jobRole);

            echo json_encode(["success" => 1, "message" => "Details successfully saved."]);
            die;
        }
        catch (Exception $ex) {
            echo json_encode(["success" => 1, "message" => $ex->getMessage()]);
            die;
        }
    }
}
