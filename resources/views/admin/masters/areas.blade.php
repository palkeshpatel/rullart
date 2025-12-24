@extends('layouts.vertical', ['title' => 'Areas List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Areas List'])

    <div class="row">
        <div class="col-12">
            <!-- Areas Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Areas List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-area-btn">
                        <i class="ti ti-plus me-1"></i> Add Area
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search areas..." value="{{ request('search') }}">
                                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="mb-0 me-2">Show
                                        <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                            id="perPageSelect">
                                            @php
                                                $currentPerPage = request('per_page', 25);
                                            @endphp
                                            <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        @include('admin.masters.partials.area.areas-table', ['areas' => $areas])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $areas])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="areaModalContainer"></div>
    <div id="areaViewModalContainer"></div>

    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                AdminAjax.initDataTable({
                    tableSelector: '#areasTable',
                    searchSelector: '[data-search]',
                    filterSelector: '[data-filter]',
                    paginationSelector: '.pagination a',
                    loadUrl: '{{ route('admin.areas') }}',
                    containerSelector: '.table-container',
                    onSuccess: function(response) {
                        if (response.pagination) {
                            document.querySelector('.pagination-container').innerHTML = response.pagination;
                        }
                    }
                });

                // Handle per page change
                document.getElementById('perPageSelect')?.addEventListener('change', function() {
                    const params = new URLSearchParams(window.location.search);
                    params.set('per_page', this.value);
                    params.delete('page');
                    
                    AdminAjax.loadTable('{{ route('admin.areas') }}', document.querySelector('.table-container'), {
                        params: Object.fromEntries(params),
                        onSuccess: function(response) {
                            if (response.pagination) {
                                const paginationContainer = document.querySelector('.pagination-container');
                                if (paginationContainer) {
                                    paginationContainer.innerHTML = response.pagination;
                                }
                            }
                        }
                    });
                });

                // Initialize Area modals
                initAreaModals();
            });

            function initAreaModals() {
                document.addEventListener('click', function(e) {
                    const addBtn = e.target.closest('.add-area-btn');
                    if (addBtn) {
                        e.preventDefault();
                        openAreaFormModal();
                    }

                    const viewBtn = e.target.closest('.view-area-btn');
                    if (viewBtn) {
                        e.preventDefault();
                        const areaId = viewBtn.dataset.areaId;
                        openAreaViewModal(areaId);
                    }

                    const editBtn = e.target.closest('.edit-area-btn');
                    if (editBtn) {
                        e.preventDefault();
                        const areaId = editBtn.dataset.areaId;
                        openAreaFormModal(areaId);
                    }

                    const deleteBtn = e.target.closest('.delete-area-btn');
                    if (deleteBtn) {
                        e.preventDefault();
                        const areaId = deleteBtn.dataset.areaId;
                        const areaName = deleteBtn.dataset.areaName;
                        confirmDeleteArea(areaId, areaName);
                    }
                });
            }

            function openAreaFormModal(areaId = null) {
                const existingModal = document.getElementById('areaModal');
                if (existingModal) {
                    const existingBsModal = bootstrap.Modal.getInstance(existingModal);
                    if (existingBsModal) existingBsModal.dispose();
                }
                
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) {
                    existingBackdrop.remove();
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }

                const modalContainer = document.getElementById('areaModalContainer');
                const url = areaId 
                    ? '{{ route("admin.areas.edit", ":id") }}'.replace(':id', areaId) 
                    : '{{ route("admin.areas.create") }}';
                
                modalContainer.innerHTML = '';
                modalContainer.innerHTML = '<div class="modal fade" id="areaModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div></div></div></div></div>';
                
                const loadingModalEl = document.getElementById('areaModal');
                const loadingModal = new bootstrap.Modal(loadingModalEl, { backdrop: 'static', keyboard: false });
                loadingModal.show();

                AdminAjax.get(url)
                    .then(response => {
                        if (response.html) {
                            loadingModal.hide();
                            loadingModal.dispose();
                            modalContainer.innerHTML = '';
                            modalContainer.innerHTML = response.html;
                            
                            const modal = document.getElementById('areaModal');
                            const bsModal = new bootstrap.Modal(modal);
                            
                            modal.addEventListener('hidden.bs.modal', function() {
                                bsModal.dispose();
                                modalContainer.innerHTML = '';
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) backdrop.remove();
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, { once: true });
                            
                            bsModal.show();
                            
                            const form = document.getElementById('areaForm');
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    submitAreaForm(form, areaId, bsModal);
                                });
                            }
                        }
                    })
                    .catch(error => {
                        loadingModal.hide();
                        loadingModal.dispose();
                        modalContainer.innerHTML = '';
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                        console.error('Error loading area form:', error);
                        AdminAjax.showError('Failed to load area form.');
                    });
            }

            function openAreaViewModal(areaId) {
                const existingModal = document.getElementById('areaViewModal');
                if (existingModal) {
                    const existingBsModal = bootstrap.Modal.getInstance(existingModal);
                    if (existingBsModal) existingBsModal.dispose();
                }
                
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) {
                    existingBackdrop.remove();
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }

                const modalContainer = document.getElementById('areaViewModalContainer');
                modalContainer.innerHTML = '';
                modalContainer.innerHTML = '<div class="modal fade" id="areaViewModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div></div></div></div></div>';
                
                const loadingModalEl = document.getElementById('areaViewModal');
                const loadingModal = new bootstrap.Modal(loadingModalEl, { backdrop: 'static', keyboard: false });
                loadingModal.show();

                AdminAjax.get('{{ route("admin.areas.show", ":id") }}'.replace(':id', areaId))
                    .then(response => {
                        if (response.html) {
                            loadingModal.hide();
                            loadingModal.dispose();
                            modalContainer.innerHTML = '';
                            modalContainer.innerHTML = response.html;
                            
                            const modal = document.getElementById('areaViewModal');
                            const bsModal = new bootstrap.Modal(modal);
                            
                            modal.addEventListener('hidden.bs.modal', function() {
                                bsModal.dispose();
                                modalContainer.innerHTML = '';
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) backdrop.remove();
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, { once: true });
                            
                            bsModal.show();
                            
                            const editBtn = modal.querySelector('.edit-area-btn');
                            if (editBtn) {
                                editBtn.addEventListener('click', function() {
                                    const editAreaId = editBtn.dataset.areaId;
                                    modal.addEventListener('hidden.bs.modal', function() {
                                        bsModal.dispose();
                                        modalContainer.innerHTML = '';
                                        const backdrop = document.querySelector('.modal-backdrop');
                                        if (backdrop) backdrop.remove();
                                        document.body.classList.remove('modal-open');
                                        document.body.style.overflow = '';
                                        document.body.style.paddingRight = '';
                                        setTimeout(function() {
                                            openAreaFormModal(editAreaId);
                                        }, 100);
                                    }, { once: true });
                                    bsModal.hide();
                                });
                            }
                        }
                    })
                    .catch(error => {
                        loadingModal.hide();
                        loadingModal.dispose();
                        modalContainer.innerHTML = '';
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                        console.error('Error loading area:', error);
                        AdminAjax.showError('Failed to load area details.');
                    });
            }

            function submitAreaForm(form, areaId, modal) {
                const formData = new FormData(form);
                const url = form.action;
                const method = form.querySelector('input[name="_method"]')?.value || 'POST';

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                AdminAjax.request(url, method, formData)
                    .then(response => {
                        AdminAjax.showSuccess(response.message || 'Area saved successfully');
                        modal.hide();
                        AdminAjax.loadTable('{{ route("admin.areas") }}', document.querySelector('.table-container'), {
                            params: {},
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    document.querySelector('.pagination-container').innerHTML = response.pagination;
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error saving area:', error);
                        AdminAjax.showError(error.message || 'Failed to save area.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            }

            function confirmDeleteArea(areaId, areaName) {
                const existingModal = document.getElementById('deleteConfirmModal');
                if (existingModal) {
                    const existingBsModal = bootstrap.Modal.getInstance(existingModal);
                    if (existingBsModal) existingBsModal.dispose();
                    existingModal.remove();
                }

                const modalHtml = `
                    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete "<strong id="deleteItemName"></strong>"?</p>
                                    <p class="text-danger mb-0">This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                document.getElementById('deleteItemName').textContent = areaName;
                const modalEl = document.getElementById('deleteConfirmModal');
                const modal = new bootstrap.Modal(modalEl);

                modalEl.addEventListener('hidden.bs.modal', function() {
                    modal.dispose();
                    modalEl.remove();
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, { once: true });

                const deleteBtn = document.getElementById('confirmDeleteBtn');
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = 'Delete';

                deleteBtn.onclick = function() {
                    deleteArea(areaId, modal, deleteBtn);
                };

                modal.show();
            }

            function deleteArea(areaId, modal, deleteBtn) {
                const originalText = deleteBtn.innerHTML;
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

                AdminAjax.request('{{ route("admin.areas.destroy", ":id") }}'.replace(':id', areaId), 'DELETE')
                    .then(response => {
                        AdminAjax.showSuccess(response.message || 'Area deleted successfully');
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = originalText;
                        modal.hide();
                        AdminAjax.loadTable('{{ route("admin.areas") }}', document.querySelector('.table-container'), {
                            params: {},
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    document.querySelector('.pagination-container').innerHTML = response.pagination;
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error deleting area:', error);
                        AdminAjax.showError(error.message || 'Failed to delete area.');
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = originalText;
                    });
            }
        })();
    </script>
@endsection
