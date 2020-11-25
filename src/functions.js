window.displayErrorToast = function (errorMap, errorList) {
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

window.displayErrorToastStandard = function (errorMessage, errorTitle = null) {
    let $toastError = $('#templates').find('#templateToastError').clone();
    let $toastContainer = $('#toastContainer');

    if (errorTitle != null) {
        $toastError.find('strong').first().html(errorTitle);
    }
    $toastError.find('.toast-body').first().append(`<p class="mb-0">${errorMessage}</p>`)

    $toastError.toast('show');
    $toastContainer.append($toastError);
}

window.displaySuccessToast = function (successMessage, successTitle = null) {
    let $toastSuccess = $('#templates').find('#templateToastSuccess').clone();
    let $toastContainer = $('#toastContainer');

    if (successTitle != null) {
        $toastSuccess.find('strong').first().html(successTitle);
    }
    $toastSuccess.find('.toast-body').first().append(`<p class="mb-0">${successMessage}</p>`)

    $toastSuccess.toast('show');
    $toastContainer.append($toastSuccess);
}

window.displayStandardToast = function (message, title = null) {
    let $toastStandard = $('#templates').find('#templateToastStandard').clone();
    let $toastContainer = $('#toastContainer');

    if (title != null) {
        $toastStandard.find('strong').first().html(title);
    }
    $toastStandard.find('.toast-body').first().append(`<p class="mb-0">${message}</p>`)

    $toastStandard.toast('show');
    $toastContainer.append($toastStandard);
}

window.confirmDialog = function (message, title, yesCallback) {
    $('#confirmMessage').html(message);
    $('#confirmTitle').html(title);
    $('#confirmModal').modal('show');

    $('#confirmBtnYes').on('click', function () {
        $('#confirmModal').modal('hide');
        yesCallback();
    });
    $('#confirmBtnNo').on('click', function () {
        $('#confirmModal').modal('hide');
    });
}

$.validator.addMethod("noWhiteSpace", function (value, element) {
    if (value && !value.trim()) {
        return false;
    }
    else {
        return true;
    }
}, "Whitespace (spaces and tabs) alone are not allowed.");

