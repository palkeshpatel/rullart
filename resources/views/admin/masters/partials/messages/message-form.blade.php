<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">
                    {{ $message ? 'Edit Gift Message' : 'Add Gift Message' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="messageForm" method="POST" action="{{ $message ? route('admin.messages.update', $message->messageid) : route('admin.messages.store') }}" novalidate>
                @csrf
                @if($message)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Message(EN) <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="3" required>{{ old('message', $message ? $message->message : '') }}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message(AR)</label>
                        <textarea name="messageAR" class="form-control" rows="3">{{ old('messageAR', $message ? $message->messageAR : '') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" min="0" name="displayorder" class="form-control" value="{{ old('displayorder', $message ? $message->displayorder : 0) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order(AR)</label>
                                <input type="number" min="0" name="displayorderAR" class="form-control" value="{{ old('displayorderAR', $message ? $message->displayorderAR : 0) }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $message ? $message->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $message ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

