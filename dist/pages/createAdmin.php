<?php
require __DIR__ . '/../php/classes/_connect.php';
require __DIR__ . '/../php/classes/_account.php';

$account = new Account();

session_start();

$password = password_hash("SomeCrappyAdminPassword", PASSWORD_DEFAULT);
$account->addAccount("Admin.McAdmin@enrollr.co.uk", "SomeCrappyAdminPassword", "Admin", "McAdmin", "Adminstrator", true);


