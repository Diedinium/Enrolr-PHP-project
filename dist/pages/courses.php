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
                        <button class="btn enrolr-brand-colour-bg text-white" onclick="$('#ModalAddCourse').modal('show')">Create new course</button>
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
                            <div class="form-row mb-3">
                                <div class="col-md-4 col-sm-6 col-12">
                                    <select name="sortUpcoming" id="sortUpcoming" class="form-control form-control-sm">
                                        <option selected value="1">Date Asc (Default)</option>
                                        <option value="2">Date Desc</option>
                                        <option value="3">Attendees Asc</option>
                                        <option value="4">Attendees Desc</option>
                                        <?php if (!$account->getIsAdmin()) : ?>
                                            <option value="5">Enrolled Asc</option>
                                            <option value="6">Enrolled Desc</option>
                                        <?php endif; ?>
                                        <option value="7">Created Asc</option>
                                        <option value="8">Created Desc</option>
                                    </select>
                                </div>
                                <div class="col d-flex align-items-center mt-2 mt-sm-0">
                                    <input type="search" class="form-control form-control-sm" placeholder="Search by title">
                                    <i data-toggle="tooltip" data-placement="top" title="Search" id="upcomingSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon pl-2"></i>
                                </div>
                            </div>

                            <?php
                            $upcomingCourses = Course::getUpcomingCourses($account->getId());
                            if (count($upcomingCourses) < 1) :
                            ?>
                                <div class="alert alert-info">No upcoming courses found.</div>
                            <?php else : ?>
                                <div class="row row-cols-1 row-cols-lg-3 row-cols-md-2 mx-n2">
                                    <?php foreach ($upcomingCourses as $upcomingCourse) : ?>
                                        <?php
                                        $courseDate = new DateTimeImmutable($upcomingCourse['date']);
                                        $today = new DateTimeImmutable();
                                        $isFullyBooked = $upcomingCourse['maxAttendees'] <= $upcomingCourse['enrolled'];
                                        $isThisWeek = $courseDate < $today->modify("+7 day");
                                        ?>
                                        <div class="col mb-2 px-2">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title mb-0"><?= $upcomingCourse['title'] ?></h5>
                                                    <div class="pb-2">
                                                        <span class="badge badge-info"><?= $upcomingCourse['duration'] ?> hours</span>
                                                        <?php if ($isThisWeek) : ?>
                                                            <span class="badge badge-success">This week</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="card-text"><?= $upcomingCourse['description'] ?></p>
                                                </div>
                                                <ul class="list-group list-group-flush">
                                                    <?php if (!empty($upcomingCourse['location'])) : ?>
                                                        <li class="list-group-item">
                                                            <span><i data-toggle="tooltip" data-placement="top" title="Location" class="fa fa-map-marked text-muted pr-2"></i><?= $upcomingCourse['location'] ?></span>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li class="list-group-item d-flex">
                                                        <span class="mr-auto"><i data-toggle="tooltip" data-placement="top" title="Course date/time" class="fa fa-calendar-day text-muted pr-2"></i><?= $courseDate->format("d/m/Y g:i a") ?></span>
                                                        <span><i data-toggle="tooltip" data-placement="top" title="Attendees" class="fa fa-user text-muted pr-2"></i><?= $upcomingCourse['enrolled'] . "/" . $upcomingCourse['maxAttendees'] ?></span>
                                                    </li>
                                                </ul>
                                                <div class="card-footer">
                                                    <div class="d-flex align-items-center">
                                                        <small class="text-muted mr-auto"><?= date("d/m/Y h:i a", strtotime($upcomingCourse['created'])) ?></small>
                                                        <?php if ($account->getIsAdmin()) : ?>
                                                            <div class="enrolr-actions-min-width text-right">
                                                                <i data-toggle="tooltip" data-placement="top" title="Edit" class="fas fa-edit fa-lg enrolr-standard-icon event-edit-course"></i>
                                                                <i data-toggle="tooltip" data-placement="top" title="Delete" class="fas fa-trash fa-lg enrolr-danger-icon event-delete-course pl-2"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>


                        </div>
                        <div class="tab-pane fade p-2" id="past" role="tabpanel">
                            <div class="form-row mb-3">
                                <div class="col-md-4 col-sm-6 col-12">
                                    <select name="sortPast" id="sortPast" class="form-control form-control-sm">
                                        <option selected value="1">Date Asc (Default)</option>
                                        <option value="2">Date Desc</option>
                                        <option value="3">Attendees Asc</option>
                                        <option value="4">Attendees Desc</option>
                                        <?php if (!$account->getIsAdmin()) : ?>
                                            <option value="5">Enrolled Asc</option>
                                            <option value="6">Enrolled Desc</option>
                                        <?php endif; ?>
                                        <option value="7">Created Asc</option>
                                        <option value="8">Created Desc</option>
                                    </select>
                                </div>
                                <div class="col d-flex align-items-center mt-2 mt-sm-0">
                                    <input type="search" class="form-control form-control-sm" placeholder="Search by title">
                                    <i data-toggle="tooltip" data-placement="top" title="Search" id="pastSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon pl-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/partials/common.php' ?>

    <?php if ($account->getIsAdmin()) : ?>
        <div class="modal" tabindex="-1" id="ModalAddCourse">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Course</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="../php/course/_addCourse.php" method="POST" id="formAddCourse">
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
                                    <input type="datetime-local" id="createCourseDate" name="createCourseDate" class="form-control" placeholder="2020-12-01" min="<?= date("Y-m-d\\TH:i") ?>">
                                    <label for="createCourseDate">Course date/time</label>
                                </div>
                                <div class="form-label-group col-md-4">
                                    <input type="number" id="createCourseDuration" name="createCourseDuration" class="form-control" placeholder="54">
                                    <label for="createCourseDuration">Course duration (hours)</label>
                                </div>
                                <div class="form-label-group col-md-4">
                                    <input type="number" id="createCourseAttendees" name="createCourseAttendees" class="form-control" placeholder="32">
                                    <label for="createCourseAttendees">Max Attendees</label>
                                </div>
                            </div>
                            <div class="form-label-group">
                                <input type="text" id="createLink" name="createLink" class="form-control" placeholder="Link" data-msg-required="You must set either a link or location">
                                <label for="createLink">Link</label>
                            </div>
                            <div class="form-label-group">
                                <input type="text" id="createLocation" name="createLocation" class="form-control" placeholder="Location" data-msg-required="You must set either a link or location">
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
    <?php endif; ?>

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

            $('#formAddCourse').validate({
                rules: {
                    createTitle: {
                        required: true,
                        maxlength: 255,
                        noWhiteSpace: true
                    },
                    createDescription: {
                        required: true,
                        maxlength: 350,
                        noWhiteSpace: true
                    },
                    createCourseDate: {
                        required: true                      
                    },
                    createCourseDuration: {
                        required: true
                    },
                    createCourseAttendees: {
                        required: true
                    },
                    createLink: {
                        required: '#createLocation:blank',
                        maxlength: 1000,
                        noWhiteSpace: true
                    },
                    createLocation: {
                        required: '#createLink:blank',
                        maxlength: 255,
                        noWhiteSpace: true
                    }
                },
                errorElement: 'small'
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