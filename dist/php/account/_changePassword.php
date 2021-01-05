<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

// Return error for non-authenticated accounts.
if (!$account->getAuthenticated()) {
    echo json_encode(["success" => 0, "message" => "You are not authorised to perform this action"]);
    die;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $newPasswordConfirm = $_POST['newPasswordConfirm'];

    // If new password and confirmed password do not match, return error.
    if ($newPassword !== $newPasswordConfirm) {
        echo json_encode(["success" => 0, "message" => "New passwords do not match, please try again."]);
        die;
    }

    // If old password was not correct, return error.
    if (!password_verify($currentPassword, $account->getPassword())) {
        echo json_encode(["success" => 0, "message" => "Your old password was not correct, please try again."]);
        die;
    }

    try {
        // Update password and return success.
        $account->changePassword($newPassword);
        echo json_encode(["success" => 1, "message" => "Password successfully updated. Please log out of your current session and attempt to log in with your new password."]);
    } catch (Exception $ex) {
        echo json_encode(["success" => 0, "message" => $ex->getMessage()]);
    }
}
