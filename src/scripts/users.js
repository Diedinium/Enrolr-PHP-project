import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast, submitLogout, confirmDialog } from './functions';
import 'datatables.net-bs4';

window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

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

    $('input, select').on('focusout', function() {
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
        dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function() {
            if ($('#staffTable tbody .dataTables_empty').length) {
                $('#staffTable_wrapper, #staff div.row.justify-content-end').hide();
            }
        }
    });

    $(document).on('keyup click', '#adminSearchBox, #adminSearchIcon', function() {
        adminTable.search($('#adminSearchBox').val()).draw();
    });

    $(document).on('keyup click', '#staffSearchBox, #staffSearchIcon', function() {
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