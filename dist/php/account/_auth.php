<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../classes/_account.php';
require __DIR__ . '/../classes/_utilities.php';

session_start();

$account = new Account();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $page = $_POST['page'];

        if (empty($email) || empty($password)) {
            dieWithError("Could not login, either a username or password was not provided");
        }

        try {
            $account->login($email, $password);

            if ($account->getAuthenticated()) {
                if ($account->getIsAdmin()) {
                    $_SESSION['successMessage'] = "Logged in successfully";
                    header("Location: ../../pages/courses.php");
                    die;
                }
                else {
                    $_SESSION['successMessage'] = "Logged in successfully";
                    header("Location: ../../pages/enrolments.php");
                    die;
                }
            }
            else {
                dieWithError("Login failed.");
            }
        }
        catch (Exception $ex) {
            dieWithError($ex->getMessage(), $page);
        }   
    }
    else {
        $account->login();
    }
}
else {
    $account->login();
}