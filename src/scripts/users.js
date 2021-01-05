import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast, submitLogout, confirmDialog } from './functions';
import 'datatables.net-bs4';

// Set required variables for use in window
window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

$(function() {
    // Navigate to specified tab pased on query parameter on page load
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    let $rowReference;

    if (urlParams.has('tab')) {
        const tabName = urlParams.get('tab');

        $(`#${tabName}`).tab('show');
    }

    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // On tab being shown, adjust columns of datatable.
    $('a[data-toggle="tab"').on('shown.bs.tab', function(e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    // On focus out of inputs clear error class from input
    $('input, select').on('focusout', function() {
        $(this).removeClass('error');
    });

    // Init datatable for admin staff
    let adminTable = $('#adminTable').DataTable({
        ordering: false,
        scrollX: true,
        stateSave: true,
        lengthChange: false,
        dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    // Init datatable for regular staff
    let staffTable = $('#staffTable').DataTable({
        ordering: false,
        scrollX: true,
        stateSave: true,
        lengthChange: false,
        dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function() {
            if ($('#staffTable tbody .dataTables_empty').length) {
                $('#staffTable_wrapper, #staff div.row.justify-content-end').hide();
            }
        }
    });

    // On keyup or click of admin search box or admin search icon, perform search on admin table
    $(document).on('keyup click', '#adminSearchBox, #adminSearchIcon', function() {
        adminTable.search($('#adminSearchBox').val()).draw();
    });

    // On keyup or click of admin search box or admin search icon, perform search on staff table
    $(document).on('keyup click', '#staffSearchBox, #staffSearchIcon', function() {
        staffTable.search($('#staffSearchBox').val()).draw();
    });

    // Validate add user form via jQuery Validation
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

    // Validate edit user form via jQuery validation
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

    // Validate update user password form via jQuery validate
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

    // On click of element with event-user-edit, set values of form and show edit modal
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

    // On submit of add user form, post data to createUser endpoint
    $(document).on('submit', '#formAddUser', function(e) {
        e.preventDefault();
        showSpinner();
        $.ajax({
            type: 'POST',
            url: '../php/account/_createUser.php',
            data: $('#formAddUser').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    // On success, clone row template, set values and then add row to admin or staff table depending on if added user is admin or not
                    const $rowTemplate = $('#templates').children('div').eq(4).find('tr:first').clone();
                    $rowTemplate.find('td').eq(0).html(response.details.firstName);
                    $rowTemplate.find('td').eq(1).html(response.details.lastName);
                    $rowTemplate.find('td').eq(2).html(response.details.email);
                    $rowTemplate.find('td').eq(3).html(response.details.jobTitle);
                    $rowTemplate.find('td').eq(4).find('i').eq(0).attr('data-userId', response.details.id);
                    $rowTemplate.find('td').eq(4).find('i').eq(1).attr('data-userId', response.details.id);

                    if (response.details.isAdmin) {
                        $(`#admin-tab`).tab('show');
                        adminTable.row.add($rowTemplate).draw();
                    } else {
                        $('#staff div.alert-info').remove();
                        $('#staffTable_wrapper, #staff div.row.justify-content-end').show();
                        $('#staff-tab').tab('show');
                        staffTable.row.add($rowTemplate).draw();
                    }

                    // Rest add user form then hide modal
                    $('#formAddUser').trigger('reset');
                    displaySuccessToast(response.message);
                    $('#ModalAddUser').modal('hide');
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

    // On submit of edit user form, post data to editUser endpoint
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
                    // If row reference of edited user is in staff table, update staff table, otherwise update in admin table
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

    // On submit of update user password form, post data to update Password endpoint
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
                    // Clear form on success and hide modal
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

    // On click of element with event delete staff, confirm, if yes, post delete to deleteUser endpoint
    $(document).on('click', '.event-user-delete-staff', function() {
        $('.tooltip').tooltip('hide');
        const userEmail = $(this).closest('tr').find('td').eq(2).html();
        const $button = $(this);
        // Confirm with user that they really want to delete the staff member
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
                    // On success, fade user from table, then update table data to remove row and re-draw
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

    // On click of element with event delete admin, confirm, if yes then post delete to deleteUser endpoint
    $(document).on('click', '.event-user-delete-admin', function() {
        $('.tooltip').tooltip('hide');
        const userEmail = $(this).closest('tr').find('td').eq(2).html();
        const $button = $(this);
        // Confirm user really wants to delete admin
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
                    // On success, fade admin from admin table, then remove from datatable data and re-draw
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

    // When toast is hidden, remove from DOM
    $(document).on('hidden.bs.toast', function($event) {
        $event.target.remove();
    });
});