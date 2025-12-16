/**
 * Admin AJAX Helper
 * Centralized AJAX utilities for admin panel operations
 */

const AdminAjax = {
    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    },

    /**
     * Default headers for AJAX requests
     */
    getDefaultHeaders() {
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrfToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
    },

    /**
     * Show alert message
     */
    showAlert(type, message, container = null) {
        const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : `alert-${type}`;
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        if (container) {
            const el = typeof container === 'string' ? document.querySelector(container) : container;
            if (el) {
                el.insertAdjacentHTML('beforeend', alertHtml);
                setTimeout(() => {
                    const alert = el.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }
        } else {
            const defaultContainer = document.querySelector('.content-page') || document.body;
            defaultContainer.insertAdjacentHTML('afterbegin', alertHtml);
            setTimeout(() => {
                const alert = defaultContainer.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    },

    /**
     * Show success message
     */
    showSuccess(message, container = null) {
        this.showAlert('success', message, container);
    },

    /**
     * Show error message
     */
    showError(message, container = null) {
        this.showAlert('error', message, container);
    },

    /**
     * Make AJAX request
     */
    async request(url, method = 'GET', data = null, options = {}) {
        const config = {
            method: method,
            headers: {
                ...this.getDefaultHeaders(),
                ...(options.headers || {})
            }
        };

        // Handle form data
        if (data instanceof FormData) {
            delete config.headers['Content-Type'];
            config.body = data;
        } else if (data) {
            if (method === 'GET') {
                const params = new URLSearchParams(data);
                url += '?' + params.toString();
            } else {
                config.body = JSON.stringify(data);
            }
        }

        try {
            const response = await fetch(url, config);
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const json = await response.json();
                if (!response.ok) {
                    throw json;
                }
                return json;
            }
            
            const text = await response.text();
            return {
                success: response.ok,
                status: response.status,
                data: text,
                redirect: response.redirected ? response.url : null
            };
        } catch (error) {
            console.error('AJAX Error:', error);
            throw error;
        }
    },

    /**
     * GET request
     */
    get(url, data = null, options = {}) {
        return this.request(url, 'GET', data, options);
    },

    /**
     * POST request
     */
    post(url, data = null, options = {}) {
        return this.request(url, 'POST', data, options);
    },

    /**
     * PUT request
     */
    put(url, data = null, options = {}) {
        return this.request(url, 'PUT', data, options);
    },

    /**
     * DELETE request
     */
    delete(url, data = null, options = {}) {
        return this.request(url, 'DELETE', data, options);
    },

    /**
     * Load table data via AJAX
     */
    loadTable(url, containerSelector, options = {}) {
        const container = typeof containerSelector === 'string' 
            ? document.querySelector(containerSelector) 
            : containerSelector;

        if (!container) {
            console.error('Container not found:', containerSelector);
            return;
        }

        // Show loading state
        if (options.showLoading !== false) {
            container.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        }

        // Get current filters from form if exists
        const form = container.closest('.card')?.querySelector('form') || document.querySelector('form[data-table-filters]');
        let params = {};
        
        if (form) {
            const formData = new FormData(form);
            formData.forEach((value, key) => {
                if (value) params[key] = value;
            });
        }

        // Merge with provided params
        params = { ...params, ...options.params };
        
        // Preserve per_page if exists in form
        if (form) {
            const perPageSelect = form.querySelector('#perPageSelect');
            if (perPageSelect && perPageSelect.value) {
                params.per_page = perPageSelect.value;
            }
        }

        return this.get(url, params)
            .then(response => {
                if (response.html) {
                    container.innerHTML = response.html;
                } else if (response.table) {
                    container.innerHTML = response.table;
                } else {
                    container.innerHTML = '<div class="alert alert-warning">No data available</div>';
                }
                
                // Update pagination if provided separately
                if (response.pagination) {
                    const paginationContainer = container.closest('.card-body')?.querySelector('.pagination-container');
                    if (paginationContainer) {
                        paginationContainer.innerHTML = response.pagination;
                    } else {
                        // If pagination container not found, try to find existing pagination
                        const existingPagination = container.closest('.card-body')?.querySelector('.row.mt-3');
                        if (existingPagination) {
                            existingPagination.outerHTML = response.pagination;
                        } else {
                            // Append after table container
                            container.insertAdjacentHTML('afterend', response.pagination);
                        }
                    }
                }

                // Re-initialize any scripts if needed
                if (options.onSuccess) {
                    options.onSuccess(response);
                }

                return response;
            })
            .catch(error => {
                container.innerHTML = '<div class="alert alert-danger">Error loading data. Please try again.</div>';
                if (options.onError) {
                    options.onError(error);
                }
                throw error;
            });
    },

    /**
     * Initialize data table with AJAX
     */
    initDataTable(options = {}) {
        const {
            tableSelector = '.data-table',
            searchSelector = '[data-search]',
            filterSelector = '[data-filter]',
            paginationSelector = '[data-pagination]',
            loadUrl = null,
            containerSelector = '.table-container'
        } = options;

        const container = typeof containerSelector === 'string' 
            ? document.querySelector(containerSelector) 
            : containerSelector;

        if (!container) return;

        // Search handler
        const searchInput = document.querySelector(searchSelector);
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    AdminAjax.loadTable(loadUrl || window.location.href, container, {
                        params: { search: this.value }
                    });
                }, 500);
            });
        }

        // Filter handlers
        const filterInputs = document.querySelectorAll(filterSelector);
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                AdminAjax.loadTable(loadUrl || window.location.href, container, {
                    params: { [this.name]: this.value }
                });
            });
        });

        // Pagination handler - attach directly to pagination container for better control
        const paginationContainer = container.closest('.card-body')?.querySelector('.pagination-container') || 
                                   document.querySelector('.pagination-container');
        
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                const paginationLink = e.target.closest('.pagination a');
                if (paginationLink && paginationLink.href && !paginationLink.classList.contains('disabled')) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get current form data to preserve filters
                    const form = document.querySelector('form[data-table-filters]');
                    const formData = new FormData(form || {});
                    
                    // Get per_page value from select
                    const perPageSelect = document.getElementById('perPageSelect');
                    if (perPageSelect && perPageSelect.value) {
                        formData.set('per_page', perPageSelect.value);
                    }
                    
                    // Parse URL to get page number
                    try {
                        const url = new URL(paginationLink.href);
                        const page = url.searchParams.get('page');
                        if (page) {
                            formData.set('page', page);
                        }
                    } catch (err) {
                        // If URL parsing fails, try to extract page from href
                        const match = paginationLink.href.match(/[?&]page=(\d+)/);
                        if (match) {
                            formData.set('page', match[1]);
                        }
                    }
                    
                    // Build params object
                    const params = {};
                    formData.forEach((value, key) => {
                        if (value) params[key] = value;
                    });
                    
                    AdminAjax.loadTable(loadUrl || window.location.href, container, {
                        params: params,
                        onSuccess: options.onSuccess
                    });
                }
            });
        }
        
        // Store onSuccess callback for later use
        if (options.onSuccess) {
            container.dataset.onSuccess = 'true';
        }
    }
};

// Make it globally available
window.AdminAjax = AdminAjax;

