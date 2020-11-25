<?php

function dieWithError(string $errorMessage, string $page = "")
{
    global $connection;

    $_SESSION['errorMessage'] = $errorMessage;
    header("Location: ../../$page");
    $connection->close();
    die;
}
