<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if (!$account->getAuthenticated()) {
    dieWithError("You did not provide valid login details.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['currentPassword']) || !isset($_POST['newPassword']) || !isset($_POST['newPasswordConfirm'])) {
        dieWithError("Something went wrong, not all details needed to update password were passed", "pages/settings.php?tab=security-tab");
    }
    else {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $newPasswordConfirm = $_POST['newPasswordConfirm'];

        if (!password_verify($currentPassword, $account->getPassword())) {
            dieWithError("Your current password is not correct, could not update password. Please try again.", "pages/settings.php?tab=security-tab");
        }

        if ($newPassword !== $newPasswordConfirm) {
            dieWithError("New passwords do not match, could not update. Please try again.", "pages/settings.php?tab=security-tab");
        }

        try {
            $account->changePassword($newPassword);

            $_SESSION['successMessage'] = "Password updated, please log back in using your new password";
            $account->logout();
            header("Location: ../../");
        }
        catch (Exception $ex) {
            dieWithError($ex->getMessage(), "pages/settings.php?tab=security-tab");
        }
    }
}
else {
    dieWithError("You cannot directly load this page", "pages/settings.php");
}