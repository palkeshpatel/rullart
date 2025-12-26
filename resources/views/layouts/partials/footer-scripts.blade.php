<!-- App js -->
@vite('resources/js/app.js')

<!-- Admin AJAX Helper -->
<script src="{{ asset('js/admin-ajax.js') }}"></script>

<!-- Validation CSS -->
<style>
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .invalid-feedback.d-block {
        display: block !important;
    }

    .is-invalid {
        border-color: #dc3545;
    }
</style>

@yield('scripts')
