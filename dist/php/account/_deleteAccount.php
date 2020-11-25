<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if (!$account->getAuthenticated()) {
    dieWithError("You did not provide valid login details.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $account->logout();
        $account->deleteAccount();

        $_SESSION['successMessage'] = "Your account has succesfully been deleted.";  
        header("Location: ../../");
    } catch (Exception $ex) {
        dieWithError($ex->getMessage(), "pages/settings.php?tab=management-tab");
    }
} else {
    dieWithError("You cannot directly load this page", "pages/settings.php?tab=management-tab");
}