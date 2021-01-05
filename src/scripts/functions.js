import $ from 'jquery';
import 'bootstrap';

// Clones toast template and appends into toast contaienr, for error list type toasts
function displayErrorToast(errorMap, errorList) {
    if (Object.keys(errorMap).length > 0) {
        let $toastError = $('#templates').find('#templateToastError').clone();
        let $toastContainer = $('#toastContainer');

        for (const [key, value] of Object.entries(errorMap)) {
            let errorFormatted = `<p class="mb-0 text-danger">${key} - ${value}</p>`
            $toastError.find('.toast-body').first().append(errorFormatted);
        }
        
        $toastError.toast('show');
        $toastContainer.append($toastError);
    }
}

// Clones toast template and appends into toast contaienr, for standard error toasts
function displayErrorToastStandard(errorMessage, errorTitle = null) {
    let $toastError = $('#templates').find('#templateToastError').clone();
    let $toastContainer = $('#toastContainer');

    if (errorTitle != null) {
        $toastError.find('strong').first().html(errorTitle);
    }
    $toastError.find('.toast-body').first().append(`<p class="mb-0">${errorMessage}</p>`)

    $toastError.toast('show');
    $toastContainer.append($toastError);
}

// Clones toast template and appends into toast contaienr, for success toasts
function displaySuccessToast(successMessage, successTitle = null) {
    let $toastSuccess = $('#templates').find('#templateToastSuccess').clone();
    let $toastContainer = $('#toastContainer');

    if (successTitle != null) {
        $toastSuccess.find('strong').first().html(successTitle);
    }
    $toastSuccess.find('.toast-body').first().append(`<p class="mb-0">${successMessage}</p>`)

    $toastSuccess.toast('show');
    $toastContainer.append($toastSuccess);
}

// Clones toast template and appends into toast contaienr, for standard (non error or success) toasts
function displayStandardToast(message, title = null) {
    let $toastStandard = $('#templates').find('#templateToastStandard').clone();
    let $toastContainer = $('#toastContainer');

    if (title != null) {
        $toastStandard.find('strong').first().html(title);
    }
    $toastStandard.find('.toast-body').first().append(`<p class="mb-0">${message}</p>`)

    $toastStandard.toast('show');
    $toastContainer.append($toastStandard);
}

// Confirm dialog, on click of yes button, returns yesCallback, otherwise just hides modal.
function confirmDialog(message, title, yesCallback) {
    $('#confirmMessage').html(message);
    $('#confirmTitle').html(title);
    $('#confirmModal').modal('show');

    $('#confirmBtnYes').off().on('click', function () {
        $('#confirmModal').modal('hide');
        yesCallback();
    });
    $('#confirmBtnNo').off().on('click', function () {
        $('#confirmModal').modal('hide');
    });
}

// Shows spinner and loader
function showSpinner() {
    $('.loader:first, .overlay:first').removeClass('d-none');
}

// Hides spinner and loader
function hideSpinner() {
    $('.loader:first, .overlay:first').addClass('d-none');
}

// Submits logout form
function submitLogout() {
    $('#logoutForm').trigger('submit');
}

// Comparison to check if two provided dates are on the same date
const datesAreOnSameDay = (first, second) =>
    first.getFullYear() === second.getFullYear() &&
    first.getMonth() === second.getMonth() &&
    first.getDate() === second.getDate();

// Adds the "noWhiteSpace" validator to jQuery validation. Returns false if value and trimmed value both equal false
$.validator.addMethod("noWhiteSpace", function (value, element) {
    if (value && !value.trim()) {
        return false;
    }
    else {
        return true;
    }
}, "Whitespace (spaces and tabs) alone are not allowed.");

// Adds the "GreateThan" method to jQuery validate, if dates are valid, returns true/false on if value date is greater than params date, returns true otherwise
$.validator.addMethod("greaterThan", function (value, element, params) {

    if (!/Invalid|NaN/.test(new Date(value)) && !/Invalid|NaN/.test(new Date($(params).val()))) {
        return new Date(value) > new Date($(params).val());
    }
    else {
        return true;
    }
}, "This field must be greater than it's corresponding field");

// Allows days to be added to a Date type
Date.prototype.addDays = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
};

// Export relevant functions for use in other files.
export { displayErrorToast, displayErrorToastStandard, displaySuccessToast, displayStandardToast, confirmDialog, showSpinner, hideSpinner, submitLogout, datesAreOnSameDay };

