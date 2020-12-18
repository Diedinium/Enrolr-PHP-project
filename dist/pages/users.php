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

    <link rel="stylesheet" href="../main.css">
</head>

<body>
    <script src="../main.js"></script>
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
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <?php if ($account->getIsAdmin()) : ?>
                        <li class="nav-item active">
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
                                                <i data-toggle="tooltip" data-placement="top" title="Search" id="staffSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon"></i>
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
                                            <td class="text-right enrolr-datatable-actions-min-width">
                                                <i data-userId="<?= $userAccount['id'] ?>" class="fas fa-user-edit enrolr-standard-icon mr-2 event-user-edit"></i>
                                                <i data-userId="<?= $userAccount['id'] ?>" class="fas fa-user-times enrolr-danger-icon mr-2 event-user-delete-staff"></i>
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
                                            <td class="text-right enrolr-datatable-actions-min-width">
                                                <?php if ($userAccount['email'] !== "Admin.McAdmin@enrolr.co.uk") : ?>
                                                    <i data-userId="<?= $userAccount['id'] ?>" class="fas fa-user-edit enrolr-standard-icon mr-2 event-user-edit"></i>
                                                    <?php if ($account->getId() != $userAccount['id']) : ?>
                                                        <i data-userId="<?= $userAccount['id'] ?>" class="fas fa-user-times enrolr-danger-icon mr-2 event-user-delete-admin"></i>
                                                    <?php endif; ?>
                                                <?php endif; ?>
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
                <form action="../php/account/_createUser.php" method="POST" id="formAddUser">
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

    <div class="modal" tabindex="-1" id="ModalEditUser">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="../php/account/_editUser.php" method="POST" id="formEditUser">
                        <input type="hidden" id="updateUserId" name="updateUserId">
                        <div class="form-label-group">
                            <input type="email" id="updateEmail" name="updateEmail" class="form-control" placeholder="Email address" autocomplete="new-password">
                            <label for="updateEmail">Email address</label>
                        </div>
                        <div class="d-sm-flex">
                            <div class="form-label-group flex-fill mr-1">
                                <input type="text" id="updateFirstName" name="updateFirstName" class="form-control" placeholder="First Name">
                                <label for="updateFirstName">First Name</label>
                            </div>
                            <div class="form-label-group flex-fill ml-1">
                                <input type="text" id="updateLastName" name="updateLastName" class="form-control" placeholder="Last Name">
                                <label for="updateLastName">Last Name</label>
                            </div>
                        </div>
                        <div class="form-label-group">
                            <input type="text" id="updateJobRole" name="updateJobRole" class="form-control" placeholder="Last Name">
                            <label for="updateJobRole">Job Role</label>
                        </div>
                        <button type="submit" class="btn enrolr-brand-colour-bg text-white">Save</button>
                    </form>
                    <hr>
                    <h5>Update user password</h5>
                    <p>If a user has forgotten their password, use the form below to update their password.</p>
                    <form action="../php/account/_editUserPassword.php" method="POST" id="formUpdateUserPassword">
                        <input type="hidden" id="updatePasswordUserId" name="updatePasswordUserId">
                        <div class="form-label-group">
                            <input type="password" id="updatePassword" name="updatePassword" class="form-control" placeholder="Email address" autocomplete="new-password">
                            <label for="updatePassword">New Password</label>
                        </div>
                        <div class="form-label-group">
                            <input type="password" id="updatePasswordConfirm" name="updatePasswordConfirm" class="form-control" data-msg-equalTo="Passwords do not match." placeholder="Email address" autocomplete="new-password">
                            <label for="updatePasswordConfirm">Retype Password</label>
                        </div>
                        <button type="submit" class="btn enrolr-brand-colour-bg text-white">Update</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
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
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            let $rowReference;

            if (urlParams.has('tab')) {
                const tabName = urlParams.get('tab');

                $(`#${tabName}`).tab('show');
            }

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
                staffTable.search($('#staffSearchBox').val()).draw();
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

            $('#formEditUser').validate({
                rules: {
                    updateEmail: {
                        required: true,
                        maxlength: 200,
                        noWhiteSpace: true
                    },
                    updateFirstName: {
                        required: true,
                        maxlength: 50,
                        noWhiteSpace: true
                    },
                    updateJobRole: {
                        required: true,
                        maxlength: 100,
                        noWhiteSpace: true
                    },
                    updateLastName: {
                        required: true,
                        maxlength: 50,
                        noWhiteSpace: true
                    }
                },
                errorElement: 'small'
            });

            $('#formUpdateUserPassword').validate({
                rules: {
                    updatePassword: {
                        required: true,
                        maxlength: 150,
                        minlength: 8,
                        noWhiteSpace: true
                    },
                    updatePasswordConfirm: {
                        required: true,
                        maxlength: 150,
                        minlength: 8,
                        noWhiteSpace: true,
                        equalTo: '#updatePassword'
                    }
                },
                errorElement: 'small'
            });

            $(document).on('click', '.event-user-edit', function() {
                const $rowValues = $(this).closest('tr').find('td');
                $('.tooltip').tooltip('hide');
                $rowReference = $(this).closest('tr');

                const $formInputs = $('#formEditUser input');
                $formInputs.eq(0).val($(this).attr('data-userId'));
                $formInputs.eq(1).val($rowValues.eq(2).html());
                $formInputs.eq(2).val($rowValues.eq(0).html());
                $formInputs.eq(3).val($rowValues.eq(1).html());
                $formInputs.eq(4).val($rowValues.eq(3).html());
                $('#formUpdateUserPassword input').first().val($(this).attr('data-userId'));

                $('#ModalEditUser').modal('show');
            });

            $(document).on('submit', '#formEditUser', function(e) {
                e.preventDefault();
                showSpinner();
                $.ajax({
                    type: 'POST',
                    url: '../php/account/_editUser.php',
                    data: $('#formEditUser').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success == true) {
                            if ($($rowReference).closest('div.dataTables_wrapper').attr('id') === "staffTable_wrapper") {
                                let data = staffTable.row($rowReference).data();
                                data[0] = $('#formEditUser input').eq(2).val();
                                data[1] = $('#formEditUser input').eq(3).val();
                                data[2] = $('#formEditUser input').eq(1).val();
                                data[3] = $('#formEditUser input').eq(4).val();
                                staffTable.row($rowReference).data(data).draw();
                            } else {
                                let data = adminTable.row($rowReference).data();
                                data[0] = $('#formEditUser input').eq(2).val();
                                data[1] = $('#formEditUser input').eq(3).val();
                                data[2] = $('#formEditUser input').eq(1).val();
                                data[3] = $('#formEditUser input').eq(4).val();
                                adminTable.row($rowReference).data(data).draw();
                            }
                            displaySuccessToast(response.message);
                            $('#ModalEditUser').modal('hide');
                            hideSpinner();
                        } else {
                            displayErrorToastStandard(response.message);
                            hideSpinner();
                        }
                    },
                    error: function() {
                        hideSpinner();
                        displayErrorToastStandard('Something went wrong while handling this request');
                    }
                });

            });

            $(document).on('submit', '#formUpdateUserPassword', function(e) {
                e.preventDefault();
                showSpinner();
                $.ajax({
                    type: 'POST',
                    url: '../php/account/_updatePassword.php',
                    data: $('#formUpdateUserPassword').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success == true) {
                            displaySuccessToast(response.message);
                            $('#formUpdateUserPassword').trigger('reset');
                            $('#ModalEditUser').modal('hide');
                            hideSpinner();
                        } else {
                            displayErrorToastStandard(response.message);
                            hideSpinner();
                        }
                    },
                    error: function() {
                        hideSpinner();
                        displayErrorToastStandard('Something went wrong while handling this request');
                    }
                });
            });

            $(document).on('click', '.event-user-delete-staff', function() {
                $('.tooltip').tooltip('hide');
                const userEmail = $(this).closest('tr').find('td').eq(2).html();
                const $button = $(this);
                confirmDialog(`Are you sure you want to delete ${userEmail}? This action cannot be undone.`, 'Confirm Deletion', function() {
                    showSpinner();
                    const $parentToRemove = $button.closest('tr');
                    $.ajax({
                        type: 'POST',
                        url: '../php/account/_deleteUser.php',
                        data: {
                            id: $button.attr('data-userId')
                        },
                        dataType: 'json',
                        success: function(response) {
                            hideSpinner();
                            if (response.success == true) {
                                displaySuccessToast(response.message);
                                $parentToRemove.fadeOut(500, () => {
                                    staffTable.row($parentToRemove).remove().draw();
                                });
                            } else {
                                displayErrorToastStandard(response.message);
                            }
                        },
                        error: function() {
                            hideSpinner();
                            displayErrorToastStandard('Something went wrong while handling this request');
                        }
                    });
                });
            });

            $(document).on('click', '.event-user-delete-admin', function() {
                $('.tooltip').tooltip('hide');
                const userEmail = $(this).closest('tr').find('td').eq(2).html();
                const $button = $(this);
                confirmDialog(`Are you sure you want to delete ${userEmail}? This action cannot be undone.`, 'Confirm Deletion', function() {
                    showSpinner();
                    const $parentToRemove = $button.closest('tr');
                    $.ajax({
                        type: 'POST',
                        url: '../php/account/_deleteUser.php',
                        data: {
                            id: $button.attr('data-userId')
                        },
                        dataType: 'json',
                        success: function(response) {
                            hideSpinner();
                            if (response.success == true) {
                                displaySuccessToast(response.message);
                                $parentToRemove.fadeOut(500, () => {
                                    adminTable.row($parentToRemove).remove().draw();
                                });
                            } else {
                                displayErrorToastStandard(response.message);
                            }
                        },
                        error: function() {
                            hideSpinner();
                            displayErrorToastStandard('Something went wrong while handling this request');
                        }
                    });
                });
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