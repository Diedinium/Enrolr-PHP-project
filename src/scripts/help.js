import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { displayErrorToast, submitLogout } from './functions';

// Set variables that should be available on page
window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

$(function() {
    // Validate logon form
    $('#logonForm').validate({
        onkeyup: false,
        onclick: false,
        onfocusout: false,
        showErrors: function(errorMap, errorList) {
            this.defaultShowErrors();
            displayErrorToast(errorMap, errorList);
        },
        errorPlacement: function(error, element) {}
    });

    // Clear error class on focus out of input
    $('input, select').on('focusout', function() {
        $(this).removeClass('error');
    });
});