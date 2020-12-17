<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if (!$account->getAuthenticated() || !$account->getIsAdmin()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['updatePasswordUserId'])) {
        echo json_encode(["success" => 0, "message" => "Something went wrong, this request could not be processed"]);
    } else {
        $userId = $_POST['updatePasswordUserId'];
        $newPassword = $_POST['updatePassword'];
        $newPasswordConfirm = $_POST['updatePasswordConfirm'];

        if ($newPassword !== $newPasswordConfirm) {
            echo json_encode(["success" => 0, "message" => "Passwords do not match, please try again."]);
        }

        $accountToUpdate = new Account();
        $accountToUpdate->setId($userId);
        try {
            $accountToUpdate->changePassword($newPassword);
            echo json_encode(["success" => 1, "message" => "Password for user with id $userId successfully updated."]);
        } catch (Exception $ex) {
            echo json_encode(["success" => 0, "message" => "Updating password failed."]);
        }
    }
}
