<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

// Return error if user is not authenticated or is not admin
if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (!isset($_POST['id'])) {
        echo json_encode(['success' => 0, 'message' => 'Something went wrong, this request could not be processed']);
        die;
    }

    // Initialise account to delete.
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
