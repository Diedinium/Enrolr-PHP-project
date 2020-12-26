import $ from 'jquery';
import validate from 'jquery-validation';
import 'jquery-validation/dist/additional-methods';
import 'bootstrap';
import { submitLogout, confirmDialog, showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast } from './functions';

window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('#clearSearchIcon').hide();

    $('input, select').on('focusout', function () {
        $(this).removeClass('error');
    });

    let formCreateCourseValidator = $('#formCreateCourse').validate({
        rules: {
            createTitle: {
                required: true,
                maxlength: 255,
                noWhiteSpace: true
            },
            createDescription: {
                required: true,
                maxlength: 350,
                noWhiteSpace: true
            },
            createDate: {
                required: true
            },
            createDuration: {
                required: true
            },
            createMaxAttendees: {
                required: true
            },
            createLink: {
                required: '#createLocation:blank',
                maxlength: 1000,
                noWhiteSpace: true
            },
            createLocation: {
                required: '#createLink:blank',
                maxlength: 255,
                noWhiteSpace: true
            }
        },
        errorElement: 'small'
    });

    let formEditCourseValidator = $('#formEditCourse').validate({
        rules: {
            editTitle: {
                required: true,
                maxlength: 255,
                noWhiteSpace: true
            },
            editDescription: {
                required: true,
                maxlength: 350,
                noWhiteSpace: true
            },
            editDate: {
                required: true
            },
            editDuration: {
                required: true
            },
            editMaxAttendees: {
                required: true
            },
            editLink: {
                required: '#editLocation:blank',
                maxlength: 1000,
                noWhiteSpace: true
            },
            editLocation: {
                required: '#editLink:blank',
                maxlength: 255,
                noWhiteSpace: true
            }
        },
        errorElement: 'small'
    });

    let formUpcomingSearchValidator = $('#upcomingSearchForm').validate({
        onfocusout: false,
        rules: {
            searchMinDate: {
                require_from_group: [1, '.upcomingSearchGroup']
            },
            searchMaxDate: {
                require_from_group: [1, '.upcomingSearchGroup'],
                greaterThan: '#searchMinDate'
            },
            searchTitle: {
                require_from_group: [1, '.upcomingSearchGroup'],
                noWhiteSpace: true,
                minlength: 3,
                maxlength: 50
            }
        },
        errorElement: 'small'
    });

    $(document).on('hidden.bs.toast', function ($event) {
        $event.target.remove();
    });

    $('#ModalCreateCourse').on('hidden.bs.modal', function () {
        $('#formCreateCourse').trigger('reset');
        formCreateCourseValidator.resetForm();
    });

    // Store response details
    let upcomingResponse = [];
    let userIsAdmin = false;
    let paginateIndex = 1;
    let isSearching = false;

    // Load initial courses
    let getUpcoming = () => $.ajax({
        type: 'GET',
        url: '../php/course/_getUpcoming.php',
        data: {
            pageIndex: paginateIndex,
            searchMinDate: $('#searchMinDate').val(),
            searchMaxDate: $('#searchMaxDate').val(),
            searchTitle: $('#searchTitle').val()
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                upcomingResponse = response.data;
                userIsAdmin = response.isAdmin;
                renderUpcoming(userIsAdmin);
            } else {
                displayErrorToastStandard(response.message);
            }
        },
        error: function () {
            displayErrorToastStandard('Something went wrong fetching data for this page, please reload the page to try again.');
        }
    });

    getUpcoming();

    // Renders upcoming courses, displaying appropriate user controls based on role.
    function renderUpcoming(isAdmin) {
        if (upcomingResponse.length < 1) {
            if (!isSearching) {
                $('#upcomingSearchForm').hide();
            }
            $('#courses nav').hide();
            $('#courses').append('<div class="alert alert-info">No upcoming courses found.</div>');
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
        } else {
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
            $('#upcomingSearchForm').show();
            $('#courses nav').show();
            const $upcomingContainer = $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2');
            upcomingResponse.every((course, i) => {
                const createdDate = new Date(course.created);
                const courseDate = new Date(course.date);
                const todaysDatePlus7 = new Date().addDays(7);
                const isThisWeek = courseDate < todaysDatePlus7;
                const isToday = courseDate.getDate() === new Date().getDate();
                let $courseTemplate = $('#templates').children('div').eq(5).clone();
                $courseTemplate.find('h5.card-title').html(course.title);
                $courseTemplate.find('p.card-text').html(course.description);
                $courseTemplate.find('span.badge.badge-info').first().html(`${course.duration} hours`);

                if (!isThisWeek) {
                    $courseTemplate.find('span.badge.badge-success').first().remove();
                }

                if (isToday) {
                    $courseTemplate.find('span.badge.badge-success').first().html('Today!').removeClass('badge-success').addClass('badge-warning');
                }

                $courseTemplate.find('ul li span span').eq(1).html(courseDate.toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                }));
                $courseTemplate.find('ul li span span').eq(2).html(`${course.enrolled}/${course.maxAttendees}`);
                $courseTemplate.find('div.card-footer small').first().html(createdDate.toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                }));

                if (course.location === null || course.location === "") {
                    let $linkTemplate = $('#templates').children('li').eq(0).clone();
                    $linkTemplate.find('small').remove();
                    $linkTemplate.find('p').html(`<a href="${course.link}">${course.link}</a>`);
                    if (isAdmin) {
                        $courseTemplate.find('ul li').first().replaceWith($linkTemplate);
                    }
                    else {
                        if (course.isUserEnrolled == true) {
                            $courseTemplate.find('ul li').first().replaceWith($linkTemplate);
                        }
                        else {
                            $courseTemplate.find('ul li').first().replaceWith($('#templates').children('li').eq(0).clone());
                        }
                    }

                } else {
                    $courseTemplate.find('ul li span span').first().html(course.location);
                }

                if (!isAdmin) {
                    $courseTemplate.find('div.card-footer div i').remove();

                    if (course.isUserEnrolled == false) {
                        $courseTemplate.find('div.card-footer div.enrolr-actions-min-width').append(
                            '<button type="button" class="btn enrolr-brand-colour-bg text-white event-course-enrol">Enrol</button>'
                        );
                    }
                    else {
                        $courseTemplate.find('div.card-footer div.enrolr-actions-min-width').append(
                            '<button type="button" class="btn btn-secondary text-white event-course-unenrol">Unenrol</button>'
                        );
                    }
                }

                $courseTemplate.data(course);

                $upcomingContainer.append($courseTemplate);

                if (i >= 11) return false;
                else return true;
            });

            if (paginateIndex > 1) {
                $('#courses nav ul li:nth-child(1)').removeClass('disabled');
            }
            else {
                $('#courses nav ul li:nth-child(1)').addClass('disabled');
            }

            if (upcomingResponse.length > 12) {
                $('#courses nav ul li:nth-child(2)').removeClass('disabled');
            }
            else {
                $('#courses nav ul li:nth-child(2)').addClass('disabled');
            }

            $('[data-toggle="tooltip"]').tooltip();
        }
    };

    function replaceUpcomingWithLoading() {
        $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
        for (let i = 0; i < 3; i++) {
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').append($('#templates').children('div').eq(6).clone());
        }
    }

    $(document).on('click', '.event-delete-course', function () {
        confirmDialog(`Are you sure you want to delete "${$(this).closest('div.col.mb-2.px-2').data().title}", this action cannot be undone`, 'Confirm Deletion', () => {
            const $parentCourse = $(this).closest('div.col.mb-2.px-2');
            $.ajax({
                type: 'POST',
                url: '../php/course/_deleteCourse.php',
                data: {
                    id: $parentCourse.data().id
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success == true) {
                        displaySuccessToast(response.message);
                        $parentCourse.fadeOut(500, () => {
                            upcomingResponse = upcomingResponse.filter(course => course.id !== $parentCourse.data().id);
                            renderUpcoming(userIsAdmin);
                        });
                    } else {
                        displayErrorToastStandard(response.message);
                    }
                },
                error: function () {
                    displayErrorToastStandard('Something went wrong while handling this request');
                }
            });
        });
    });

    $(document).on('click', '.event-edit-course', function () {
        $('.tooltip').tooltip('hide');
        formEditCourseValidator.resetForm();
        const currentCourseData = $(this).closest('div.col.mb-2.px-2').data();
        $('#formEditCourse').trigger('reset');
        $('#editId').val(currentCourseData.id);
        $('#editTitle').val(currentCourseData.title);
        $('#editDate').val(currentCourseData.date.replace(' ', 'T'));
        $('#editDescription').val(currentCourseData.description);
        $('#editDuration').val(currentCourseData.duration);
        $('#editMaxAttendees').val(currentCourseData.maxAttendees);
        $('#editLocation').val(currentCourseData.location);
        $('#editLink').val(currentCourseData.link);
        $('#ModalEditCourse').modal('show').data(currentCourseData);
    });

    $(document).on('submit', '#formCreateCourse', function (e) {
        e.preventDefault();
        showSpinner();
        $.ajax({
            type: 'POST',
            url: '../php/course/_createCourse.php',
            data: $('#formCreateCourse').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
                    getUpcoming();
                    displaySuccessToast(response.message);
                    $('#ModalCreateCourse').modal('hide');
                    renderUpcoming(userIsAdmin);
                    hideSpinner();
                } else {
                    displayErrorToastStandard(response.message);
                    hideSpinner();
                }
            },
            error: function () {
                hideSpinner();
                displayErrorToastStandard('Something went wrong while handling this request');
            }
        });
    });

    $(document).on('submit', '#formEditCourse', function (e) {
        e.preventDefault();
        showSpinner();
        $.ajax({
            type: 'POST',
            url: '../php/course/_editCourse.php',
            data: $('#formEditCourse').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
                    let currentData = $('#ModalEditCourse').data();
                    currentData.title = $('#editTitle').val();
                    currentData.date = $('#editDate').val();
                    currentData.description = $('#editDescription').val();
                    currentData.duration = $('#editDuration').val();
                    currentData.maxAttendees = $('#editMaxAttendees').val();
                    currentData.location = $('#editLocation').val();
                    currentData.link = $('#editLink').val();

                    let foundIndex = upcomingResponse.findIndex(course => course.id === currentData.id);
                    upcomingResponse[foundIndex] = currentData;
                    upcomingResponse.sort((a, b) => new Date(a.date) - new Date(b.date));
                    displaySuccessToast(response.message);
                    $('#ModalEditCourse').modal('hide');
                    renderUpcoming(userIsAdmin);
                    hideSpinner();
                } else {
                    displayErrorToastStandard(response.message);
                    hideSpinner();
                }
            },
            error: function () {
                hideSpinner();
                displayErrorToastStandard('Something went wrong while handling this request');
            }
        });
    });

    let tabContent = document.querySelector('ul.nav.nav-tabs.card-header-tabs');

    $(document).on('click', '#courses nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        paginateIndex--;
        replaceUpcomingWithLoading();
        getUpcoming();
        tabContent.scrollIntoView();
    });

    $(document).on('click', '#courses nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        paginateIndex++;
        replaceUpcomingWithLoading();
        getUpcoming();
        tabContent.scrollIntoView();
    });

    $(document).on('enter', '#upcomingSearchForm input', function() {
        $('#upcomingSearchForm').trigger('submit');
    });

    $(document).on('click', '#upcomingSearchIcon', function() {
        $('#upcomingSearchForm').trigger('submit');
    });

    $(document).on('submit', '#upcomingSearchForm', function(e) {
        e.preventDefault();
        paginateIndex = 1;
        isSearching = true;
        replaceUpcomingWithLoading();
        getUpcoming();
        tabContent.scrollIntoView();
    });

    $(document).on('input', '#upcomingSearchForm input', function() {
        if ($('#searchMinDate').val() === "" && $('#searchMaxDate').val() === "" && $('#searchTitle').val() === "") {
            $('#clearSearchIcon').hide();
        }
        else {
            $('#clearSearchIcon').show();
        }
    });

    $(document).on('click', '#clearSearchIcon', function() {
        $('#upcomingSearchForm').trigger('reset');
        formUpcomingSearchValidator.resetForm();
        paginateIndex = 1;
        isSearching = false;
        replaceUpcomingWithLoading();
        getUpcoming();
    });
});