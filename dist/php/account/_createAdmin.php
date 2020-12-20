<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../classes/_account.php';

// This is intended to try and delete then re-create the default admin account. Change password when deployed.
Account::ensureAdminAccountDeleted();

$account = new Account();
$account->addAccount("Admin.McAdmin@enrolr.co.uk", "SomeLongAdminPassword", "Admin", "McAdmin", "Adminstrator", true);


