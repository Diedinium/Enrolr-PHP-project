<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

// Redirect if user is not logged in.
if (!$account->getAuthenticated()) {
    dieWithError("You did not provide valid login details.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $account->deleteAllEnrollments();

        $_SESSION['successMessage'] = "Enrolments successfully deleted.";  
        header("Location: ../../pages/settings.php?tab=management-tab");
    } catch (Exception $ex) {
        dieWithError($ex->getMessage(), "pages/settings.php?tab=management-tab");
    }
} else {
    dieWithError("You cannot directly load this page", "pages/settings.php?tab=management-tab");
}