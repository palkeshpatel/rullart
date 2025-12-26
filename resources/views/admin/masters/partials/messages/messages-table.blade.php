<table class="table table-bordered table-striped table-hover" id="messagesTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="messageid">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="message">
                    Message(EN) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="messageAR">
                    Message(AR) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="displayorder">
                    Display Order <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($messages as $message)
            <tr>
                <td>{{ $message->messageid }}</td>
                <td>{{ Str::limit($message->message, 50) }}</td>
                <td>{{ Str::limit($message->messageAR, 50) }}</td>
                <td>{{ $message->displayorder }}</td>
                <td>{{ $message->isactive ? 'Yes' : 'No' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-message-btn" 
                           title="View" data-message-id="{{ $message->messageid }}">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-message-btn" 
                           title="Edit" data-message-id="{{ $message->messageid }}">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-message-btn" 
                           title="Delete" data-message-id="{{ $message->messageid }}" 
                           data-message-text="{{ Str::limit($message->message, 30) }}">
                            <i class="ti ti-trash fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No messages found</td>
            </tr>
        @endforelse
    </tbody>
</table>

