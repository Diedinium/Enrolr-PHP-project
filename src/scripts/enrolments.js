import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast, submitLogout } from './functions';

window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

$(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $('input, select').on('focusout', function() {
        $(this).removeClass('error');
    });

    $(document).on('hidden.bs.toast', function($event) {
        $event.target.remove();
    });
});