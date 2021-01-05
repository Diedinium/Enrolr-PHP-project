<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/_auth.php';

// Redirect if user is not logged in.
if (!$account->getAuthenticated()) {
    dieWithError("You did not provide valid login details.");
}

try {
    $account->logout();
    $_SESSION['successMessage'] = 'Logout successful';
    header("Location: ../../");
    die;
} catch (Exception $ex) {
    $_SESSION['errorMessage'] = 'Logout failed, are you sure you haven\'t already logged out?';
    header("Location: {$_SERVER['HTTP_REFERER']}");
    $connection->close();
    die;
}
