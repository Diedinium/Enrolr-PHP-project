import $ from 'jquery';
import validate from 'jquery-validation';
import 'bootstrap';
import { showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast, submitLogout, datesAreOnSameDay } from './functions';

// Provide necessary functions/variables to window so they can be used wtihin HTML page.
window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

$(function () {
    // Initialise tooltips once page is ready.
    $('[data-toggle="tooltip"]').tooltip();

    $('input, select').on('focusout', function () {
        $(this).removeClass('error');
    });

    // Remove toasts from DOM when hidden, just to keep things tidy ;)
    $(document).on('hidden.bs.toast', function ($event) {
        $event.target.remove();
    });

    // Store response details for upcoming
    let upcomingEnrolmentsResponse = [];
    let upcomingPaginateIndex = 1;

    // Store response details for past
    let pastEnrolmentsResponse = [];
    let pastPaginateIndex = 1;

    // Function variable to load upcoming enrolments.
    let getUpcomingEnrolments = (loadingAnimation = true) => {
        if (loadingAnimation) replaceEnrolmentsWithLoading('#upcoming');
        $.ajax({
            type: 'GET',
            url: '../php/enrol/_getUpcomingEnrolments.php',
            data: {
                pageIndex: upcomingPaginateIndex
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    upcomingEnrolmentsResponse = response.data;
                    renderEnrolments('#upcoming', false, upcomingEnrolmentsResponse, upcomingPaginateIndex);
                } else {
                    displayErrorToastStandard(response.message);
                }
            },
            error: function () {
                displayErrorToastStandard('Something went wrong fetching data for this page, please reload the page to try again.');
            }
        })
    };

    // Function variable to load past courses
    let getPastEnrolments = () => {
        replaceEnrolmentsWithLoading('#past');
        $.ajax({
            type: 'GET',
            url: '../php/enrol/_getPastEnrolments.php',
            data: {
                pageIndex: pastPaginateIndex
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    pastEnrolmentsResponse = response.data;
                    renderEnrolments('#past', true, pastEnrolmentsResponse, pastPaginateIndex);
                } else {
                    displayErrorToastStandard(response.message);
                }
            },
            error: function () {
                displayErrorToastStandard('Something went wrong fetching data for this page, please reload the page to try again.');
            }
        })
    };

    // Load initial enrolments
    getUpcomingEnrolments();

    // Render function; takes selector, past boolean, response and paginate index and builds list of enrolments in relevant selected area.
    function renderEnrolments(selector, isPast, response, paginateIndex) {
        // Display message on no found enrolments, otherwise render.
        if (response.length < 1) {
            // Hide nav, remove previous alerts and display different message based on past/present.
            $(`${selector} nav`).hide();
            $(`${selector} div.alert.alert-info`).remove();
            if (isPast) {
                $(`${selector}`).append(`<div class="alert alert-info">No past enrolments found.</div>`);
            }
            else {
                $(`${selector}`).append(`<div class="alert alert-info">No upcoming enrolments found.</div>`);
            }
            $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).empty();
        } else {
            // Empty enrolment container, clear previous alerts and ensure navbar is shown.
            $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).empty();
            $(`${selector} div.alert.alert-info`).remove();
            $(`${selector} nav`).show();

            // Select enrolment container
            const $enrolmentContainer = $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`);
            // For every item in response, loop and append until max size of 12 (index 11) is reached.
            response.every((enrolment, i) => {
                // Get dates/calculate booleans such as if course is within next 7 days, or is today.
                const createdDate = new Date(enrolment.created);
                const courseDate = new Date(enrolment.date);
                const todaysDatePlus7 = new Date().addDays(7);
                const isThisWeek = courseDate < todaysDatePlus7;
                const isToday = datesAreOnSameDay(courseDate, new Date());
                const isFullyBooked = enrolment.maxAttendees <= enrolment.enrolled;

                // Get the template and populate title, description and duration.
                let $enrolTemplate = $(`#templates`).children(`div`).eq(9).clone();
                $enrolTemplate.find(`.enrolr-title`).html(enrolment.title);
                $enrolTemplate.find(`.enrolr-description`).html(enrolment.description);
                $enrolTemplate.find(`.enrolr-badge-hours`).html(`${enrolment.duration} hours`);

                // If course is not this week, remove badge.
                if (!isThisWeek || isPast) {
                    $enrolTemplate.find(`.enrolr-badge-this-week`).remove();
                }

                // If course is not today, remove badge.
                if (!isToday || isPast) {
                    $enrolTemplate.find(`.enrolr-badge-today`).remove();
                }

                // If course is not fully booked, remove badge.
                if (!isFullyBooked || isPast) {
                    $enrolTemplate.find(`.enrolr-badge-fully-booked`).remove();
                }

                if (isPast) {
                    $enrolTemplate.find(`.enrolr-badge-hours`).remove();
                    $enrolTemplate.find(`button`).remove();
                }

                // Set the course date and attendees count
                $enrolTemplate.find(`.enrolr-date`).html(courseDate.toLocaleString([], {
                    dateStyle: `short`,
                    timeStyle: `short`,
                    hour12: true
                }));
                $enrolTemplate.find(`.enrolr-attendees`).html(`${enrolment.enrolled}/${enrolment.maxAttendees}`);

                // Set the course created date.
                $enrolTemplate.find(`.enrolr-created-date`).html(createdDate.toLocaleString([], {
                    dateStyle: `short`,
                    timeStyle: `short`,
                    hour12: true
                }));

                // If location is empty, remove list item. Otherwise set value.
                if (enrolment.location === null || enrolment.location === "") {
                    $enrolTemplate.find(`.enrolr-location`).closest('li').remove();
                }
                else {
                    $enrolTemplate.find(`.enrolr-location`).html(enrolment.location);
                }

                // If link is empty, remove list item. Otherwise set value.
                if (enrolment.link === null || enrolment.link === "") {
                    $enrolTemplate.find(`.enrolr-link`).closest('li').remove();
                }
                else {
                    $enrolTemplate.find(`.enrolr-link`).attr('href', enrolment.link);
                }

                // Set data onto element so it can be referenced in future events (like unenrolment)
                $enrolTemplate.data(enrolment);

                // Append the modified template to the enrolment container
                $enrolmentContainer.append($enrolTemplate);

                // Controls looping, exits every loop when index is more than or equal to 11.
                if (i >= 11) return false;
                else return true;
            });

            // Disable/enable previous paginate button
            if (paginateIndex > 1) {
                $(`${selector} nav ul li:nth-child(1)`).removeClass(`disabled`);
            }
            else {
                $(`${selector} nav ul li:nth-child(1)`).addClass(`disabled`);
            }

            // Disable/enable next paginate button
            if (response.length > 12) {
                $(`${selector} nav ul li:nth-child(2)`).removeClass(`disabled`);
            }
            else {
                $(`${selector} nav ul li:nth-child(2)`).addClass(`disabled`);
            }

            // Set paginate counter
            $(`${selector} nav div.rounded.border.px-2.py-1.mr-auto span`).eq(1).html(paginateIndex);

            // Ensure tooltips are enabled.
            $(`[data-toggle="tooltip"]`).tooltip();
        }
    }

    // Replaces an enrolments list with placeholder loading elements. Useful to display while data is being fetched etc
    function replaceEnrolmentsWithLoading(selector) {
        $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).empty();
        for (let i = 0; i < 3; i++) {
            $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).append($('#templates').children('div').eq(6).clone());
        }
    }

    // Event listener for previous clicks on upcoming enrolments
    $(document).on('click', '#upcoming nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        upcomingPaginateIndex--;
        getUpcomingEnrolments();
    });

    // Event listener for next clicks on upcoming enrolments
    $(document).on('click', '#upcoming nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        upcomingPaginateIndex++;
        getUpcomingEnrolments();
    });

    $('#past-tab').on('shown.bs.tab', function () {
        getPastEnrolments();
    });

    // Event listener for previous clicks on past enrolments
    $(document).on('click', '#past nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        pastPaginateIndex--;
        getPastEnrolments();
    });

    // Event listener for next clicks on past enrolments
    $(document).on('click', '#past nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        pastPaginateIndex++;
        getPastEnrolments();
    });

    let unenrolUser = async (courseId) => $.ajax({
        type: 'POST',
        url: '../php/enrol/_userUnenrol.php',
        data: {
            id: courseId
        },
        dataType: 'json',
        success: function (response) {
            if (response.success == true) {
                displaySuccessToast(response.message);
                return true;
            } else {
                displayErrorToastStandard(response.message);
                return false;
            }
        },
        error: function () {
            displayErrorToastStandard('Something went wrong while handling this request');
            return false;
        }
    });

    $(document).on('click', '#upcoming .event-course-unenrol', async function () {
        let result = await unenrolUser($(this).closest('div.col.mb-2.px-2').data().id);
        if (result) $(this).closest('div.col.mb-2.px-2').fadeOut(500, () => {
            getUpcomingEnrolments(false);
        });
    });
});