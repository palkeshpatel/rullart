<!-- PDF Loading Modal - Common Component -->
<div class="modal fade" id="pdfLoadingModal" tabindex="-1" aria-labelledby="pdfLoadingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mt-3 mb-1">Generating PDF...</h5>
                <p class="text-muted mb-0">Please wait while we prepare your report.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Common PDF Loader Script - Initialize after Bootstrap is loaded
    (function() {
        function initPdfLoader() {
            const pdfButtons = document.querySelectorAll('.pdf-export-btn');
            
            if (pdfButtons.length === 0) {
                return;
            }
            
            const loadingModalElement = document.getElementById('pdfLoadingModal');
            if (!loadingModalElement) {
                console.error('PDF Loading Modal not found');
                return;
            }
            
            // Check if Bootstrap is available
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap is not loaded');
                return;
            }
            
            const loadingModal = new bootstrap.Modal(loadingModalElement);
            
            pdfButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const pdfUrl = this.href;
                    
                    // Show loading modal immediately
                    loadingModal.show();
                    
                    // Create a temporary anchor element and click it to trigger download
                    const link = document.createElement('a');
                    link.href = pdfUrl;
                    link.download = ''; // Let server set filename via Content-Disposition
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    
                    // Trigger click
                    link.click();
                    
                    // Clean up
                    setTimeout(function() {
                        document.body.removeChild(link);
                        loadingModal.hide();
                    }, 3000);
                });
            });
        }
        
        // Initialize when DOM is ready and Bootstrap is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // Wait a bit to ensure Bootstrap is loaded
                setTimeout(initPdfLoader, 100);
            });
        } else {
            // DOM is already ready
            setTimeout(initPdfLoader, 100);
        }
    })();
</script>

