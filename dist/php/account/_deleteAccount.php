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

        $_SESSION['successMessage'] = "Account has succesfully been deleted.";  
        header("Location: ../../");
    } catch (Exception $ex) {
        dieWithError($ex->getMessage(), "pages/enrollments.php");
    }
} else {
    dieWithError("You cannot directly load this page", "pages/enrollments.php");
}