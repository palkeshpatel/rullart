<div class="modal fade" id="messageViewModal" tabindex="-1" aria-labelledby="messageViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageViewModalLabel">Gift Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Message ID:</th>
                                <td>{{ $message->messageid }}</td>
                            </tr>
                            <tr>
                                <th>Message(EN):</th>
                                <td>{{ $message->message }}</td>
                            </tr>
                            <tr>
                                <th>Message(AR):</th>
                                <td>{{ $message->messageAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Display Order:</th>
                                <td>{{ $message->displayorder ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>Display Order(AR):</th>
                                <td>{{ $message->displayorderAR ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($message->isactive)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-message-btn" data-message-id="{{ $message->messageid }}">
                    <i class="ti ti-edit me-1"></i> Edit Message
                </button>
            </div>
        </div>
    </div>
</div>

