import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { displayErrorToast } from './functions';

$(function() {
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

    $('input, select').on('focusout', function() {
        $(this).removeClass('error');
    });
});