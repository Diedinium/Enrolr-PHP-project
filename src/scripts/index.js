import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { displayErrorToast } from './functions';

$(function() {
    // Validate logon form via jQuery validate
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

    // Remove error class on focus out of input
    $('input, select').on('focusout', function() {
        $(this).removeClass('error');
    });
});