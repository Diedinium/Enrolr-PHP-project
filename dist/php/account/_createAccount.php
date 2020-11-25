<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if ($account->getAuthenticated()) {
    header("Location: pages/todos.php");
    $connection->close();
    die;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['createEmail']) || !isset($_POST['createPassword']) || !isset($_POST['createPasswordConfirm']) || !isset($_POST['createFirstName']) || !isset($_POST['createLastName'])) {
        dieWithError("Something went wrong, details needed to create an account were not passed.", "pages/createAccount.php");
    }
    else {
        $createEmail = $_POST['createEmail'];
        $createPassword = $_POST['createPassword'];
        $createPasswordConfirm = $_POST['createPasswordConfirm'];
        $createFirstName = $_POST['createFirstName'];
        $createLastName = $_POST['createLastName'];

        if ($createPassword !== $createPasswordConfirm) {
            dieWithError("Passwords not not match, account not created. Please try again.", "pages/settings.php?tab=password-tab");
        }

        try {
            $account->addAccount($createEmail, $createPassword, $createFirstName, $createLastName);

            $_SESSION['successMessage'] = "Account created! Welcome to Todr, get started by creating your first todo group below.";
            header("Location: ../../pages/todos.php");
        }
        catch (Exception $ex) {
            dieWithError($ex->getMessage(), "pages/createAccount.php");
        }
    }
}
else {
    dieWithError("You cannot directly load this page", "pages/settings.php");
}
