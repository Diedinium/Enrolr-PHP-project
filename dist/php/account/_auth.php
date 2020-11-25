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

        if (empty($email) || empty($password)) {
            dieWithError("Could not login, either a username or password was not provided");
        }

        try {
            $account->login($email, $password);

            if ($account->getAuthenticated()) {
                $_SESSION['successMessage'] = "Logged in successfully";
                header("Location: ../../pages/todos.php");
                die;
            }
            else {
                dieWithError("Login failed.");
            }
        }
        catch (Exception $ex) {
            dieWithError($ex->getMessage());
        }   
    }
    else {
        $account->login();
    }
}
else {
    $account->login();
}