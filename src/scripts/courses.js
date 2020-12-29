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
    $('#clearPastSearchIcon').hide();

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
            $('#courses div.alert.alert-info').remove();
            $('#courses').append('<div class="alert alert-info">No upcoming courses found.</div>');
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
        } else {
            $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
            $('#courses div.alert.alert-info').remove();
            $('#upcomingSearchForm').show();
            $('#courses nav').show();
            const $upcomingContainer = $('#courses div.row.row-cols-1.row-cols-lg-3.row-cols-md-2');
            upcomingResponse.every((course, i) => {
                const createdDate = new Date(course.created);
                const courseDate = new Date(course.date);
                const todaysDatePlus7 = new Date().addDays(7);
                const isThisWeek = courseDate < todaysDatePlus7;
                const isToday = courseDate.getDate() === new Date().getDate();
                const isFullyBooked = course.maxAttendees <= course.enrolled;
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

                if (isFullyBooked) {
                    $courseTemplate.find('div.card-body').children('div').first().append('<span class="badge badge-danger">Fully Booked</span>')
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

    function replaceCoursesWithLoading(selector) {
        $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).empty();
        for (let i = 0; i < 3; i++) {
            $(`${selector} div.row.row-cols-1.row-cols-lg-3.row-cols-md-2`).append($('#templates').children('div').eq(6).clone());
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
                            if (upcomingResponse.length < 1 && paginateIndex > 1) {
                                paginateIndex--;
                                replaceCoursesWithLoading('#courses');
                                getUpcoming();
                            }
                            else {
                                renderUpcoming(userIsAdmin);
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
        $('#ModalEditCourse').data(currentCourseData);
        $('#ModalEditCourse').modal('show');
    });

    let paginateIndexEnrolledStaff = 1;
    let enrolledStaffResponse = [];
    let getEnrolledStaff = (courseId) => $.ajax({
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
                renderEnrolledStaff();
            } else {
                displayErrorToastStandard(response.message);
            }
        },
        error: function () {
            displayErrorToastStandard('Something went wrong fetching enrolled users, please reload the page to try again.');
        }
    });

    $('#ModalEditCourse').on('shown.bs.modal', function () {
        $('#ModalEditCourse div.modal-body ul').first().empty();
        $('#ModalEditCourse div.modal-body div.alert.alert-info').remove();
        $('#ModalEditCourse .enrolled-staff-placeholder').show();
        paginateIndexEnrolledStaff = 1;
        getEnrolledStaff($(this).data().id);
    });

    $('#ModalEditCourse').on('hidden.bs.modal', function () {
        let foundIndex = upcomingResponse.findIndex(course => course.id === $(this).data().id);
        upcomingResponse[foundIndex].enrolled = $(this).data().enrolled;
        renderUpcoming(userIsAdmin);
    });

    $(document).on('click', '#ModalEditCourse nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        paginateIndexEnrolledStaff--;
        $('#ModalEditCourse div.modal-body ul').first().empty();
        $('#ModalEditCourse .enrolled-staff-placeholder').show();
        getEnrolledStaff($('#ModalEditCourse').data().id);
    });

    $(document).on('click', '#ModalEditCourse nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        paginateIndexEnrolledStaff++;
        $('#ModalEditCourse div.modal-body ul').first().empty();
        $('#ModalEditCourse .enrolled-staff-placeholder').show();
        getEnrolledStaff($('#ModalEditCourse').data().id);
    });

    $(document).on('click', '.event-user-remove-from-course', function () {
        const $listItem = $(this).closest('li.list-group-item');
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
                    $('#ModalEditCourse').data().enrolled--;
                    displaySuccessToast(response.message);
                    $listItem.fadeOut(500, () => {
                        enrolledStaffResponse = enrolledStaffResponse.filter(staff => staff.id !== $listItem.data().id);
                        if (enrolledStaffResponse.length < 1 && paginateIndexEnrolledStaff > 1) {
                            paginateIndexEnrolledStaff--;
                        }
                        $('#ModalEditCourse div.modal-body ul').first().empty();
                        $('#ModalEditCourse .enrolled-staff-placeholder').show();
                        getEnrolledStaff($('#ModalEditCourse').data().id);
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

    function renderEnrolledStaff() {
        if (paginateIndexEnrolledStaff > 1) {
            $('#ModalEditCourse nav ul li:nth-child(1)').removeClass('disabled');
        }
        else {
            $('#ModalEditCourse nav ul li:nth-child(1)').addClass('disabled');
        }

        if (enrolledStaffResponse.length > 5) {
            $('#ModalEditCourse nav ul li:nth-child(2)').removeClass('disabled');
        }
        else {
            $('#ModalEditCourse nav ul li:nth-child(2)').addClass('disabled');
        }

        if (enrolledStaffResponse.length < 1) {
            $('#ModalEditCourse div.modal-body div.alert.alert-info').remove();
            $('#ModalEditCourse div.modal-body').children('div').first().append('<div class="alert alert-info">No staff are currently enrolled.</div>');
            $('#ModalEditCourse div.modal-body nav').hide();
            $('#ModalEditCourse .enrolled-staff-placeholder').hide();
        }
        else {
            $('#ModalEditCourse .enrolled-staff-placeholder').hide();
            $('#ModalEditCourse div.modal-body nav').show();
            $('#ModalEditCourse div.modal-body div.alert.alert-info').remove();
            enrolledStaffResponse.every((staff, i) => {
                let $enrolledStaffTemplate = $('#templates').children('li').eq(1).clone();
                $enrolledStaffTemplate.find('p strong').html(`${staff.firstName} ${staff.lastName}`);
                $enrolledStaffTemplate.find('p em').html(staff.jobTitle);
                $enrolledStaffTemplate.find('p').eq(1).html(staff.email); ''
                $enrolledStaffTemplate.find('small').html(`Enrolled: ${new Date(staff.dateCreated).toLocaleString([], {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    hour12: true
                })}`);
                $enrolledStaffTemplate.data(staff);
                $('#ModalEditCourse div.modal-body ul').first().append($enrolledStaffTemplate);
                if (i >= 4) return false;
                else return true;
            });

        }
    }

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
        replaceCoursesWithLoading('#courses');
        getUpcoming();
        tabContent.scrollIntoView();
    });

    $(document).on('click', '#courses nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        paginateIndex++;
        replaceCoursesWithLoading('#courses');
        getUpcoming();
        tabContent.scrollIntoView();
    });

    $(document).on('enter', '#upcomingSearchForm input', function () {
        $('#upcomingSearchForm').trigger('submit');
    });

    $(document).on('click', '#upcomingSearchIcon', function () {
        $('#upcomingSearchForm').trigger('submit');
    });

    $(document).on('submit', '#upcomingSearchForm', function (e) {
        e.preventDefault();
        paginateIndex = 1;
        isSearching = true;
        replaceCoursesWithLoading('#courses');
        getUpcoming();
        tabContent.scrollIntoView();
    });

    $(document).on('input', '#upcomingSearchForm input', function () {
        if ($('#searchMinDate').val() === "" && $('#searchMaxDate').val() === "" && $('#searchTitle').val() === "") {
            $('.tooltip').tooltip('hide');
            $('#clearSearchIcon').hide();
        }
        else {
            $('#clearSearchIcon').show();
        }
    });

    $(document).on('click', '#clearSearchIcon', function () {
        $('#upcomingSearchForm').trigger('reset');
        $('.tooltip').tooltip('hide');
        $('#clearSearchIcon').hide();
        formUpcomingSearchValidator.resetForm();
        paginateIndex = 1;
        isSearching = false;
        replaceCoursesWithLoading('#courses');
        getUpcoming();
    });

    $(document).on('click', '.event-course-enrol:not(.disabled)', function () {
        const courseId = $(this).closest('div.col.mb-2.px-2').data().id;
        $.ajax({
            type: 'POST',
            url: '../php/enrol/_userEnrol.php',
            data: {
                id: courseId
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
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

    $(document).on('click', '.event-course-unenrol', function () {
        const courseId = $(this).closest('div.col.mb-2.px-2').data().id;
        $.ajax({
            type: 'POST',
            url: '../php/enrol/_userUnenrol.php',
            data: {
                id: courseId
            },
            dataType: 'json',
            success: function (response) {
                if (response.success == true) {
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

    let pastCoursesResponse = [];
    let userIsAdminPastCourses = false;
    let paginateIndexPastCourses = 1;
    let isSearchingPastCourses = false;

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

    function renderPast(isAdmin) {
        if (pastCoursesResponse.length < 1) {
            if (!isSearchingPastCourses) {
                $('#pastSearchForm').hide();
            }
            $('#past nav').hide();
            $('#past div.alert.alert-info').remove();
            $('#past').append('<div class="alert alert-info">No upcoming courses found.</div>');
            $('#past div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
        } else {
            $('#past div.row.row-cols-1.row-cols-lg-3.row-cols-md-2').empty();
            $('#past div.alert.alert-info').remove();
            $('#pastSearchForm').show();
            $('#past nav').show();
            const $pastContainer = $('#past div.row.row-cols-1.row-cols-lg-3.row-cols-md-2');
            pastCoursesResponse.every((course, i) => {
                const createdDate = new Date(course.created);
                const courseDate = new Date(course.date);
                let $courseTemplate = $('#templates').children('div').eq(8).clone();
                $courseTemplate.find('h5.card-title').html(course.title);
                $courseTemplate.find('p.card-text').html(course.description);

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
                    $linkTemplate.find('small').html('Link is no longer available.')
                    $courseTemplate.find('ul li').first().replaceWith($linkTemplate);
                } else {
                    $courseTemplate.find('ul li span span').first().html(course.location);
                }

                if (!isAdmin) {
                    $courseTemplate.find('div.card-footer div i').remove();
                }

                $courseTemplate.data(course);

                $pastContainer.append($courseTemplate);

                if (i >= 11) return false;
                else return true;
            });

            if (paginateIndexPastCourses > 1) {
                $('#past nav ul li:nth-child(1)').removeClass('disabled');
            }
            else {
                $('#past nav ul li:nth-child(1)').addClass('disabled');
            }

            if (pastCoursesResponse.length > 12) {
                $('#past nav ul li:nth-child(2)').removeClass('disabled');
            }
            else {
                $('#past nav ul li:nth-child(2)').addClass('disabled');
            }

            $('[data-toggle="tooltip"]').tooltip();
        }
    };

    $('#past-tab').on('shown.bs.tab', function () {
        getPast(userIsAdminPastCourses);
    });

    $(document).on('click', '.event-delete-past-course', function() {
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
                            pastCoursesResponse = pastCoursesResponse.filter(course => course.id !== $parentCourse.data().id);
                            if (pastCoursesResponse.length < 1 && paginateIndexPastCourses > 1) {
                                paginateIndexPastCourses--;
                                replaceCoursesWithLoading('#past');
                                getPast();
                            }
                            else {
                                renderPast(userIsAdminPastCourses);
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

    $(document).on('click', '#past nav ul.pagination li:not(.disabled):nth-child(1) button', function () {
        paginateIndexPastCourses--;
        replaceCoursesWithLoading('#past');
        getPast();
        tabContent.scrollIntoView();
    });

    $(document).on('click', '#past nav ul.pagination li:not(.disabled):nth-child(2) button', function () {
        paginateIndexPastCourses++;
        replaceCoursesWithLoading('#past');
        getPast();
        tabContent.scrollIntoView();
    });

    $(document).on('enter', '#pastSearchForm input', function () {
        $('#pastSearchForm').trigger('submit');
    });

    $(document).on('click', '#pastSearchIcon', function () {
        $('#pastSearchForm').trigger('submit');
    });

    $(document).on('submit', '#pastSearchForm', function (e) {
        e.preventDefault();
        paginateIndexPastCourses = 1;
        isSearchingPastCourses = true;
        replaceCoursesWithLoading('#past');
        getPast();
        tabContent.scrollIntoView();
    });

    $(document).on('input', '#pastSearchForm input', function () {
        if ($('#searchPastMinDate').val() === "" && $('#searchPastMaxDate').val() === "" && $('#searchPastTitle').val() === "") {
            $('.tooltip').tooltip('hide');
            $('#clearPastSearchIcon').hide();
        }
        else {
            $('#clearPastSearchIcon').show();
        }
    });

    $(document).on('click', '#clearPastSearchIcon', function () {
        $('#pastSearchForm').trigger('reset');
        $('.tooltip').tooltip('hide');
        $('#pastSearchIcon').hide();
        formPastSearchValidator.resetForm();
        paginateIndexPastCourses = 1;
        isSearchingPastCourses = false;
        replaceCoursesWithLoading('#past');
        getPast();
    });
});