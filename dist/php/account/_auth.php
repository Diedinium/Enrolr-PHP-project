<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../classes/_account.php';
require __DIR__ . '/../classes/_utilities.php';

// Ensure session and account are setup before attempting authentication
session_start();
$account = new Account();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If posting, assume request is a login attempt and get login details
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $page = $_POST['page'];

        if (empty($email) || empty($password)) {
            dieWithError("Could not login, either a username or password was not provided");
        }

        try {
            // Attempt to login using email and password
            $account->login($email, $password);
            
            // If login is successful, redirect to relevant page for account.
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
        // If posting, but login details are not present, attempt normal login.
        $account->login();
    }
}
else {
    // If not posting, attempt normal login.
    $account->login();
}