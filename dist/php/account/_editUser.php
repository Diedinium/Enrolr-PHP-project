<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (!isset($_POST['updateUserId'])) {
        echo json_encode(['success' => 0, 'message' => 'Something went wrong, this request could not be processed']);
        die;
    }

    $userId = $_POST['updateUserId'];
    $email = $_POST['updateEmail'];
    $firstName = $_POST['updateFirstName'];
    $lastName = $_POST['updateLastName'];
    $jobRole = $_POST['updateJobRole'];
    $accountToDelete = new Account();
    $accountToDelete->setId($userId);
    try {
        $accountToDelete->editUser($firstName, $lastName, $email, $jobRole);

        echo json_encode(["success" => 1, "message" => "User with id $userId succesfully updated."]);
        die;
    }
    catch (Exception $ex) {
        echo json_encode(['success' => 0, 'message' => $ex->getMessage()]);
        die;
    }
}