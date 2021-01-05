<?php
// Ensure timezone is set correctly when rendering pages.
date_default_timezone_set("Europe/London");

$dbPassword = "JuPbdDs1aDSdhnfU";
$dbUserName = "wad-assignment";
$dbServer = "localhost";
$dbName = "wad-assignment";

$connection = new mysqli($dbServer, $dbUserName, $dbPassword, $dbName);

if ($connection->connect_errno) {
    exit("Database connection failed. Reason:".$connection->connect_error);
}