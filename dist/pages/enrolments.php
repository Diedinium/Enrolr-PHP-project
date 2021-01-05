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

// Redirect admins away from page, as admins are not able to enrol on courses.
if ($account->getIsAdmin()) {
    $_SESSION['errorMessage'] = "Admin accounts are not allowed to enrol on courses, so you do not need to access this page.";
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
    <title>Enrolr - Enrolments</title>

    <link rel="stylesheet" href="../static/main.css">
    <link rel="icon" href="../img/EnrolrLogo.png">
</head>

<body>
    <script src="../static/enrolments.js"></script>
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
                    <li class="nav-item active">
                        <a class="nav-link" href=""><i class="fas fa-graduation-cap"></i> Enrolments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php"><i class="fas fa-chalkboard-teacher"></i> Courses</a>
                    </li>
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

            <div class="card border-0 enrolr-subtle-shadow mt-3">
                <div class="card-header">
                    <h3><span class="enrolr-gradient">Your Enrolments</span></h3>
                    <ul class="nav nav-tabs card-header-tabs" id="courseTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="upcoming-tab" data-toggle="tab" href="#upcoming">Upcoming Enrolments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="past-tab" data-toggle="tab" href="#past">Past Enrolments</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-2">
                    <div class="tab-content" id="enrolmentTabContent">
                        <div class="tab-pane fade show active p-2" id="upcoming" role="tabpanel">
                            <div class="row row-cols-1 row-cols-lg-3 row-cols-md-2 mx-n2">
                                <div class="col mb-2 px-2">
                                    <div class="ph-item mb-2">
                                        <div class="ph-col-12">
                                            <div class="ph-row">
                                                <div class="ph-col-8 big"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-8 empty"></div>
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-12"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-12 big"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col mb-2 px-2">
                                    <div class="ph-item mb-2">
                                        <div class="ph-col-12">
                                            <div class="ph-row">
                                                <div class="ph-col-8 big"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-8 empty"></div>
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-12"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-12 big"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col mb-2 px-2">
                                    <div class="ph-item mb-2">
                                        <div class="ph-col-12">
                                            <div class="ph-row">
                                                <div class="ph-col-8 big"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-8 empty"></div>
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-12"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-12 big"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <nav>
                                <div class="d-flex justify-content-end">
                                    <div class="rounded border px-2 py-1 mr-auto"><span class="text-muted">Page: </span><span class="badge badge-primary">1</span></div>
                                    <ul class="pagination mb-0">
                                        <li class="page-item disabled"><button type="button" class="page-link">Previous</button></li>
                                        <li class="page-item disabled"><button type="button" class="page-link">Next</button></li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                        <div class="tab-pane fade p-2" id="past" role="tabpanel">
                            <div class="row row-cols-1 row-cols-lg-3 row-cols-md-2 mx-n2">
                                <div class="col mb-2 px-2">
                                    <div class="ph-item mb-2">
                                        <div class="ph-col-12">
                                            <div class="ph-row">
                                                <div class="ph-col-8 big"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-8 empty"></div>
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-12"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-12 big"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col mb-2 px-2">
                                    <div class="ph-item mb-2">
                                        <div class="ph-col-12">
                                            <div class="ph-row">
                                                <div class="ph-col-8 big"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-8 empty"></div>
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-12"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-12 big"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col mb-2 px-2">
                                    <div class="ph-item mb-2">
                                        <div class="ph-col-12">
                                            <div class="ph-row">
                                                <div class="ph-col-8 big"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-8 empty"></div>
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-12"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-6"></div>
                                                <div class="ph-col-6 empty"></div>
                                                <div class="ph-col-4"></div>
                                                <div class="ph-col-4 empty"></div>
                                                <div class="ph-col-4"></div>
                                            </div>
                                            <div class="ph-row mt-4">
                                                <div class="ph-col-12 big"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <nav>
                                <div class="d-flex justify-content-end">
                                    <div class="rounded border px-2 py-1 mr-auto"><span class="text-muted">Page: </span><span class="badge badge-primary">1</span></div>
                                    <ul class="pagination mb-0">
                                        <li class="page-item disabled"><button type="button" class="page-link">Previous</button></li>
                                        <li class="page-item disabled"><button type="button" class="page-link">Next</button></li>
                                    </ul>
                                </div>
                            </nav>
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