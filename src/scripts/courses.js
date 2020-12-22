import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast, submitLogout, confirmDialog } from './functions';

window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

$(function () {
    $('[data-toggle="tooltip"]').tooltip();

    $('input, select').on('focusout', function () {
        $(this).removeClass('error');
    });

    let formAddCourseValidator = $('#formAddCourse').validate({
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
            createCourseDate: {
                required: true
            },
            createCourseDuration: {
                required: true
            },
            createCourseAttendees: {
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

    $(document).on('hidden.bs.toast', function ($event) {
        $event.target.remove();
    });

    $('#ModalAddCourse').on('hidden.bs.modal', function () {
        $('#formAddCourse').trigger('reset');
        formAddCourseValidator.resetForm();
    });

    // Store response details
    let upcomingResponse = [];
    let userIsAdmin = false;

    // Load initial courses
    $.ajax({
        type: 'GET',
        url: '../php/course/_getUpcoming.php',
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

    // Renders upcoming courses, displaying appropriate user controls based on role.
    function renderUpcoming(isAdmin) {
        if (upcomingResponse.length < 1) {
            $('#upcomingSearchFilter').eq(0).hide();
            $('#courses').append('<div class="alert alert-info">No upcoming courses found.</div>');
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
        } else {
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
            $('#upcomingSearchFilter').eq(1).show();
            const $upcomingContainer = $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2');
            upcomingResponse.forEach(course => {
                const createdDate = new Date(course.created);
                const courseDate = new Date(course.date);
                const todaysDatePlus7 = new Date().addDays(7);
                const isThisWeek = courseDate < todaysDatePlus7;
                let $courseTemplate = $('#templates').children('div').eq(5).clone();
                $courseTemplate.find('h5.card-title').html(course.title);
                $courseTemplate.find('p.card-text').html(course.description);
                $courseTemplate.find('span.badge.badge-info').first().html(`${course.duration} hours`);

                if (!isThisWeek) {
                    $courseTemplate.find('span.badge.badge-success').first().remove();
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

                if (course.location === null) {
                    $courseTemplate.find('ul li').first().remove();
                } else {
                    $courseTemplate.find('ul li span span').first().html(course.location);
                }

                if (!isAdmin) {
                    $courseTemplate.find('div.card-footer div i').remove();
                }
                $courseTemplate.data(course);

                $upcomingContainer.append($courseTemplate);
            });
            $('[data-toggle="tooltip"]').tooltip();
        }
    };

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
});