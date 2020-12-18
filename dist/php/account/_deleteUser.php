<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (!isset($_POST['id'])) {
        echo json_encode(['success' => 0, 'message' => 'Something went wrong, this request could not be processed']);
        die;
    }

    $idToDelete = $_POST['id'];
    $accountToDelete = new Account();
    $accountToDelete->setId($idToDelete);
    try {
        $accountToDelete->deleteAccount();

        echo json_encode(["success" => 1, "message" => "User with id $idToDelete succesfully deleted."]);
        die;
    }
    catch (Exception $ex) {
        echo json_encode(['success' => 0, 'message' => $ex->getMessage()]);
        die;
    }
}