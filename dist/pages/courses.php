<?php
require __DIR__ . '/../php/classes/_connect.php';
require __DIR__ . '/../php/account/_auth.php';

if (!$account->getAuthenticated()) {
    $_SESSION['errorMessage'] = "You did not provide valid login details.";
    header("Location: ../index.php");
    $connection->close();
    exit;
}

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
    <title>Enrolr - Enrollments</title>
    <script src="../main.js"></script>
    <link rel="stylesheet" href="../main.css">
</head>

<body>
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
                    <?php if (!$account->getIsAdmin()) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="enrolments.php"><i class="fas fa-graduation-cap"></i> Enrolments</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item active">
                        <a class="nav-link" href=""><i class="fas fa-chalkboard-teacher"></i> Courses</a>
                    </li>
                    <?php if ($account->getIsAdmin()) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php"><i class="fas fa-users-cog"></i> User Management</a>
                        </li>
                    <?php endif ?>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php"><i class="fas fa-question"></i> About</a>
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

            <?php if ($account->getIsAdmin()) : ?>
                <div class="card border-0 enrolr-subtle-shadow mt-1">
                    <div class="card-header">
                        <h3><span class="enrolr-gradient">Course Management</span></h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text">As an administrator, you cannot enrol on courses, but you can create, edit and delete them. </p>
                        <button class="btn enrolr-brand-colour-bg text-white" onclick="$('#ModalAddUser').modal('show')">Create new course</button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card border-0 enrolr-subtle-shadow mt-3">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="courseTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="courses-tab" data-toggle="tab" href="#courses">Upcoming Courses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="past-tab" data-toggle="tab" href="#past">Past Courses</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-2">
                    <div class="tab-content" id="courseTabContent">
                        <div class="tab-pane fade show active p-2" id="courses" role="tabpanel">
                            <p>Placeholder</p>
                        </div>
                        <div class="tab-pane fade p-2" id="past" role="tabpanel">
                        <p>Placeholder</p>
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

    <script>
        function submitLogout() {
            $('#logoutForm').submit();
        }

        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            $('input, select').focusout(function() {
                $(this).removeClass('error');
            });

            $(document).on('hidden.bs.toast', function($event) {
                $event.target.remove();
            });
        });
    </script>
</body>

</html>

<?php
$connection->close();
?>