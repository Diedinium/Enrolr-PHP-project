<div class="enrolr-toast-container position-fixed" id="toastContainer" style="z-index: 1900;">

</div>

<div class="d-none" id="templates">
    <div class="toast enrolr-error-toast" role="alert" data-delay="8000" id="templateToastError">
        <div class="toast-header">
            <strong class="mr-auto text-danger">Error</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                <span>&times;</span>
            </button>
        </div>
        <div class="toast-body">
        </div>
    </div>

    <div class="toast enrolr-success-toast" role="alert" data-delay="6000" id="templateToastSuccess">
        <div class="toast-header">
            <strong class="mr-auto text-success">Success</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                <span>&times;</span>
            </button>
        </div>
        <div class="toast-body">
        </div>
    </div>

    <div class="toast" role="alert" data-delay="6000" id="templateToastStandard">
        <div class="toast-header">
            <strong class="mr-auto">Error</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                <span>&times;</span>
            </button>
        </div>
        <div class="toast-body">
        </div>
    </div>

    <div>
        <div class="row justify-content-end">
            <div class="col-12 d-sm-none">
                <div class="alert alert-warning">
                    <strong>Note:</strong> Since you are on a small screen, scroll the table horizontally to see actions.
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="d-flex align-items-center">
                    <input type="search" id="staffSearchBox" class="form-control form-control-sm" placeholder="Type to search">
                    <div class="pl-2">
                        <i data-toggle="tooltip" data-placement="top" title="Search" id="staffSearchIcon" class="fas fa-search fa-lg enrolr-standard-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <table id="staffTable" class="table w-100">
            <thead>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Job Title</th>
                <th></th>
            </thead>
        </table>
    </div>

    <div>
        <table>
            <tbody>
                <tr>
                    <td>Placeholder</td>
                    <td>Placeholder</td>
                    <td>Placeholder</td>
                    <td>Placeholder</td>
                    <td class="text-right enrolr-datatable-actions-min-width">
                        <i data-userId="0" class="fas fa-user-edit enrolr-standard-icon mr-2 event-user-edit"></i>
                        <i data-userId="0" class="fas fa-user-times enrolr-danger-icon mr-2 event-user-delete-staff"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col mb-2 px-2">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-0">title</h5>
                <div class="pb-2">
                    <span class="badge badge-info">hours</span>
                    <span class="badge badge-success">This week</span>
                </div>
                <p class="card-text enrolr-line-sep">description</p>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <span><i data-toggle="tooltip" data-placement="top" title="Location" class="fa fa-map-marked text-muted pr-2"></i><span></span></span>
                </li>
                <li class="list-group-item d-flex">
                    <span class="mr-auto"><i data-toggle="tooltip" data-placement="top" title="Course date/time" class="fa fa-calendar-day text-muted pr-2"></i><span></span></span>
                    <span><i data-toggle="tooltip" data-placement="top" title="Attendees" class="fa fa-user text-muted pr-2"></i><span></span></span>
                </li>
            </ul>
            <div class="card-footer">
                <div class="d-flex align-items-center">
                    <small class="text-muted mr-auto">created</small>
                    <div class="enrolr-actions-min-width text-right">
                        <i data-toggle="tooltip" data-placement="top" title="Edit" class="fas fa-edit fa-lg enrolr-standard-icon event-edit-course"></i>
                        <i data-toggle="tooltip" data-placement="top" title="Delete" class="fas fa-trash fa-lg enrolr-danger-icon event-delete-course pl-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col mb-2 px-2">
        <div class="ph-item mb-2">
            <div class="ph-col-12">
                <div class="ph-row">
                    <div class="ph-col-8 big"></div>
                    <div class="ph-col-4 empty"></div>
                    <div class="ph-col-4"></div>
                    <div class="ph-col-8 empty"></div>
                    <div class="ph-col-6"></div>
                    <div class="ph-col-6 empty"></div>
                    <div class="ph-col-12"></div>
                </div>
                <div class="ph-row mt-4">
                    <div class="ph-col-6"></div>
                    <div class="ph-col-6 empty"></div>
                    <div class="ph-col-4"></div>
                    <div class="ph-col-4 empty"></div>
                    <div class="ph-col-4"></div>
                </div>
                <div class="ph-row mt-4">
                    <div class="ph-col-12 big"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="enrolled-staff-placeholder">
        <div class="ph-item mb-0 px-0 py-2">
            <div class="ph-col-12">
                <div class="ph-row mb-0">
                    <div class="ph-col-4 big"></div>
                    <div class="ph-col-8 empty"></div>
                    <div class="ph-col-8"></div>
                    <div class="ph-col-4 emtpy"></div>
                    <div class="ph-col-6"></div>
                    <div class="ph-col-6 empty"></div>
                </div>
            </div>
        </div>
        <div class="ph-item mb-0 px-0 py-2">
            <div class="ph-col-12">
                <div class="ph-row mb-0">
                    <div class="ph-col-4 big"></div>
                    <div class="ph-col-8 empty"></div>
                    <div class="ph-col-8"></div>
                    <div class="ph-col-4 emtpy"></div>
                    <div class="ph-col-6"></div>
                    <div class="ph-col-6 empty"></div>
                </div>
            </div>
        </div>
        <div class="ph-item mb-0 px-0 py-2">
            <div class="ph-col-12">
                <div class="ph-row mb-0">
                    <div class="ph-col-4 big"></div>
                    <div class="ph-col-8 empty"></div>
                    <div class="ph-col-8"></div>
                    <div class="ph-col-4 emtpy"></div>
                    <div class="ph-col-6"></div>
                    <div class="ph-col-6 empty"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col mb-2 px-2">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-0">title</h5>
                <p class="card-text enrolr-line-sep">description</p>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <span><i data-toggle="tooltip" data-placement="top" title="Location" class="fa fa-map-marked text-muted pr-2"></i><span></span></span>
                </li>
                <li class="list-group-item d-flex">
                    <span class="mr-auto"><i data-toggle="tooltip" data-placement="top" title="Course date/time" class="fa fa-calendar-day text-muted pr-2"></i><span></span></span>
                    <span><i data-toggle="tooltip" data-placement="top" title="Attendees" class="fa fa-user text-muted pr-2"></i><span></span></span>
                </li>
            </ul>
            <div class="card-footer">
                <div class="d-flex align-items-center">
                    <small class="text-muted mr-auto">created</small>
                    <div class="enrolr-actions-min-width text-right">
                        <i data-toggle="tooltip" data-placement="top" title="View attendees" class="fas fa-users fa-lg enrolr-standard-icon event-view-past-attendees"></i>
                        <i data-toggle="tooltip" data-placement="top" title="Delete" class="fas fa-trash fa-lg enrolr-danger-icon event-delete-past-course pl-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col mb-2 px-2">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-0 enrolr-title">title</h5>
                <div class="pb-2">
                    <span class="badge badge-info enrolr-badge-hours">hours</span>
                    <span class="badge badge-success enrolr-badge-this-week">This week</span>
                    <span class="badge badge-warning enrolr-badge-today">Today!</span>
                    <span class="badge badge-danger enrolr-badge-fully-booked">Fully booked</span>
                </div>
                <p class="card-text enrolr-line-sep enrolr-description">description</p>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item ">
                    <span><i data-toggle="tooltip" data-placement="top" title="Location" class="fa fa-map-marked text-muted pr-2"></i><span class="enrolr-location"></span></span>
                </li>
                <li class="list-group-item d-flex align-items-center">
                    <i data-toggle="tooltip" data-placement="top" title="Link" class="fa fa-globe text-muted pr-2"></i>
                    <div>
                        <p class="mb-0 text-break">
                            <a class="enrolr-link" href="#">Link to course</a>
                        </p>
                    </div>
                </li>
                <li class="list-group-item d-flex">
                    <span class="mr-auto"><i data-toggle="tooltip" data-placement="top" title="Course date/time" class="fa fa-calendar-day text-muted pr-2"></i><span class="enrolr-date"></span></span>
                    <span><i data-toggle="tooltip" data-placement="top" title="Attendees" class="fa fa-user text-muted pr-2"></i><span class="enrolr-attendees"></span></span>
                </li>
            </ul>
            <div class="card-footer">
                <div class="d-flex align-items-center">
                    <small class="text-muted mr-auto enrolr-created-date">created</small>
                    <div class="enrolr-actions-min-width text-right">
                        <button type="button" class="btn btn-secondary text-white event-course-unenrol">Unenrol</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <li class="list-group-item d-flex align-items-center">
        <i data-toggle="tooltip" data-placement="top" title="Link" class="fa fa-globe text-muted pr-2"></i>
        <div>
            <p class="mb-0 text-break">Online Course</p>
            <small class="text-muted">Link will be provided once you enrol.</small>
        </div>
    </li>

    <li class="list-group-item">
        <div class="d-flex align-items-center">
            <div class="pr-3 mr-auto">
                <p class="mb-0"><strong>Jane Doe</strong>, <em>Job Role</em></p>
                <p class="mb-0">janedoe@test.com</p>
                <small class="text-muted">Enrolled: dateHere</small>
            </div>
            <button type="submit" class="btn btn-danger event-user-remove-from-course">Remove</button>
        </div>
    </li>
</div>

<div class="loader d-none"></div>
<div class="overlay d-none"></div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="confirmTitle">Confirm</h4>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button id="confirmBtnYes" type="button" class="btn enrolr-brand-colour-bg text-white">Yes</button>
                <button id="confirmBtnNo" type="button" class="btn btn-secondary">No</button>
            </div>
        </div>
    </div>
</div>