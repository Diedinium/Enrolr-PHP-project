<?php

// Helper function to save needing to type out same redirect code multiple times...
function dieWithError(string $errorMessage, string $page = "")
{
    global $connection;

    $_SESSION['errorMessage'] = $errorMessage;
    header("Location: ../../$page");
    $connection->close();
    die;
}
