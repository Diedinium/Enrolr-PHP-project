<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if (!$account->getAuthenticated()) {
    dieWithError("You did not provide valid login details.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['firstName']) || !isset($_POST['lastName'])) {
        dieWithError("Something went wrong, first name or last name were not passed", "pages/settings.php?tab=management-tab");
    }
    else {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];

        try {
            $account->updateNames($firstName, $lastName);

            $_SESSION['successMessage'] = "Names updated";
            header("Location: ../../pages/settings.php?tab=management-tab");
        }
        catch (Exception $ex) {
            dieWithError($ex->getMessage(), "pages/settings.php?tab=management-tab");
        }
    }
}
else {
    dieWithError("You cannot directly load this page", "pages/settings.php");
}
