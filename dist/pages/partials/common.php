<div class="enrolr-toast-container position-fixed" id="toastContainer" style="z-index: 2000;">

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

    <div class="toast enrolr-success-toast" role="alert" data-delay="8000" id="templateToastSuccess">
        <div class="toast-header">
            <strong class="mr-auto text-success">Success</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                <span>&times;</span>
            </button>
        </div>
        <div class="toast-body">
        </div>
    </div>

    <div class="toast" role="alert" data-delay="8000" id="templateToastStandard">
        <div class="toast-header">
            <strong class="mr-auto">Error</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                <span>&times;</span>
            </button>
        </div>
        <div class="toast-body">
        </div>
    </div>
</div>

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
                <button id="confirmBtnNo" type="button" class="btn btn-danger">No</button>
            </div>
        </div>
    </div>
</div>