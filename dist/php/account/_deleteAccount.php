<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

if (!$account->getAuthenticated()) {
    dieWithError("You did not provide valid login details.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (isset($_POST['idToDelete']) && $account->getIsAdmin()) {
            $idToDelete = $_POST['idToDelete'];
            $accountToDelete = new Account();
            $accountToDelete->setId($idToDelete);
            $accountToDelete->deleteAccount();

            $_SESSION['successMessage'] = "Account with id of $idToDelete has succesfully been deleted.";  
            header("Location: ../../pages/users.php");
        }
        else {
            $account->logout();
            $account->deleteAccount();
    
            $_SESSION['successMessage'] = "Account has succesfully been deleted.";  
            header("Location: ../../");
        }
    } catch (Exception $ex) {
        dieWithError($ex->getMessage(), "pages/enrolments.php");
    }
} 
else {
    dieWithError("You cannot directly load this page");
}