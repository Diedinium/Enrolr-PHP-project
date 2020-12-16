<?php
require __DIR__ . '/../php/classes/_connect.php';
require __DIR__ . '/../php/account/_auth.php';

if (!$account->getAuthenticated()) {
    $_SESSION['errorMessage'] = "You did not provide valid login details.";
    header("Location: ../index.php");
    $connection->close();
    exit;
}

if (!$account->getIsAdmin()) {
    $_SESSION['errorMessage'] = "You do not have access to this page.";
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
    <title>Enrolr - Users</title>
    <script src="../main.js"></script>
    <link rel="stylesheet" href="../main.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top enrolr-navbar-top-accent shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center p-0" href="#">
                <img src="../img/enrolrLogo.png" alt="enrolr logo" width="60" class="d-inline-block">
                <span>Enrolr</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="../index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <?php if ($account->getIsAdmin()) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href=""><i class="fas fa-users-cog"></i> User Management</a>
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

            <div class="card border-0 enrolr-subtle-shadow">
                <div class="card-header">
                    <h3><span class="enrolr-gradient">User Management</span></h3>
                </div>
                <div class="card-body">
                    <p class="card-text">Create a new user, as either an administrator or regular staff member.</p>
                    <button class="btn enrolr-brand-colour-bg text-white" onclick="$('#ModalAddUser').modal('show')">Create new user</button>
                </div>
            </div>

            <div class="card border-0 enrolr-subtle-shadow mt-3">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="staffTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="staff-tab" data-toggle="tab" href="#staff">Staff</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="admin-tab" data-toggle="tab" href="#admin">Admins</a>
                        </li>
                    </ul>
                </div>
                <?php $result = Account::getAllStaff(); ?>
                <div class="card-body p-2">
                    <div class="tab-content" id="staffTabContent">
                        <div class="tab-pane fade show active p-2" id="staff" role="tabpanel">
                            <?php
                            $staffResult = Account::filterStaff($result);
                            if (count($staffResult) < 1) :
                            ?>
                                <div class="alert alert-info">No Staff found, add one using the button above!</div>
                            <?php else : ?>
                                <div class="row justify-content-end">
                                    <div class="col-12 d-sm-none">
                                        <div class="alert alert-warning">
                                            <strong>Note:</strong> Since you are on a small screen, scroll the table horizontally to see actions.
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="d-flex align-items-center">
                                            <input type="search" id="staffSearchBox" class="form-control form-control-sm" placeholder="Type to search">
                                            <div class="pl-2">
                                                <i id="staffSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table id="staffTable" class="table w-100">
                                    <thead>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Job Title</th>
                                        <th></th>
                                    </thead>
                                    <?php foreach ($staffResult as $userAccount) : ?>
                                        <tr>
                                            <td><?= $userAccount['firstName'] ?></td>
                                            <td><?= $userAccount['lastName'] ?></td>
                                            <td><?= $userAccount['email'] ?></td>
                                            <td><?= $userAccount['jobTitle'] ?></td>
                                            <td class="text-right">
                                                <i onclick="alert('Not yet implemented')" data-toggle="tooltip" data-placement="top" title="Edit User" class="fas fa-user-edit enrolr-standard-icon mr-2"></i>
                                                <i onclick="alert('Not yet implemented')" data-toggle="tooltip" data-placement="top" title="Delete User" class="fas fa-user-times enrolr-danger-icon mr-2"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </table>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade p-2" id="admin" role="tabpanel">
                            <?php
                            $adminResult = Account::fitlerAdministrators($result);
                            if (count($result) < 1) :
                            ?>
                                <div class="alert alert-info">No Administrators found, add one using the button above!</div>
                            <?php else : ?>
                                <div class="row justify-content-end">
                                    <div class="col-12 d-sm-none">
                                        <div class="alert alert-warning">
                                            <strong>Note:</strong> Since you are on a small screen, scroll the table horizontally to see actions.
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="d-flex align-items-center">
                                            <input type="search" id="adminSearchBox" class="form-control form-control-sm" placeholder="Type to search">
                                            <div class="pl-2">
                                                <i id="adminSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table id="adminTable" class="table w-100">
                                    <thead>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Job Title</th>
                                        <th></th>
                                    </thead>
                                    <?php foreach ($adminResult as $userAccount) : ?>
                                        <tr>
                                            <td><?= $userAccount['firstName'] ?></td>
                                            <td><?= $userAccount['lastName'] ?></td>
                                            <td><?= $userAccount['email'] ?></td>
                                            <td><?= $userAccount['jobTitle'] ?></td>
                                            <td class="text-right" style="min-width: 100px;">
                                                <i onclick="alert('Not yet implemented')" class="fas fa-user-edit enrolr-standard-icon mr-2"></i>
                                                <i onclick="alert('Not yet implemented')" class="fas fa-user-times enrolr-danger-icon mr-2"></i>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal" tabindex="-1" id="ModalAddUser">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="../php/users/_createUser.php" method="POST" id="formAddUser">
                    <div class="modal-body">
                        <div class="form-label-group">
                            <input type="email" id="createEmail" name="createEmail" class="form-control" placeholder="Email address" autocomplete="new-password">
                            <label for="createEmail">Email address</label>
                        </div>
                        <div class="form-label-group">
                            <input type="password" id="createPassword" name="createPassword" class="form-control" placeholder="Email address" autocomplete="new-password">
                            <label for="createPassword">Password</label>
                        </div>
                        <div class="form-label-group">
                            <input type="password" id="createPasswordConfirm" name="createPasswordConfirm" class="form-control" data-msg-equalTo="Passwords do not match." placeholder="Email address" autocomplete="new-password">
                            <label for="createPasswordConfirm">Retype Password</label>
                        </div>
                        <div class="d-sm-flex">
                            <div class="form-label-group flex-fill mr-1">
                                <input type="text" id="createFirstName" name="createFirstName" class="form-control" placeholder="First Name">
                                <label for="createFirstName">First Name</label>
                            </div>
                            <div class="form-label-group flex-fill ml-1">
                                <input type="text" id="createLastName" name="createLastName" class="form-control" placeholder="Last Name">
                                <label for="createLastName">Last Name</label>
                            </div>
                        </div>
                        <div class="form-label-group">
                            <input type="text" id="createJobRole" name="createJobRole" class="form-control" placeholder="Last Name">
                            <label for="createLastName">Job Role</label>
                        </div>
                        <hr>
                        <h5>User Options</h5>
                        <div class="form-group form-check">
                            <input type="checkbox" id="createIsAdmin" name="createIsAdmin" class="form-check-input">
                            <label class="form-check-label" for="createIsAdmin">Make admin</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn enrolr-brand-colour-bg text-white">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

            $('a[data-toggle="tab"').on('shown.bs.tab', function(e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });

            $('input, select').focusout(function() {
                $(this).removeClass('error');
            });

            let adminTable = $('#adminTable').DataTable({
                ordering: false,
                scrollX: true,
                stateSave: true,
                lengthChange: false,
                dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            let staffTable = $('#staffTable').DataTable({
                ordering: false,
                scrollX: true,
                stateSave: true,
                lengthChange: false,
                dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            $('#adminSearchBox, #adminSearchIcon').on('keyup click', function() {
                adminTable.search($('#adminSearchBox').val()).draw();
            });

            $('#staffSearchBox, #staffSearchIcon').on('keyup click', function() {
                adminTable.search($('#staffSearchBox').val()).draw();
            });

            $('#formAddUser').validate({
                rules: {
                    createEmail: {
                        required: true,
                        maxlength: 200,
                        noWhiteSpace: true
                    },
                    createPassword: {
                        required: true,
                        maxlength: 150,
                        minlength: 8,
                        noWhiteSpace: true
                    },
                    createPasswordConfirm: {
                        required: true,
                        maxlength: 150,
                        minlength: 8,
                        noWhiteSpace: true,
                        equalTo: '#createPassword'
                    },
                    createFirstName: {
                        required: true,
                        maxlength: 50,
                        noWhiteSpace: true
                    },
                    createJobRole: {
                        required: true,
                        maxlength: 100,
                        noWhiteSpace: true
                    },
                    createLastName: {
                        required: true,
                        maxlength: 50,
                        noWhiteSpace: true
                    }
                },
                errorElement: 'small'
            });
        });
    </script>
</body>

</html>

<?php
$connection->close();
?>