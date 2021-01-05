<?php
require __DIR__ . '/../php/classes/_connect.php';
require __DIR__ . '/../php/account/_auth.php';

// Redirect non-authenticated users back to the index page.
if (!$account->getAuthenticated()) {
    $_SESSION['errorMessage'] = "You did not provide valid login details.";
    header("Location: ../index.php");
    $connection->close();
    exit;
}

// Get error messages from session if present
$errorMessage;
$successMessage;

if (!empty($_SESSION['errorMessage'])) {
    $errorMessage = $_SESSION['errorMessage'];
    unset($_SESSION['errorMessage']);
}

if (!empty($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolr - Settings</title>

    <link rel="stylesheet" href="../static/main.css">
    <link rel="icon" href="../img/EnrolrLogo.png">
</head>

<body>
    <script src="../static/settings.js"></script>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top enrolr-navbar-top-accent shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center p-0" href="#">
                <img src="../img/EnrolrLogo.png" alt="enrolr logo" width="60" class="d-inline-block">
                <span>Enrolr</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <?php if (!$account->getIsAdmin()) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="enrolments.php"><i class="fas fa-graduation-cap"></i> Enrolments</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php"><i class="fas fa-chalkboard-teacher"></i> Courses</a>
                    </li>
                    <?php if ($account->getIsAdmin()) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php"><i class="fas fa-users-cog"></i> User Management</a>
                        </li>
                    <?php endif ?>
                    <li class="nav-item">
                        <a class="nav-link" href="help.php"><i class="fas fa-question"></i> Help</a>
                    </li>
                </ul>
                <form class="form-inline my-2 my-lg-0" action="../php/account/_logout.php" method="POST" id="logoutForm">
                    <div class="mr-sm-3 mr-3 text-muted"><i class="fas fa-user-circle"></i> <?= $account->getEmail() ?></div>
                    <a href="settings.php"><i class="fas fa-cog fa-lg enrolr-standard-icon mr-3" data-toggle="tooltip" data-placement="bottom" title="Edit user settings"></i></a>
                    <i class="fas fa-sign-out-alt fa-lg enrolr-danger-icon" onclick="submitLogout()" data-toggle="tooltip" data-placement="bottom" title="Logout"></i>
                </form>
            </div>
        </div>
    </nav>

    <main class="enrolr-navbar-spacer">
        <div class="container">
            <?php if (!empty($errorMessage)) : ?>
                <div class="alert alert-danger mt-2">
                    <?= $errorMessage ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)) : ?>
                <div class="alert alert-success mt-2">
                    <?= $successMessage ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 enrolr-subtle-shadow mt-1">
                <div class="card-header">
                    <h3><span class="enrolr-gradient">User Settings</span></h3>
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details">Account Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="security-tab" data-toggle="tab" href="#security">Security</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="management-tab" data-toggle="tab" href="#management">Account Management</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-2">
                    <div class="tab-content" id="settingsTabContent">
                        <div class="tab-pane fade show active p-2" id="details" role="tabpanel">
                            <form>
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label"><strong>Email</strong></label>
                                    <div class="col-md-10">
                                        <span class="form-control text-muted border-0 pl-0"><?= $account->getEmail() ?></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label"><strong>Full Name</strong></label>
                                    <div class="col-md-10">
                                        <span class="form-control text-muted border-0 pl-0"><?= $account->getFullName() ?></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label"><strong>Job role</strong></label>
                                    <div class="col-md-10">
                                        <span class="form-control text-muted border-0 pl-0"><?= $account->getJobRole() ?></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label"><strong>Account created</strong></label>
                                    <div class="col-md-10">
                                        <span class="form-control text-muted border-0 pl-0"><?php echo date('Y/m/d H:i a', strtotime($account->getCreated())) ?></span>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade p-2" id="security" role="tabpanel">
                            <?php if ($account->getIsAdmin()) : ?>
                                <div class="alert alert-warning">
                                    <strong>WARNING: </strong>Be very careful changing your password as an admin, only another admin can reset your password.
                                    Please note down for future use that using <a href="../php/account/_createAdmin.php">this link</a> will re-create the default Admin.McAdmin@enrolr.co.uk account with the default password configured in this file. 
                                    This could prove useful if you lose access to the application. If you have access to the web server, you can configure the password the default admin account has by changing this file.
                                </div>
                            <?php endif; ?>
                            <form action="../php/account/_updatePassword.php" id="formChangePassword" method="POST">
                                <div class="form-group">
                                    <label for="firstName">Current Password</label>
                                    <input type="password" id="currentPassword" name="currentPassword" required class="form-control mw-50">
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" id="newPassword" name="newPassword" data-msg-minlength="Password must be at least 8 characters long." required class="form-control mw-50"></input>
                                </div>
                                <div class="form-group">
                                    <label for="newPasswordConfirm">Confirm new password</label>
                                    <input type="password" id="newPasswordConfirm" name="newPasswordConfirm" data-msg-minlength="Password must be at least 8 characters long." data-msg-equalTo="Passwords do not match." required class="form-control mw-50"></input>
                                </div>
                                <button class="btn enrolr-brand-colour-bg text-white">Change</button>
                            </form>
                        </div>

                        <div class="tab-pane fade p-2" id="management" role="tabpanel">
                            <form action="../php/account/_updateDetails.php" id="formUpdateDetails" method="POST">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" required class="form-control mw-50" value="<?= $account->getFirstName() ?>">
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" required class="form-control mw-50" value="<?= $account->getLastName() ?>"></input>
                                </div>
                                <div class="form-group">
                                    <label for="jobRole">Job Role</label>
                                    <input type="text" id="jobRole" name="jobRole" required class="form-control mw-50" value="<?= $account->getJobRole() ?>"></input>
                                </div>
                                <button class="btn enrolr-brand-colour-bg text-white">Save</button>
                            </form>

                            <hr>

                            <h3>Account Actions</h3>
                            <div class="alert alert-warning">Please note that the settings below are permanent and cannot be undone!</div>

                            <ul class="list-group">
                                <?php if ($account->getIsAdmin()) : ?>
                                    <li class="list-group-item">
                                        <div class="d-sm-flex align-items-center">
                                            <div class="pr-3 mr-auto">
                                                <strong>Please note</strong>
                                                <div>
                                                    Deleting enrolments is not possible for admin users, as you should not be able to enrol on courses in the first place.
                                                    If you wish to enrol on courses, please create and use a normal staff account to do so.
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php else : ?>
                                    <li class="list-group-item">
                                        <div class="d-sm-flex align-items-center">
                                            <div class="pr-3 mr-auto">
                                                <strong>Delete all enrolments</strong>
                                                <div>This will delete all of your current enrolments. This action is permanent and cannot be undone.</div>
                                            </div>
                                            <form action="../php/account/_deleteEnrolments.php" method="POST" class="flex-shrink-0 mt-2 mt-sm-0" id="formDeleteAllEnrollments">
                                                <button type="submit" class="btn btn-danger">Delete all enrolments</button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endif; ?>
                                <?php if ($account->getEmail() !== "Admin.McAdmin@enrolr.co.uk") : ?>
                                    <li class="list-group-item">
                                        <div class="d-sm-flex align-items-center">
                                            <div class="pr-3 mr-auto">
                                                <strong>Delete Account</strong>
                                                <div>This will permanently delete your account along with all associated data. This action is permanent and cannot be undone.</div>
                                            </div>
                                            <form action="../php/account/_deleteAccount.php" onsubmit="showSpinner()" method="POST" class="flex-shrink-0 mt-2 mt-sm-0" id="formDeleteAccount">
                                                <button type="submit" class="btn btn-danger">Delete Account</button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/partials/common.php' ?>

    <footer>
        <hr />
        <div class="container mb-3">
            <span class="text-muted">&copy; Jake Hall</span>
            <span class="float-right text-muted"><?= date("Y") ?></span>
        </div>
    </footer>
</body>

</html>

<?php
$connection->close();
?>