<?php

$dbPassword = "JuPbdDs1aDSdhnfU";
$dbUserName = "wad-assignment";
$dbServer = "localhost";
$dbName = "wad-assignment";

$connection = new mysqli($dbServer, $dbUserName, $dbPassword, $dbName);

if ($connection->connect_errno) {
    exit("Database connection failed. Reason:".$connection->connect_error);
}