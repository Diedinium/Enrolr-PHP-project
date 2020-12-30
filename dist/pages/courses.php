<?php
require __DIR__ . '/../php/classes/_connect.php';
require __DIR__ . '/../php/classes/_course.php';
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

    <link rel="stylesheet" href="../static/main.css">
</head>

<body>
    <script src="../static/courses.js"></script>
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

            <?php if ($account->getIsAdmin()) : ?>
                <div class="card border-0 enrolr-subtle-shadow mt-1">
                    <div class="card-header">
                        <h3><span class="enrolr-gradient">Course Management</span></h3>
                    </div>
                    <div class="card-body p-3">
                        <p class="card-text">As an administrator, you cannot enrol on courses, but you can create, edit and delete them. </p>
                        <p class="card-text"><span class="badge badge-warning">Important!</span> As an administrator, you can remove staff from courses by clicking the edit icon on a course.</p>
                        <button class="btn enrolr-brand-colour-bg text-white" onclick="$('#ModalCreateCourse').modal('show')">Create new course</button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card border-0 enrolr-subtle-shadow mt-3">
                <div class="card-header">
                    <?php if (!$account->getIsAdmin()) : ?>
                        <h3><span class="enrolr-gradient">Courses</span></h3>
                    <?php endif; ?>
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
                            <form id="upcomingSearchForm">
                                <h5>Search upcoming courses</h5>
                                <div class="form-row mb-3 align-items-start" id="upcomingSearchFilter">
                                    <div class="col-lg-3 col-md-12 mb-2 mb-lg-0">
                                        <div class="form-label-group mb-0 w-100">
                                            <input type="date" id="searchMinDate" name="searchMinDate" class="form-control upcomingSearchGroup" placeholder="2020-12-01" min="<?= date("Y-m-d") ?>">
                                            <label for="searchMinDate">Min date</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-12 mb-2 mb-lg-0">
                                        <div class="form-label-group mb-0 w-100">
                                            <input type="date" id="searchMaxDate" name="searchMaxDate" class="form-control upcomingSearchGroup" data-msg-greaterThan="This date cannot be less than or equal to the min date." placeholder="2020-12-01" min="<?= date("Y-m-d") ?>">
                                            <label for="searchMaxDate">Max date</label>
                                        </div>
                                    </div>
                                    <div class="col d-flex align-items-center">
                                        <div class="form-label-group mb-0 w-100">
                                            <input type="text" id="searchTitle" name="searchTitle" class="form-control upcomingSearchGroup" placeholder="Search by title">
                                            <label for="searchTitle">Search by title</label>
                                        </div>
                                        <i data-toggle="tooltip" data-placement="top" title="Clear search" id="clearSearchIcon" class="fas fa-redo fa-lg enrolr-danger-icon pl-2"></i>
                                        <i data-toggle="tooltip" data-placement="top" title="Search" id="upcomingSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon pl-3"></i>
                                    </div>
                                </div>
                            </form>

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
                            <form id="pastSearchForm">
                                <h5>Search past courses</h5>
                                <div class="form-row mb-3 align-items-start" id="pastSearchFilter">
                                    <div class="col-lg-3 col-md-12 mb-2 mb-lg-0">
                                        <div class="form-label-group mb-0 w-100">
                                            <input type="date" id="searchPastMinDate" name="searchPastMinDate" class="form-control pastSearchGroup" placeholder="2020-12-01" max="<?= date("Y-m-d") ?>">
                                            <label for="searchPastMinDate">Min date</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-12 mb-2 mb-lg-0">
                                        <div class="form-label-group mb-0 w-100">
                                            <input type="date" id="searchPastMaxDate" name="searchPastMaxDate" class="form-control pastSearchGroup" data-msg-greaterThan="This date cannot be less than or equal to the min date." placeholder="2020-12-01" max="<?= date("Y-m-d") ?>">
                                            <label for="searchPastMaxDate">Max date</label>
                                        </div>
                                    </div>
                                    <div class="col d-flex align-items-center">
                                        <div class="form-label-group mb-0 w-100">
                                            <input type="text" id="searchPastTitle" name="searchPastTitle" class="form-control pastSearchGroup" placeholder="Search by title">
                                            <label for="searchPastTitle">Search by title</label>
                                        </div>
                                        <i data-toggle="tooltip" data-placement="top" title="Clear search" id="clearPastSearchIcon" class="fas fa-redo fa-lg enrolr-danger-icon pl-2"></i>
                                        <i data-toggle="tooltip" data-placement="top" title="Search" id="pastSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon pl-3"></i>
                                    </div>
                                </div>
                            </form>

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

    <?php if ($account->getIsAdmin()) : ?>
        <div class="modal" tabindex="-1" id="ModalCreateCourse">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Course</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="../php/course/_createCourse.php" method="POST" id="formCreateCourse">
                        <div class="modal-body">
                            <div class="form-label-group">
                                <input type="text" id="createTitle" name="createTitle" class="form-control" placeholder="Title">
                                <label for="createTitle">Title</label>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="createDescription" id="createDescription" rows="3" placeholder="Description"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-label-group col-md-4">
                                    <input type="datetime-local" id="createDate" name="createDate" class="form-control" placeholder="2020-12-01" min="<?= date("Y-m-d\\TH:i") ?>">
                                    <label for="createCourseDate">Course date/time</label>
                                </div>
                                <div class="form-label-group col-md-4">
                                    <input type="number" id="createDuration" name="createDuration" class="form-control" placeholder="54">
                                    <label for="createDuration">Course duration (hours)</label>
                                </div>
                                <div class="form-label-group col-md-4">
                                    <input type="number" id="createMaxAttendees" name="createMaxAttendees" class="form-control" placeholder="32">
                                    <label for="createMaxAttendees">Max Attendees</label>
                                </div>
                            </div>
                            <div class="form-label-group">
                                <input type="text" id="createLink" name="createLink" class="form-control" placeholder="Link" data-msg-required="You must set either a link or location, or both.">
                                <label for="createLink">Link</label>
                            </div>
                            <div class="form-label-group">
                                <input type="text" id="createLocation" name="createLocation" class="form-control" placeholder="Location" data-msg-required="You must set either a link or location, or both.">
                                <label for="createLocation">Location</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn enrolr-brand-colour-bg text-white">Add Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal" tabindex="-1" id="ModalEditCourse">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Course</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="../php/course/_editCourse.php" method="POST" id="formEditCourse">
                            <input type="hidden" value="0" name="editId" id="editId">
                            <div class="form-label-group">
                                <input type="text" id="editTitle" name="editTitle" class="form-control" placeholder="Title">
                                <label for="editTitle">Title</label>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="editDescription" id="editDescription" rows="3" placeholder="Description"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-label-group col-md-4">
                                    <input type="datetime-local" id="editDate" name="editDate" class="form-control" placeholder="2020-12-01" min="<?= date("Y-m-d\\TH:i") ?>">
                                    <label for="editDate">Course date/time</label>
                                </div>
                                <div class="form-label-group col-md-4">
                                    <input type="number" id="editDuration" name="editDuration" class="form-control" placeholder="54">
                                    <label for="editDuration">Course duration (hours)</label>
                                </div>
                                <div class="form-label-group col-md-4">
                                    <input type="number" id="editMaxAttendees" name="editMaxAttendees" class="form-control" placeholder="32">
                                    <label for="editMaxAttendees">Max Attendees</label>
                                </div>
                            </div>
                            <div class="form-label-group">
                                <input type="text" id="editLink" name="editLink" class="form-control" placeholder="Link" data-msg-required="You must set either a link or location, or both.">
                                <label for="editLink">Link</label>
                            </div>
                            <div class="form-label-group">
                                <input type="text" id="editLocation" name="editLocation" class="form-control" placeholder="Location" data-msg-required="You must set either a link or location, or both.">
                                <label for="editLocation">Location</label>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn enrolr-brand-colour-bg text-white">Save</button>
                            </div>
                        </form>
                        <hr>
                        <div>
                            <h6>Staff enrolled</h6>
                            <ul class="list-group">

                            </ul>
                            <div class="enrolled-staff-placeholder">
                                <div class="ph-item mb-0 px-0 py-2">
                                    <div class="ph-col-12">
                                        <div class="ph-row mb-0">
                                            <div class="ph-col-4 big"></div>
                                            <div class="ph-col-8 empty"></div>
                                            <div class="ph-col-8"></div>
                                            <div class="ph-col-4 emtpy"></div>
                                            <div class="ph-col-6"></div>
                                            <div class="ph-col-6 empty"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ph-item mb-0 px-0 py-2">
                                    <div class="ph-col-12">
                                        <div class="ph-row mb-0">
                                            <div class="ph-col-4 big"></div>
                                            <div class="ph-col-8 empty"></div>
                                            <div class="ph-col-8"></div>
                                            <div class="ph-col-4 emtpy"></div>
                                            <div class="ph-col-6"></div>
                                            <div class="ph-col-6 empty"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ph-item mb-0 px-0 py-2">
                                    <div class="ph-col-12">
                                        <div class="ph-row mb-0">
                                            <div class="ph-col-4 big"></div>
                                            <div class="ph-col-8 empty"></div>
                                            <div class="ph-col-8"></div>
                                            <div class="ph-col-4 emtpy"></div>
                                            <div class="ph-col-6"></div>
                                            <div class="ph-col-6 empty"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <nav class="mt-2">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" tabindex="-1" id="ModalViewAttendees">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Course attendees</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="alert alert-warning"><strong>Note: </strong>You cannot remove attendees from past courses.</div>
                            <ul class="list-group">

                            </ul>
                            <div class="enrolled-staff-placeholder">
                                <div class="ph-item mb-0 px-0 py-2">
                                    <div class="ph-col-12">
                                        <div class="ph-row mb-0">
                                            <div class="ph-col-4 big"></div>
                                            <div class="ph-col-8 empty"></div>
                                            <div class="ph-col-8"></div>
                                            <div class="ph-col-4 emtpy"></div>
                                            <div class="ph-col-6"></div>
                                            <div class="ph-col-6 empty"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ph-item mb-0 px-0 py-2">
                                    <div class="ph-col-12">
                                        <div class="ph-row mb-0">
                                            <div class="ph-col-4 big"></div>
                                            <div class="ph-col-8 empty"></div>
                                            <div class="ph-col-8"></div>
                                            <div class="ph-col-4 emtpy"></div>
                                            <div class="ph-col-6"></div>
                                            <div class="ph-col-6 empty"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ph-item mb-0 px-0 py-2">
                                    <div class="ph-col-12">
                                        <div class="ph-row mb-0">
                                            <div class="ph-col-4 big"></div>
                                            <div class="ph-col-8 empty"></div>
                                            <div class="ph-col-8"></div>
                                            <div class="ph-col-4 emtpy"></div>
                                            <div class="ph-col-6"></div>
                                            <div class="ph-col-6 empty"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <nav class="mt-2">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

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