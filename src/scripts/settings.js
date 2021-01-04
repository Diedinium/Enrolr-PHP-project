import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast, submitLogout, confirmDialog } from './functions';

window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;
window.showSpinner = showSpinner;
window.confirmDialog = confirmDialog;

$(function() {
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    if (urlParams.has('tab')) {
        const tabName = urlParams.get('tab');

        $(`#${tabName}`).tab('show');
    }

    $('[data-toggle="tooltip"]').tooltip();


    $('input, select').on('focusout', function() {
        $(this).removeClass('error');
    });

    $('#formChangePassword').validate({
        rules: {
            currentPassword: {
                required: true,
                maxlength: 100,
                noWhiteSpace: true
            },
            newPassword: {
                required: true,
                maxlength: 100,
                minlength: 8,
                noWhiteSpace: true
            },
            newPasswordConfirm: {
                required: true,
                maxlength: 100,
                minlength: 8,
                noWhiteSpace: true,
                equalTo: '#newPassword'
            }
        },
        errorElement: 'small'
    });

    $('#formUpdateDetails').validate({
        rules: {
            firstName: {
                required: true,
                maxlength: 50,
                noWhiteSpace: true
            },
            lastName: {
                required: true,
                maxlength: 50,
                noWhiteSpace: true
            },
            jobRole: {
                required: true,
                maxlength: 100,
                noWhiteSpace: true
            }
        },
        errorElement: 'small'
    });

    $(document).on('submit', '#formChangePassword', function(e) {
        e.preventDefault();
        showSpinner();
        $.ajax({
            type: 'POST',
            url: '../php/account/_changePassword.php',
            data: $('#formChangePassword').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    displaySuccessToast(response.message);
                    $('#formChangePassword').trigger('reset');
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

    $(document).on('submit', '#formUpdateDetails', function(e) {
        e.preventDefault();
        showSpinner();
        $.ajax({
            type: 'POST',
            url: '../php/account/_updateDetails.php',
            data: $('#formUpdateDetails').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $('#details form div span').eq(1).html(`${$('#firstName').val()} ${$('#lastName').val()}`);
                    $('#details form div span').eq(2).html($('#jobRole').val());
                    displaySuccessToast(response.message);
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

    $(document).on('click', '#formDeleteAllEnrollments button', function(e) {
        e.preventDefault();
        confirmDialog('Are you sure you want to delete all your enrolments? This action cannot be undone, and will not remove past enrolments.', 'Confirm Unenrollment', function() {
            showSpinner();
            $('#formDeleteAllEnrollments').trigger('submit');
        });
    });

    $(document).on('click', '#formDeleteAccount button', function(e) {
        e.preventDefault();
        confirmDialog(`Are you sure you want to delete your account? This action cannot be undone.`, 'Confirm Deletion', function() {
            showSpinner();
            $('#formDeleteAccount').trigger('submit');
        });
    });

    $(document).on('hidden.bs.toast', function($event) {
        $event.target.remove();
    });
});