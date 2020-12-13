<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if ($account->getAuthenticated()) {
    header("Location: pages/todos.php");
    $connection->close();
    die;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['createEmail']) || !isset($_POST['createPassword']) || !isset($_POST['createPasswordConfirm']) || 
    !isset($_POST['createFirstName']) || !isset($_POST['createLastName']) || !isset($_POST['createJobRole']) || !isset($_POST['createIsAdmin'])) {
        dieWithError("Something went wrong, details needed to create an account were not passed.", "pages/users.php");
    }
    else {
        $createEmail = $_POST['createEmail'];
        $createPassword = $_POST['createPassword'];
        $createPasswordConfirm = $_POST['createPasswordConfirm'];
        $createFirstName = $_POST['createFirstName'];
        $createLastName = $_POST['createLastName'];
        $createJobRole = $_POST['createJobRole'];
        $createIsAdmin = $_POST['isAdmin'];

        if ($createPassword !== $createPasswordConfirm) {
            dieWithError("Passwords not not match, account not created. Please try again.", "pages/users.php");
        }

        try {
            $account->addAccount($createEmail, $createPassword, $createFirstName, $createLastName, $createJobRole, $createIsAdmin);

            $_SESSION['successMessage'] = "Account created with email: $createEmail";
            header("Location: ../../pages/users.php");
        }
        catch (Exception $ex) {
            dieWithError($ex->getMessage(), "pages/users.php");
        }
    }
}
else {
    dieWithError("You cannot directly load this page");
}
