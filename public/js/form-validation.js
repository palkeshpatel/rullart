/**
 * jQuery Validation Configuration for Master Forms
 * Applies validation to all master forms with consistent rules and messages
 */

(function($) {
    'use strict';

    // Wait for jQuery and jQuery Validation to be available
    function initValidation() {
        if (typeof $ === 'undefined' || typeof $.fn.validate === 'undefined') {
            setTimeout(initValidation, 100);
            return;
        }

        // Common validation rules and messages
        const commonRules = {
            // Colors, Sizes
            'filtervalue': {
                required: true,
                maxlength: 255
            },
            'filtervalueAR': {
                required: true,
                maxlength: 255
            },
            // Areas
            'areaname': {
                required: true,
                maxlength: 255
            },
            'areanameAR': {
                required: true,
                maxlength: 255
            },
            // Countries
            'countryname': {
                required: true,
                maxlength: 50
            },
            'countrynameAR': {
                required: true,
                maxlength: 50
            },
            // Gift Messages
            'message': {
                required: true
            },
            'messageAR': {
                required: true
            },
            // Areas - Country
            'fkcountryid': {
                required: true
            }
        };

        const commonMessages = {
            // Colors, Sizes
            'filtervalue': {
                required: 'Please enter a value in English',
                maxlength: 'Maximum 255 characters allowed'
            },
            'filtervalueAR': {
                required: 'Please enter a value in Arabic',
                maxlength: 'Maximum 255 characters allowed'
            },
            // Areas
            'areaname': {
                required: 'Please enter area name in English',
                maxlength: 'Maximum 255 characters allowed'
            },
            'areanameAR': {
                required: 'Please enter area name in Arabic',
                maxlength: 'Maximum 255 characters allowed'
            },
            // Countries
            'countryname': {
                required: 'Please enter country name in English',
                maxlength: 'Maximum 50 characters allowed'
            },
            'countrynameAR': {
                required: 'Please enter country name in Arabic',
                maxlength: 'Maximum 50 characters allowed'
            },
            // Gift Messages
            'message': {
                required: 'Please enter message in English'
            },
            'messageAR': {
                required: 'Please enter message in Arabic'
            },
            // Areas - Country
            'fkcountryid': {
                required: 'Please select a country'
            }
        };

        // Initialize validation for forms when they are added to DOM
        function setupFormValidation(formId) {
            const $form = $(formId);
            if ($form.length && !$form.data('validator')) {
                $form.validate({
                    rules: commonRules,
                    messages: commonMessages,
                    errorElement: 'div',
                    errorClass: 'invalid-feedback',
                    validClass: 'is-valid',
                    highlight: function(element, errorClass, validClass) {
                        $(element).addClass('is-invalid').removeClass(validClass);
                        $(element).closest('.mb-3').find('.invalid-feedback').html($(element).attr('data-msg') || $(element).next('.invalid-feedback').html() || '');
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        $(element).removeClass('is-invalid').addClass(validClass);
                    },
                    errorPlacement: function(error, element) {
                        const errorElement = element.closest('.mb-3').find('.invalid-feedback');
                        if (errorElement.length) {
                            errorElement.html(error.text());
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {
                        // Don't prevent default - let the form submit handler do its work
                        // This is just for validation display
                        return true;
                    }
                });
            }
        }

        // Setup validation for existing forms
        setupFormValidation('#colorForm');
        setupFormValidation('#sizeForm');
        setupFormValidation('#areaForm');
        setupFormValidation('#countryForm');
        setupFormValidation('#couponCodeForm');
        setupFormValidation('#discountForm');
        setupFormValidation('#messageForm');
        setupFormValidation('#courierCompanyForm');

        // Watch for dynamically added forms (modals)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is a form
                        if (node.tagName === 'FORM' && node.id) {
                            setupFormValidation('#' + node.id);
                        }
                        // Check if the added node contains a form
                        const forms = node.querySelectorAll && node.querySelectorAll('form[id]');
                        if (forms) {
                            forms.forEach(function(form) {
                                setupFormValidation('#' + form.id);
                            });
                        }
                    }
                });
            });
        });

        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initValidation);
    } else {
        initValidation();
    }

})(typeof jQuery !== 'undefined' ? jQuery : window.$);

