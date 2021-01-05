import $ from 'jquery';
import validate from 'jquery-validation';
import 'jquery-validation/dist/additional-methods';
import 'bootstrap';
import { submitLogout, confirmDialog, showSpinner, hideSpinner, displayErrorToastStandard, displaySuccessToast, datesAreOnSameDay } from './functions';

// Set variables that are needed in window
window.jQuery = $;
window.$ = $;
window.submitLogout = submitLogout;

$(function () {
    // Initialise tooltips, and ensure clear icons are hidden
    $('[data-toggle="tooltip"]').tooltip();
    $('#clearSearchIcon').hide();
    $('#clearPastSearchIcon').hide();

    // Clear error class on focus out from inputs
    $('input, select').on('focusout', function () {
        $(this).removeClass('error');
    });

    // Init Create Course Validator and store in variable.
    let formCreateCourseValidator = $('#formCreateCourse').validate({
        rules: {
            createTitle: {
                required: true,
                maxlength: 255,
                noWhiteSpace: true
            },
            createDescription: {
                required: true,
                maxlength: 600,
                noWhiteSpace: true
            },
            createDate: {
                required: true
            },
            createDuration: {
                required: true,
                min: 0.1
            },
            createMaxAttendees: {
                required: true,
                min: 1
            },
            createLink: {
                required: '#createLocation:blank',
                maxlength: 1000,
                noWhiteSpace: true,
                url: true
            },
            createLocation: {
                required: '#createLink:blank',
                maxlength: 255,
                noWhiteSpace: true
            }
        },
        errorElement: 'small'
    });

    // Init Edit course validator and store in variable.
    let formEditCourseValidator = $('#formEditCourse').validate({
        rules: {
            editTitle: {
                required: true,
                maxlength: 255,
                noWhiteSpace: true
            },
            editDescription: {
                required: true,
                maxlength: 600,
                noWhiteSpace: true
            },
            editDate: {
                required: true
            },
            editDuration: {
                required: true,
                min: 0.1
            },
            editMaxAttendees: {
                required: true,
                min: 1
            },
            editLink: {
                required: '#editLocation:blank',
                maxlength: 1000,
                noWhiteSpace: true,
                url: true
            },
            editLocation: {
                required: '#editLink:blank',
                maxlength: 255,
                noWhiteSpace: true
            }
        },
        errorElement: 'small'
    });

    // Init Upcoming Search Form validator
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

    // Init Past Search Form Validator
    let formPastSearchValidator = $('#pastSearchForm').validate({
        onfocusout: false,
        rules: {
            searchPastMinDate: {
                require_from_group: [1, '.pastSearchGroup']
            },
            searchPastMaxDate: {
                require_from_group: [1, '.pastSearchGroup'],
                greaterThan: '#searchPastMinDate'
            },
            searchPastTitle: {
                require_from_group: [1, '.pastSearchGroup'],
                noWhiteSpace: true,
                minlength: 3,
                maxlength: 50
            }
        },
        errorElement: 'small'
    });

    // Whenever a toast is finished hiding, remove from DOM.
    $(document).on('hidden.bs.toast', function ($event) {
        $event.target.remove();
    });

    // When create course modal is hidden, reset form and validator
    $('#ModalCreateCourse').on('hidden.bs.modal', function () {
        $('#formCreateCourse').trigger('reset');
        formCreateCourseValidator.resetForm();
    });

    // Store response details for upcoming courses
    let upcomingResponse = [];
    let userIsAdmin = false;
    let paginateIndex = 1;
    let isSearching = false;

    // Get upcoming courses, pass paginate index and search parameters (empty by default)
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

    // Fetch initial courses
    getUpcoming();

    // Renders upcoming courses, displaying appropriate user controls based on role.
    function renderUpcoming(isAdmin) {
        // If there are no courses to display
        if (upcomingResponse.length < 1) {
            // Do not hide search inputs if user found no results while searching
            if (!isSearching) {
                $('#upcomingSearchForm').hide();
            }

            // Hide elements and appent alert
            $('#courses nav').hide();
            $('#courses div.alert.alert-info').remove();
            $('#courses').append('<div class="alert alert-info">No upcoming courses found.</div>');
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
        } else {
            // Ensure course container is empty, search form is shown, pagination is shown and all alerts are removed.
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
            $('#courses div.alert.alert-info').remove();
            $('#upcomingSearchForm').show();
            $('#courses nav').show();
            // Store reference to upcoming course container
            const $upcomingContainer = $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2');
            // For each upcomging course in response
            upcomingResponse.every((course, i) => {
                // Calculate date related values
                const createdDate = new Date(course.created);
                const courseDate = new Date(course.date);
                const todaysDatePlus7 = new Date().addDays(7);
                const isThisWeek = courseDate < todaysDatePlus7;
                const isToday = datesAreOnSameDay(courseDate, new Date());
                // Check if course is fully booked
                const isFullyBooked = course.maxAttendees <= course.enrolled;
                // Get course template and start setting values
                let $courseTemplate = $('#templates').children('div').eq(5).clone();
                $courseTemplate.find('h5.card-title').html(course.title);
                $courseTemplate.find('p.card-text').html(course.description);
                $courseTemplate.find('span.badge.badge-info').first().html(`${course.duration} hours`);

                // If course is not this week, remove badge.
                if (!isThisWeek) {
                    $courseTemplate.find('span.badge.badge-success').first().remove();
                }

                // If course is today, append badge
                if (isToday) {
                    $courseTemplate.find('span.badge.badge-success').first().html('Today!').removeClass('badge-success').addClass('badge-warning');
                }

                // If course is fully booked, append badge
                if (isFullyBooked) {
                    $courseTemplate.find('div.card-body').children('div').first().append('<span class="badge badge-danger">Fully Booked</span>')
                }

                // Display course date
                $courseTemplate.find('ul li span span').eq(1).html(courseDate.toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                }));

                // Display enrolled/max attendees and course created date.
                $courseTemplate.find('ul li span span').eq(2).html(`${course.enrolled}/${course.maxAttendees}`);
                $courseTemplate.find('div.card-footer small').first().html(createdDate.toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                }));

                // If course location is empty
                if (course.location === null || course.location === "") {
                    // Get link template
                    let $linkTemplate = $('#templates').children('li').eq(0).clone();
                    $linkTemplate.find('small').remove();
                    $linkTemplate.find('p').html(`<a href="${course.link}">${course.link}</a>`);
                    if (isAdmin) {
                        // Always show links for admin users
                        $courseTemplate.find('ul li').first().replaceWith($linkTemplate);
                    }
                    else {
                        // Only show link if user is enrolled
                        if (course.isUserEnrolled == true) {
                            $courseTemplate.find('ul li').first().replaceWith($linkTemplate);
                        }
                        else {
                            $courseTemplate.find('ul li').first().replaceWith($('#templates').children('li').eq(0).clone());
                        }
                    }

                } else {
                    // Set course location if not empty
                    $courseTemplate.find('ul li span span').first().html(course.location);
                }

                // If user is not admin, remove admin actions
                if (!isAdmin) {
                    $courseTemplate.find('div.card-footer div i').remove();

                    // Append unenrol/enrol button based on if user is enrolled or not.
                    if (course.isUserEnrolled == false) {
                        // Disable enrol button if course is fully booked.
                        if (course.enrolled < course.maxAttendees) {
                            $courseTemplate.find('div.card-footer div.enrolr-actions-min-width').append(
                                '<button type="button" class="btn enrolr-brand-colour-bg text-white event-course-enrol">Enrol</button>'
                            );
                        }
                        else {
                            $courseTemplate.find('div.card-footer div.enrolr-actions-min-width').append(
                                '<button type="button" class="btn enrolr-brand-colour-bg text-white event-course-enrol disabled">Enrol</button>'
                            );
                        }
                    }
                    else {
                        $courseTemplate.find('div.card-footer div.enrolr-actions-min-width').append(
                            '<button type="button" class="btn btn-secondary text-white event-course-unenrol">Unenrol</button>'
                        );
                    }
                }

                // Store data on element for future reference
                $courseTemplate.data(course);
                // Add to container
                $upcomingContainer.append($courseTemplate);

                // Exit every loop when max number of courses to display is reached
                if (i >= 11) return false;
                else return true;
            });

            // If the paginate index is more than 1, enable the previous button
            if (paginateIndex > 1) {
                $('#courses nav ul li:nth-child(1)').removeClass('disabled');
            }
            else {
                $('#courses nav ul li:nth-child(1)').addClass('disabled');
            }

            // If the response length is more than 12, display next button
            if (upcomingResponse.length > 12) {
                $('#courses nav ul li:nth-child(2)').removeClass('disabled');
            }
            else {
                $('#courses nav ul li:nth-child(2)').addClass('disabled');
            }

            // Set page number
            $('#courses nav div.rounded.border.px-2.py-1.mr-auto span').eq(1).html(paginateIndex);

            // Ensure tooltips are initialised
            $('[data-toggle="tooltip"]').tooltip();
        }
    };

    // Replace all courses with loading templates
    function replaceCoursesWithLoading(selector) {
        $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).empty();
        for (let i = 0; i < 3; i++) {
            $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).append($('#templates').children('div').eq(6).clone());
        }
    }

    // Event listener for course deletions
    $(document).on('click', '.event-delete-course', function () {
        // Confirm with user before deleting
        confirmDialog(`Are you sure you want to delete "${$(this).closest('div.col.mb-2.px-2').data().title}", this action cannot be undone`, 'Confirm Deletion', () => {
            // Store reference to parent course to remove
            const $parentCourse = $(this).closest('div.col.mb-2.px-2');
            // POST course to delete
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
                        // Fade course out and remove from list
                        $parentCourse.fadeOut(500, () => {
                            upcomingResponse = upcomingResponse.filter(course => course.id !== $parentCourse.data().id);
                            // If the number of courses is less than 1 but pagination index is more than 1, go back by page before fetching data.
                            if (upcomingResponse.length < 1 && paginateIndex > 1) {
                                paginateIndex--;
                                replaceCoursesWithLoading('#courses');
                                getUpcoming();
                            }
                            else {
                                replaceCoursesWithLoading('#courses');
                                getUpcoming();
                            }
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

    // Listen for course edit clicks
    $(document).on('click', '.event-edit-course', function () {
        $('.tooltip').tooltip('hide');
        formEditCourseValidator.resetForm();
        // Set form values based on data from clicked on course
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
        $('#ModalEditCourse').data(currentCourseData);
        $('#ModalEditCourse').modal('show');
    });

    // Store values for enrolled staff
    let paginateIndexEnrolledStaff = 1;
    let enrolledStaffResponse = [];

    // Store function to fetch enrolled staff
    let getEnrolledStaff = (courseId, modal, allowDelete) => $.ajax({
        type: 'GET',
        url: '../php/course/_getStaffEnrolledOnCourse.php',
        data: {
            courseId: courseId,
            pageIndex: paginateIndexEnrolledStaff
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                enrolledStaffResponse = response.data;
                renderEnrolledStaff(modal, allowDelete);
            } else {
                displayErrorToastStandard(response.message);
            }
        },
        error: function () {
            displayErrorToastStandard('Something went wrong fetching enrolled users, please reload the page to try again.');
        }
    });

    // When edit course modal is fully shown
    $('#ModalEditCourse').on('shown.bs.modal', function () {
        // Ensure old alerts/enrolled users are removed
        $('#ModalEditCourse div.modal-body ul').first().empty();
        $('#ModalEditCourse div.modal-body div.alert.alert-info').remove();
        // Show loading placeholder
        $('#ModalEditCourse .enrolled-staff-placeholder').show();
        // Set paginate index back to 1
        paginateIndexEnrolledStaff = 1;
        // Get enrolled users based on data
        getEnrolledStaff($(this).data().id, '#ModalEditCourse', true);
    });

    // When edit course modal is fully hidden
    $('#ModalEditCourse').on('hidden.bs.modal', function () {
        // Update values (needed incase enrolled users have been removed, to ensure that correct number is displayed)
        let foundIndex = upcomingResponse.findIndex(course => course.id === $(this).data().id);
        upcomingResponse[foundIndex].enrolled = $(this).data().enrolled;
        // Re-render upcoming
        renderUpcoming(userIsAdmin);
    });

    // Listen for previous pagination clicks for enrolled staff, only when not disabled
    $(document).on('click', '#ModalEditCourse nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        // Reduce page number by 1 and re-fetch enrolled staff.
        paginateIndexEnrolledStaff--;
        $('#ModalEditCourse div.modal-body ul').first().empty();
        $('#ModalEditCourse .enrolled-staff-placeholder').show();
        getEnrolledStaff($('#ModalEditCourse').data().id, '#ModalEditCourse', true);
    });

    // Listen for next pagination clicks for enrolled staff, only when not disabled
    $(document).on('click', '#ModalEditCourse nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        // Increase page number by 1 and re-fetch enrolled staff.
        paginateIndexEnrolledStaff++;
        $('#ModalEditCourse div.modal-body ul').first().empty();
        $('#ModalEditCourse .enrolled-staff-placeholder').show();
        getEnrolledStaff($('#ModalEditCourse').data().id, '#ModalEditCourse', true);
    });

    // Event listener for removing a user from a course
    $(document).on('click', '.event-user-remove-from-course', function () {
        // Store reference to a list item
        const $listItem = $(this).closest('li.list-group-item');
        // POST to remove user from course
        $.ajax({
            type: 'POST',
            url: '../php/course/_deleteEnrollment.php',
            data: {
                courseId: $('#ModalEditCourse').data().id,
                userId: $(this).closest('li.list-group-item').data().id
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
                    // On success, reduce enrolled count by one
                    $('#ModalEditCourse').data().enrolled--;
                    displaySuccessToast(response.message);
                    // Fade item out and refresh, reduce page count by one if no staff remain
                    $listItem.fadeOut(500, () => {
                        enrolledStaffResponse = enrolledStaffResponse.filter(staff => staff.id !== $listItem.data().id);
                        if (enrolledStaffResponse.length < 1 && paginateIndexEnrolledStaff > 1) {
                            paginateIndexEnrolledStaff--;
                        }
                        $('#ModalEditCourse div.modal-body ul').first().empty();
                        $('#ModalEditCourse .enrolled-staff-placeholder').show();
                        getEnrolledStaff($('#ModalEditCourse').data().id, '#ModalEditCourse', true);
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

    // Render enrolled staff, multi-purpose, for both view enrolled staff and edit enrolled staff
    function renderEnrolledStaff(modal, allowDelete) {
        // If pageinate index is more than 1, enable previous button
        if (paginateIndexEnrolledStaff > 1) {
            $(`${modal} nav ul li:nth-child(1)`).removeClass('disabled');
        }
        else {
            $(`${modal} nav ul li:nth-child(1)`).addClass('disabled');
        }

        // If enrolled staff length is more than 5, enable next button
        if (enrolledStaffResponse.length > 5) {
            $(`${modal} nav ul li:nth-child(2)`).removeClass('disabled');
        }
        else {
            $(`${modal} nav ul li:nth-child(2)`).addClass('disabled');
        }

        // Set page number
        $(`${modal} nav div.rounded.border.px-2.py-1.mr-auto span`).eq(1).html(paginateIndexEnrolledStaff);

        // If length is less than 1 display message about no enrolled staff, otherwise, render enrolled staff
        if (enrolledStaffResponse.length < 1) {
            $(`${modal} div.modal-body div.alert.alert-info`).remove();
            $(`${modal} div.modal-body`).children('div').first().append('<div class="alert alert-info">No staff are currently enrolled.</div>');
            $(`${modal} div.modal-body nav`).hide();
            $(`${modal} .enrolled-staff-placeholder`).hide();
        }
        else {
            // Hide placeholder and ensure pagination controls are shown
            $(`${modal} .enrolled-staff-placeholder`).hide();
            $(`${modal} div.modal-body nav`).show();
            $(`${modal} div.modal-body div.alert.alert-info`).remove();
            // For each enrolled staff member, clone template and render
            enrolledStaffResponse.every((staff, i) => {
                let $enrolledStaffTemplate = $('#templates').children('li').eq(1).clone();
                $enrolledStaffTemplate.find('p strong').html(`${staff.firstName} ${staff.lastName}`);
                $enrolledStaffTemplate.find('p em').html(staff.jobTitle);
                $enrolledStaffTemplate.find('p').eq(1).html(staff.email);
                // Set date of enrollment in template
                $enrolledStaffTemplate.find('small').html(`Enrolled: ${new Date(staff.dateCreated).toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                })}`);
                // If deletion is false, remove the delete button.
                if (!allowDelete) {
                    $enrolledStaffTemplate.find('button').remove();
                }
                // Add data
                $enrolledStaffTemplate.data(staff);
                // Append template to container
                $(`${modal} div.modal-body ul`).first().append($enrolledStaffTemplate);
                // Stop looping when 5th enrolled staff member has been added
                if (i >= 4) return false;
                else return true;
            });
        }
    }

    // Event listener for submission of create course
    $(document).on('submit', '#formCreateCourse', function (e) {
        e.preventDefault();
        showSpinner();
        // POST form values to create course endpoint
        $.ajax({
            type: 'POST',
            url: '../php/course/_createCourse.php',
            data: $('#formCreateCourse').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
                    // On success, fetch upcoming to cause courses to re-render
                    getUpcoming();
                    displaySuccessToast(response.message);
                    $('#ModalCreateCourse').modal('hide');
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

    // Event listener for submission of edit course form
    $(document).on('submit', '#formEditCourse', function (e) {
        e.preventDefault();
        showSpinner();
        // POST form values to edit course endpoint
        $.ajax({
            type: 'POST',
            url: '../php/course/_editCourse.php',
            data: $('#formEditCourse').serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
                    // On success, set values from form into current data
                    let currentData = $('#ModalEditCourse').data();
                    currentData.title = $('#editTitle').val();
                    currentData.date = $('#editDate').val();
                    currentData.description = $('#editDescription').val();
                    currentData.duration = $('#editDuration').val();
                    currentData.maxAttendees = $('#editMaxAttendees').val();
                    currentData.location = $('#editLocation').val();
                    currentData.link = $('#editLink').val();
                    
                    // Replace course in upcoming with edited details
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

    // Get tabcontent top (used to scroll up to)
    let tabContent = document.querySelector('ul.nav.nav-tabs.card-header-tabs');

    // Event listener for previous pagination click on upcoming courses
    $(document).on('click', '#courses nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        // Reduces paginate index by 1 and refetches data
        paginateIndex--;
        replaceCoursesWithLoading('#courses');
        getUpcoming();
        tabContent.scrollIntoView();
    });

    // Event listener for next paginaton click on upcoming courses
    $(document).on('click', '#courses nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        // Increases paginate index by 1 and refetches data.
        paginateIndex++;
        replaceCoursesWithLoading('#courses');
        getUpcoming();
        tabContent.scrollIntoView();
    });

    // Event listener for enter keypresses on upcoming search
    $(document).on('enter', '#upcomingSearchForm input', function () {
        $('#upcomingSearchForm').trigger('submit');
    });

    // Event listener for click on upcoming search icon on upcoming search
    $(document).on('click', '#upcomingSearchIcon', function () {
        $('#upcomingSearchForm').trigger('submit');
    });

    // Event listener for submit of upcoming search form
    $(document).on('submit', '#upcomingSearchForm', function (e) {
        e.preventDefault();
        // Ensures pagination index is set to 1 when search is performed
        paginateIndex = 1;
        isSearching = true;
        replaceCoursesWithLoading('#courses');
        getUpcoming();
        tabContent.scrollIntoView();
    });

    // Event listener for input in upcoming search form
    $(document).on('input', '#upcomingSearchForm input', function () {
        // If all fields do not have a value, hide clear button
        if ($('#searchMinDate').val() === "" && $('#searchMaxDate').val() === "" && $('#searchTitle').val() === "") {
            $('.tooltip').tooltip('hide');
            $('#clearSearchIcon').hide();
        }
        else {
            $('#clearSearchIcon').show();
        }
    });

    // Event listener for click on clear search icon for upcoming courses
    $(document).on('click', '#clearSearchIcon', function () {
        // Resets upcoming search form and validator, refetches data
        $('#upcomingSearchForm').trigger('reset');
        $('.tooltip').tooltip('hide');
        $('#clearSearchIcon').hide();
        formUpcomingSearchValidator.resetForm();
        paginateIndex = 1;
        isSearching = false;
        replaceCoursesWithLoading('#courses');
        getUpcoming();
    });

    // Event listener for click on non-disabled course enrol button
    $(document).on('click', '.event-course-enrol:not(.disabled)', function () {
        // Store reference to course Id
        const courseId = $(this).closest('div.col.mb-2.px-2').data().id;
        // POST course Id to enrol to endpoint
        $.ajax({
            type: 'POST',
            url: '../php/enrol/_userEnrol.php',
            data: {
                id: courseId
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
                    // Update appropriate course id and enrolled count and rerender data.
                    displaySuccessToast(response.message);
                    upcomingResponse[upcomingResponse.findIndex(course => course.id === courseId)].isUserEnrolled = 1;
                    upcomingResponse[upcomingResponse.findIndex(course => course.id === courseId)].enrolled++;
                    renderUpcoming();
                } else {
                    displayErrorToastStandard(response.message);
                }
            },
            error: function () {
                displayErrorToastStandard('Something went wrong while handling this request');
            }
        });
    });

    // Event listener for course unenrol
    $(document).on('click', '.event-course-unenrol', function () {
        // Store reference to course id
        const courseId = $(this).closest('div.col.mb-2.px-2').data().id;
        // POST to unenrol endpoint
        $.ajax({
            type: 'POST',
            url: '../php/enrol/_userUnenrol.php',
            data: {
                id: courseId
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
                    // On success set user unenrol status on appropriate course, and decrease enrolled count, re-render courses.
                    displaySuccessToast(response.message);
                    upcomingResponse[upcomingResponse.findIndex(course => course.id === courseId)].isUserEnrolled = 0;
                    upcomingResponse[upcomingResponse.findIndex(course => course.id === courseId)].enrolled--;
                    renderUpcoming();
                } else {
                    displayErrorToastStandard(response.message);
                }
            },
            error: function () {
                displayErrorToastStandard('Something went wrong while handling this request');
            }
        });
    });

    // Store past course related details
    let pastCoursesResponse = [];
    let userIsAdminPastCourses = false;
    let paginateIndexPastCourses = 1;
    let isSearchingPastCourses = false;

    // Function variable that fetches past courses
    let getPast = () => $.ajax({
        type: 'GET',
        url: '../php/course/_getPast.php',
        data: {
            pageIndex: paginateIndexPastCourses,
            searchPastMinDate: $('#searchPastMinDate').val(),
            searchPastMaxDate: $('#searchPastMaxDate').val(),
            searchPastTitle: $('#searchPastTitle').val()
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // On success store response in pastCoursesResponse and render past courses
                pastCoursesResponse = response.data;
                userIsAdminPastCourses = response.isAdmin;
                renderPast(userIsAdminPastCourses);
            } else {
                displayErrorToastStandard(response.message);
            }
        },
        error: function () {
            displayErrorToastStandard('Something went wrong fetching data for this page, please reload the page to try again.');
        }
    });

    // Renders past courses into the past course container
    function renderPast(isAdmin) {
        if (pastCoursesResponse.length < 1) {
            // Does not hide search inputs if the reason for no courses being found is due to user searching
            if (!isSearchingPastCourses) {
                $('#pastSearchForm').hide();
            }
            $('#past nav').hide();
            $('#past div.alert.alert-info').remove();
            $('#past').append('<div class="alert alert-info">No past courses found.</div>');
            $('#past div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
        } else {
            // Ensures that previous alerts and courses are removed before appending new ones
            $('#past div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
            $('#past div.alert.alert-info').remove();
            $('#pastSearchForm').show();
            $('#past nav').show();
            // Store reference to past courses container
            const $pastContainer = $('#past div.row.row-cols-1.row-cols-lg-3.row-cols-md-2');
            // For every past course in the response, append to course container
            pastCoursesResponse.every((course, i) => {
                const createdDate = new Date(course.created);
                const courseDate = new Date(course.date);
                // Clone template
                let $courseTemplate = $('#templates').children('div').eq(8).clone();
                $courseTemplate.find('h5.card-title').html(course.title);
                $courseTemplate.find('p.card-text').html(course.description);

                // Set the course date in the template
                $courseTemplate.find('ul li span span').eq(1).html(courseDate.toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                }));
                // Set attendees and course created date in template
                $courseTemplate.find('ul li span span').eq(2).html(`${course.enrolled}/${course.maxAttendees}`);
                $courseTemplate.find('div.card-footer small').first().html(createdDate.toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                }));

                // If course location is empty, render link, otherwise set location
                if (course.location === null || course.location === "") {
                    let $linkTemplate = $('#templates').children('li').eq(0).clone();
                    $linkTemplate.find('small').html('Link is no longer available.')
                    $courseTemplate.find('ul li').first().replaceWith($linkTemplate);
                } else {
                    $courseTemplate.find('ul li span span').first().html(course.location);
                }

                // If user is not admin, remove footer actions
                if (!isAdmin) {
                    $courseTemplate.find('div.card-footer div i').remove();
                }

                // Attach data to template for future reference
                $courseTemplate.data(course);

                // Append to past container
                $pastContainer.append($courseTemplate);

                // Stop appending once 11th index is hit in response
                if (i >= 11) return false;
                else return true;
            });

            // Enable previous paginate button if paginate index is more than 1
            if (paginateIndexPastCourses > 1) {
                $('#past nav ul li:nth-child(1)').removeClass('disabled');
            }
            else {
                $('#past nav ul li:nth-child(1)').addClass('disabled');
            }

            // Enable next paginate button if there are more than 12 courses
            if (pastCoursesResponse.length > 12) {
                $('#past nav ul li:nth-child(2)').removeClass('disabled');
            }
            else {
                $('#past nav ul li:nth-child(2)').addClass('disabled');
            }

            $('#past nav div.rounded.border.px-2.py-1.mr-auto span').eq(1).html(paginateIndex);

            $('[data-toggle="tooltip"]').tooltip();
        }
    };

    // Event listener for past course tab button clicks
    $('#past-tab').on('shown.bs.tab', function () {
        // Gets past courses whenever past courses tab button is clicked
        getPast(userIsAdminPastCourses);
    });

    // Event listener for deletion of past courses
    $(document).on('click', '.event-delete-past-course', function () {
        // Confirm that user really wants to delete the course
        confirmDialog(`Are you sure you want to delete "${$(this).closest('div.col.mb-2.px-2').data().title}", this action cannot be undone`, 'Confirm Deletion', () => {
            // Store reference to past course and post ccourse Id to endpoint
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
                        // On success remove course from response and re-render previous page if response length is now less than 1, otherwise just re-fetch data.
                        displaySuccessToast(response.message);
                        $parentCourse.fadeOut(500, () => {
                            pastCoursesResponse = pastCoursesResponse.filter(course => course.id !== $parentCourse.data().id);
                            if (pastCoursesResponse.length < 1 && paginateIndexPastCourses > 1) {
                                paginateIndexPastCourses--;
                                replaceCoursesWithLoading('#past');
                                getPast();
                            }
                            else {
                                replaceCoursesWithLoading('#past');
                                getPast();
                            }
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

    // Event listener for non-disabled past courses previous pagination button
    $(document).on('click', '#past nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        // Decrements by one, and then refetches data
        paginateIndexPastCourses--;
        replaceCoursesWithLoading('#past');
        getPast();
        tabContent.scrollIntoView();
    });

    // Event listener for non-disabled past courses next pagination button
    $(document).on('click', '#past nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        // Increments by one and then refetches data.
        paginateIndexPastCourses++;
        replaceCoursesWithLoading('#past');
        getPast();
        tabContent.scrollIntoView();
    });

    // Listen for enters on past search form inputs
    $(document).on('enter', '#pastSearchForm input', function () {
        $('#pastSearchForm').trigger('submit');
    });

    // Listen for clicks on past search icon
    $(document).on('click', '#pastSearchIcon', function () {
        $('#pastSearchForm').trigger('submit');
    });

    // Listen for submits of past search form
    $(document).on('submit', '#pastSearchForm', function (e) {
        // Refetch data on search
        e.preventDefault();
        paginateIndexPastCourses = 1;
        isSearchingPastCourses = true;
        replaceCoursesWithLoading('#past');
        getPast();
        tabContent.scrollIntoView();
    });

    // Listen for input on past search form inputs
    $(document).on('input', '#pastSearchForm input', function () {
        // If inputs are now emtpy, hide clear button, otherwise show
        if ($('#searchPastMinDate').val() === "" && $('#searchPastMaxDate').val() === "" && $('#searchPastTitle').val() === "") {
            $('.tooltip').tooltip('hide');
            $('#clearPastSearchIcon').hide();
        }
        else {
            $('#clearPastSearchIcon').show();
        }
    });

    // Listen for clicks on clear past search button
    $(document).on('click', '#clearPastSearchIcon', function () {
        // Reset search form and re-fetch data
        $('#pastSearchForm').trigger('reset');
        $('.tooltip').tooltip('hide');
        $('#clearPastSearchIcon').hide();
        formPastSearchValidator.resetForm();
        paginateIndexPastCourses = 1;
        isSearchingPastCourses = false;
        replaceCoursesWithLoading('#past');
        getPast();
    });

    // Listen for clicks of view past attendees class
    $(document).on('click', '.event-view-past-attendees', function () {
        // Set data on view attendees modal and show modal.
        $('.tooltip').tooltip('hide');
        const currentCourseData = $(this).closest('div.col.mb-2.px-2').data();
        $('#ModalViewAttendees').data(currentCourseData);
        $('#ModalViewAttendees').modal('show');
    });

    // On view attendees modal being shown, clear any previous data and refetch
    $('#ModalViewAttendees').on('shown.bs.modal', function () {
        $('#ModalViewAttendees div.modal-body ul').first().empty();
        $('#ModalViewAttendees div.modal-body div.alert.alert-info').remove();
        $('#ModalViewAttendees .enrolled-staff-placeholder').show();
        paginateIndexEnrolledStaff = 1;
        getEnrolledStaff($(this).data().id, '#ModalViewAttendees', false);
    });

    // On click of non-disabled previous button in view attendees modal
    $(document).on('click', '#ModalViewAttendees nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        // Decrement paginate index by one and refetch data
        paginateIndexEnrolledStaff--;
        $('#ModalViewAttendees div.modal-body ul').first().empty();
        $('#ModalViewAttendees .enrolled-staff-placeholder').show();
        getEnrolledStaff($('#ModalViewAttendees').data().id, '#ModalViewAttendees', false);
    });

    // On click of non-disabled next button in view attendees modal
    $(document).on('click', '#ModalViewAttendees nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        // Decrement paginate index by one and refetch data
        paginateIndexEnrolledStaff++;
        $('#ModalViewAttendees div.modal-body ul').first().empty();
        $('#ModalViewAttendees .enrolled-staff-placeholder').show();
        getEnrolledStaff($('#ModalViewAttendees').data().id, '#ModalViewAttendees', false);
    });
});