<?php
require __DIR__ . '/../php/classes/_connect.php';
require __DIR__ . '/../php/classes/_course.php';
require __DIR__ . '/../php/account/_auth.php';

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
    <title>Enrolr - Help</title>

    <link rel="stylesheet" href="../static/main.css">
    <link rel="icon" href="../img/EnrolrLogo.png">
</head>

<body>
    <script src="../static/help.js"></script>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top enrolr-navbar-top-accent shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center p-0" href="#">
                <img src="../img/EnrolrLogo.png" alt="Enrolr logo" width="60" class="d-inline-block">
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
                    <?php if ($account->getAuthenticated()) : ?>
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
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href=""><i class="fas fa-question"></i> Help</a>
                    </li>
                </ul>
                <?php if (!$account->getAuthenticated()) : ?>
                    <form class="form-inline my-2 my-lg-0" action="../php/account/_auth.php" method="POST" id="logonForm">
                        <input type="hidden" name="page" value="pages/help.php">
                        <input class="form-control mr-sm-2 mb-sm-0 mb-2" type="email" name="email" required placeholder="Email" aria-label="Email">
                        <input class="form-control mr-sm-2" type="password" required name="password" placeholder="Password" aria-label="Password">
                        <button class="btn btn-primary my-2 my-sm-0" type="submit">Login</button>
                    </form>
                <?php else : ?>
                    <form class="form-inline my-2 my-lg-0" action="../php/account/_logout.php" method="POST" id="logoutForm">
                        <div class="mr-sm-3 mr-3 text-muted"><i class="fas fa-user-circle"></i> <?= $account->getEmail() ?></div>
                        <a href="settings.php"><i class="fas fa-cog fa-lg enrolr-standard-icon mr-3" data-toggle="tooltip" data-placement="bottom" title="Edit user settings"></i></a>
                        <i class="fas fa-sign-out-alt fa-lg enrolr-danger-icon" onclick="submitLogout()" data-toggle="tooltip" data-placement="bottom" title="Logout"></i>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="enrolr-navbar-spacer">
        <div class="container">
            <?php if (!empty($successMessage)) : ?>
                <div class="alert alert-success mt-3">
                    <?= $successMessage ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)) : ?>
                <div class="alert alert-danger mt-3">
                    <?= $errorMessage ?>
                </div>
            <?php endif; ?>

            <div class="jumbotron mt-3 mb-3 enrolr-subtle-shadow text-center">
                <h1 class="display-4">Need help?</h1>
                <p>You're in the right place.</p>
            </div>

            <?php if (!$account->getAuthenticated()) : ?>
                <div class="card border-0 enrolr-subtle-shadow mb-3">
                    <div class="card-header">
                        <h3><span class="enrolr-gradient">Getting Started</span></h3>
                    </div>
                    <div class="card-body p-3">
                        <p class="font-weight-bolder">Welcome to Enrolr!</p>
                        <p>
                            This web app allows you to Enrol on courses that your organisation is hosting internally, or via 3rd party providers so long as your organisation adds these details
                            to the application.
                        </p>
                        <p>Please follow the steps to get started:</p>
                        <ol>
                            <li>Request an account from your organisation/administrators.</li>
                            <li>
                                <div>
                                    Once you have your account details (should be your email + password created by admin), log into your account using the login inputs in the top navigation bar.
                                </div>
                                <div class="alert alert-info mt-2">
                                    Note that on mobile you must expand the menu to see these login fields!
                                </div>
                            </li>
                            <li>
                                <div>
                                    Once you log in, before doing anything else, click the settings cog located at the top of the page (or in the dropdown menu on mobile):
                                </div>
                                <img src="../img/EnrolrSettingsIconExample.png" alt="Example of settings icon location" class="img-fluid rounded shadow-sm mt-3 mb-3">
                            </li>
                            <li>
                                <div>
                                    On the settings page, navigate to the security tab:
                                </div>
                                <img src="../img/EnrolrSecurityTabExample.png" alt="Example of settings icon location" class="img-fluid rounded shadow-sm mt-3 mb-3">
                            </li>
                            <li>
                                <div>
                                    On the security tab, enter your current password and a new password of your own choosing. It is important your new password is something memorable,
                                    and that it meets your organisations security polciies.
                                </div>
                                <img src="../img/EnrolrChangePasswordExample.png" alt="Example of settings icon location" class="img-fluid rounded shadow-sm mt-3 mb-3">
                            </li>
                            <li><span class="badge badge-success">Well done!</span> Once your password is changed, you are ready to start using the web app to enrol in courses.</li>
                            <div class="alert alert-info mt-2"><strong>Note: </strong>Return to this page once you are logged in to see some more help specific to users.</div>
                        </ol>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card border-0 enrolr-subtle-shadow mb-3">
                <div class="card-header">
                    <h3><span class="enrolr-gradient">Password issues</span></h3>
                </div>
                <div class="card-body p-3">
                    <p>If you are having password issues, such as forgetting your password, please contact an administrator to reset your password.</p>
                    <div class="alert alert-info"><strong>Useful tip: </strong> If you know your password, and just want to update it, you can do this in your user settings page.</div>
                </div>
            </div>

            <?php if (!$account->getIsAdmin() && $account->getAuthenticated()) : ?>
                <div class="card border-0 enrolr-subtle-shadow mb-3">
                    <div class="card-header">
                        <h3><span class="enrolr-gradient">General Help</span></h3>
                    </div>
                    <div class="card-body p-3">
                        <p>
                            There are a few different pages that you can use as a staff-level user, this help page provides some basic details for each.
                        </p>

                        <h4>Enrolments page</h4>
                        <p>This page shows you your upcoming enrolments, at a maximum of 12 per page. You can unenrol using the "Unenrol" button on each course.</p>
                        <p>You can also view the courses you've prviously attended, by viewing the "Past enrolments" tab. You cannot enrol from courses in the past.</p>
                        <div class="alert alert-info">
                            <strong>Useful tip: </strong>
                            You cannot enrol onto courses on your enrolments page, as it simply displays your enrolments.
                            See the "Courses" page below for information on this page.
                        </div>

                        <h4>Courses page</h4>
                        <p>This page shows all upcoming courses - and keeps a record of past courses - displaying up to 12 per page.</p>
                        <p>You can enrol and unenrol from courses on this page, use the Enrolments page for a convenient view of your existing enrolments.</p>
                        <p>Courses on this page do not display the link (if they are a link-only style course) until you enrol in them</p>
                        <p>
                            <span class="badge badge-warning">Please note</span>
                            If a course is at max capacity, you will not be able to enrol on it. If you get an error while
                            enrolling on a course, reload the page as it's likely the course was filled up sometime after you loaded the page.
                        </p>
                        <div class="alert alert-info">
                            <strong>Useful tip: </strong>
                            Use the min/max date and title search boxes to find courses by part or all of their title, or date!
                        </div>

                        <h4>Settings page</h4>
                        <p>It can be easy to miss, but at the top of the page is a setting icon.</p>
                        <p>Clicking this takes you to your user settings page, where you can:</p>
                        <ul>
                            <li>View your details</li>
                            <li>Update your details</li>
                            <li>Change your password</li>
                            <li>Remove all of your enrolments</li>
                            <li>Delete your account</li>
                        </ul>
                        <p>Please ensure you are sure you want to delete all of your enrolments or account, as it cannot be recovered.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($account->getIsAdmin()) : ?>
                <div class="card border-0 enrolr-subtle-shadow">
                    <div class="card-header">
                        <h3><span class="enrolr-gradient">Admin Help</span></h3>
                    </div>
                    <div class="card-body p-3">
                        <p>
                            There are a few different pages that you can use as a admin-level user, this help page provides some basic details for each.
                        </p>

                        <h4>User management page</h4>
                        <p>This page shows you all the users in the system.</p>
                        <p>You can delete or edit a user using the appropriate icons, or you can create a new user using the button near the top of the page.</p>
                        <p>You can also update user passwords, within the edit page for a user.</p>
                        <div class="alert alert-info">
                            <strong>Useful tip: </strong>
                            If you want to enrol on courses as an administrator, you will need to create your own non-admin account to do so.
                        </div>

                        <h4>Courses page</h4>
                        <p>This page shows all upcoming courses - and keeps a record of past courses - displaying up to 12 per page.</p>
                        <p>As an admin, you can edit, create or delete courses on this page. In the edit screen for a course, you can also remove enrolled users from a course.</p>
                        <p>Links also are displayed by default if you are an admin, allowing you to review them.</p>
                        <div class="alert alert-info">
                            <strong>Useful tip: </strong>
                            Use the min/max date and title search boxes to find courses by part or all of their title, or date!
                        </div>

                        <h4>Settings page</h4>
                        <p>It can be easy to miss, but at the top of the page is a setting icon.</p>
                        <p>Clicking this takes you to your user settings page, where you can:</p>
                        <ul>
                            <li>View your details</li>
                            <li>Update your details</li>
                            <li>Change your password</li>
                            <li>Remove all of your enrolments</li>
                            <li>Delete your account</li>
                        </ul>
                        <p>Please ensure you are sure you want to delete all of your enrolments or account, as it cannot be recovered.</p>
                        <div class="alert alert-info">
                            <strong>Note: </strong>
                            By design, the default admin account cannot be deleted and admin accounts cannot enrol on courses. 
                        </div>
                        <div class="alert alert-danger">
                            <strong>Warning: </strong>
                            Be careful changing your password as an admin user, as only another admin can then change your password. 
                            If all else fails, note down <a href="../php/account/_createAdmin.php">this link</a> which when navigated to will recreate the default admin account. 
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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