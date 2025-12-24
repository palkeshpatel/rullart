@extends('layouts.vertical', ['title' => 'Colors List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Colors List'])

    <div class="row">
        <div class="col-12">
            <!-- Colors Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Colors List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-color-btn">
                        <i class="ti ti-plus me-1"></i> Add Color
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search colors..." value="{{ request('search') }}">
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
                        @include('admin.masters.partials.colors-table', ['colors' => $colors])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $colors])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="colorModalContainer"></div>
    <div id="colorViewModalContainer"></div>

    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                AdminAjax.initDataTable({
                    tableSelector: '#colorsTable',
                    searchSelector: '[data-search]',
                    filterSelector: '[data-filter]',
                    paginationSelector: '.pagination a',
                    loadUrl: '{{ route('admin.colors') }}',
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
                    params.delete('page'); // Reset to page 1
                    
                    AdminAjax.loadTable('{{ route('admin.colors') }}', document.querySelector('.table-container'), {
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

                // Initialize Color modals
                initColorModals();
            });

            function initColorModals() {
                // Add Color button
                document.addEventListener('click', function(e) {
                    const addBtn = e.target.closest('.add-color-btn');
                    if (addBtn) {
                        e.preventDefault();
                        openColorFormModal();
                    }

                    // View Color button
                    const viewBtn = e.target.closest('.view-color-btn');
                    if (viewBtn) {
                        e.preventDefault();
                        const colorId = viewBtn.dataset.colorId;
                        openColorViewModal(colorId);
                    }

                    // Edit Color button
                    const editBtn = e.target.closest('.edit-color-btn');
                    if (editBtn) {
                        e.preventDefault();
                        const colorId = editBtn.dataset.colorId;
                        openColorFormModal(colorId);
                    }

                    // Delete Color button
                    const deleteBtn = e.target.closest('.delete-color-btn');
                    if (deleteBtn) {
                        e.preventDefault();
                        const colorId = deleteBtn.dataset.colorId;
                        const colorName = deleteBtn.dataset.colorName;
                        confirmDeleteColor(colorId, colorName);
                    }
                });
            }

            function openColorFormModal(colorId = null) {
                // Close any existing modals first
                const existingModal = document.getElementById('colorModal');
                if (existingModal) {
                    const existingBsModal = bootstrap.Modal.getInstance(existingModal);
                    if (existingBsModal) {
                        existingBsModal.dispose();
                    }
                }
                
                // Remove any existing backdrop
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) {
                    existingBackdrop.remove();
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }

                const modalContainer = document.getElementById('colorModalContainer');
                const url = colorId 
                    ? '{{ route("admin.colors.edit", ":id") }}'.replace(':id', colorId) 
                    : '{{ route("admin.colors.create") }}';
                
                // Clear container first
                modalContainer.innerHTML = '';
                
                // Show loading state inline
                modalContainer.innerHTML = '<div class="modal fade" id="colorModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div></div></div></div></div>';
                
                const loadingModalEl = document.getElementById('colorModal');
                const loadingModal = new bootstrap.Modal(loadingModalEl, { backdrop: 'static', keyboard: false });
                loadingModal.show();

                AdminAjax.get(url)
                    .then(response => {
                        if (response.html) {
                            // Dispose loading modal properly
                            loadingModal.hide();
                            loadingModal.dispose();
                            modalContainer.innerHTML = '';
                            
                            // Add new modal HTML
                            modalContainer.innerHTML = response.html;
                            
                            // Get and show the actual modal
                            const modal = document.getElementById('colorModal');
                            const bsModal = new bootstrap.Modal(modal);
                            
                            // Handle cleanup on close
                            modal.addEventListener('hidden.bs.modal', function() {
                                bsModal.dispose();
                                modalContainer.innerHTML = '';
                                // Ensure backdrop is removed
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) {
                                    backdrop.remove();
                                }
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, { once: true });
                            
                            bsModal.show();
                            
                            // Handle form submission
                            const form = document.getElementById('colorForm');
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    submitColorForm(form, colorId, bsModal);
                                });
                            }
                        }
                    })
                    .catch(error => {
                        loadingModal.hide();
                        loadingModal.dispose();
                        modalContainer.innerHTML = '';
                        // Clean up backdrop
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                        console.error('Error loading color form:', error);
                        AdminAjax.showError('Failed to load color form.');
                    });
            }

            function openColorViewModal(colorId) {
                // Close any existing modals first
                const existingModal = document.getElementById('colorViewModal');
                if (existingModal) {
                    const existingBsModal = bootstrap.Modal.getInstance(existingModal);
                    if (existingBsModal) {
                        existingBsModal.dispose();
                    }
                }
                
                // Remove any existing backdrop
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) {
                    existingBackdrop.remove();
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }

                const modalContainer = document.getElementById('colorViewModalContainer');
                
                // Clear container first
                modalContainer.innerHTML = '';
                
                // Show loading state
                modalContainer.innerHTML = '<div class="modal fade" id="colorViewModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div></div></div></div></div>';
                
                const loadingModalEl = document.getElementById('colorViewModal');
                const loadingModal = new bootstrap.Modal(loadingModalEl, { backdrop: 'static', keyboard: false });
                loadingModal.show();

                AdminAjax.get('{{ route("admin.colors.show", ":id") }}'.replace(':id', colorId))
                    .then(response => {
                        if (response.html) {
                            // Dispose loading modal properly
                            loadingModal.hide();
                            loadingModal.dispose();
                            modalContainer.innerHTML = '';
                            
                            // Add new modal HTML
                            modalContainer.innerHTML = response.html;
                            
                            // Get and show the actual modal
                            const modal = document.getElementById('colorViewModal');
                            const bsModal = new bootstrap.Modal(modal);
                            
                            // Handle cleanup on close
                            modal.addEventListener('hidden.bs.modal', function() {
                                bsModal.dispose();
                                modalContainer.innerHTML = '';
                                // Ensure backdrop is removed
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) {
                                    backdrop.remove();
                                }
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, { once: true });
                            
                            bsModal.show();
                            
                            // Handle edit button in view modal
                            const editBtn = modal.querySelector('.edit-color-btn');
                            if (editBtn) {
                                editBtn.addEventListener('click', function() {
                                    const editColorId = editBtn.dataset.colorId;
                                    // Wait for modal to fully close and cleanup before opening edit modal
                                    modal.addEventListener('hidden.bs.modal', function() {
                                        bsModal.dispose();
                                        modalContainer.innerHTML = '';
                                        // Clean up backdrop
                                        const backdrop = document.querySelector('.modal-backdrop');
                                        if (backdrop) {
                                            backdrop.remove();
                                        }
                                        document.body.classList.remove('modal-open');
                                        document.body.style.overflow = '';
                                        document.body.style.paddingRight = '';
                                        // Small delay to ensure cleanup is complete
                                        setTimeout(function() {
                                            openColorFormModal(editColorId);
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
                        // Clean up backdrop
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                        console.error('Error loading color:', error);
                        AdminAjax.showError('Failed to load color details.');
                    });
            }

            function submitColorForm(form, colorId, modal) {
                const formData = new FormData(form);
                const url = form.action;
                const method = form.querySelector('input[name="_method"]')?.value || 'POST';

                // Disable submit button
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                AdminAjax.request(url, method, formData)
                    .then(response => {
                        AdminAjax.showSuccess(response.message || 'Color saved successfully');
                        modal.hide();
                        
                        // Reload table after save
                        AdminAjax.loadTable('{{ route("admin.colors") }}', document.querySelector('.table-container'), {
                            params: {},
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    document.querySelector('.pagination-container').innerHTML = response.pagination;
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error saving color:', error);
                        AdminAjax.showError(error.message || 'Failed to save color.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            }

            function confirmDeleteColor(colorId, colorName) {
                // Remove existing modal if any
                const existingModal = document.getElementById('deleteConfirmModal');
                if (existingModal) {
                    const existingBsModal = bootstrap.Modal.getInstance(existingModal);
                    if (existingBsModal) {
                        existingBsModal.dispose();
                    }
                    existingModal.remove();
                }

                // Create delete confirmation modal
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

                document.getElementById('deleteItemName').textContent = colorName;
                const modalEl = document.getElementById('deleteConfirmModal');
                const modal = new bootstrap.Modal(modalEl);

                // Handle cleanup when modal is hidden
                modalEl.addEventListener('hidden.bs.modal', function() {
                    modal.dispose();
                    modalEl.remove();
                    // Ensure backdrop is removed
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, { once: true });

                // Reset button state before showing
                const deleteBtn = document.getElementById('confirmDeleteBtn');
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = 'Delete';

                deleteBtn.onclick = function() {
                    deleteColor(colorId, modal, deleteBtn);
                };

                modal.show();
            }

            function deleteColor(colorId, modal, deleteBtn) {
                const originalText = deleteBtn.innerHTML;
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

                AdminAjax.request('{{ route("admin.colors.destroy", ":id") }}'.replace(':id', colorId), 'DELETE')
                    .then(response => {
                        AdminAjax.showSuccess(response.message || 'Color deleted successfully');
                        
                        // Reset button state before hiding modal
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = originalText;
                        
                        modal.hide();
                        
                        // Reload table after delete
                        AdminAjax.loadTable('{{ route("admin.colors") }}', document.querySelector('.table-container'), {
                            params: {},
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    document.querySelector('.pagination-container').innerHTML = response.pagination;
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error deleting color:', error);
                        AdminAjax.showError(error.message || 'Failed to delete color.');
                        // Reset button state on error
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = originalText;
                    });
            }
        })();
    </script>
@endsection

