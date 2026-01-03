@extends('layouts.base', ['title' => 'Admin Login'])

@section('content')

    <div class="auth-box overflow-hidden align-items-center d-flex">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-md-6 col-sm-8">
                    <div class="auth-brand text-center mb-4">
                        <a href="{{ route('admin.dashboard') }}" class="logo-dark">
                            <img src="http://127.0.0.1:8000/resources/images/logo-footer.svg" alt="dark logo" height="32">
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="logo-light">
                            <img src="http://127.0.0.1:8000/resources/images/logo-footer.svg" alt="logo" height="32">
                        </a>
                        <h4 class="fw-bold mt-3">Admin Login</h4>

                    </div>

                    <div class="card p-4 rounded-4">
                        <form method="POST" action="{{ route('admin.login.store') }}">

                            @csrf

                            <div class="mb-3">
                                <label for="userName" class="form-label">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="userName" name="user"
                                        value="{{ old('user') }}" required autofocus>
                                </div>
                                @if ($errors->get('user'))
                                    <ul class="list-unstyled ps-0 mt-1">
                                        @foreach ((array) $errors->get('user') as $message)
                                            <li class="text-danger mb-1">{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="userPassword" class="form-label">Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="userPassword" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                                        style="border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" viewBox="0 0 16 16" style="display: inline-block;">
                                            <path
                                                d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                                            <path
                                                d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                                        </svg>
                                        <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" width="16"
                                            height="16" fill="currentColor" viewBox="0 0 16 16" style="display: none;">
                                            <path
                                                d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5a7.028 7.028 0 0 1-2.79-.588l-.77.772A7.028 7.028 0 0 0 8 13.5c2.12 0 3.879-1.168 5.168-2.457a13.134 13.134 0 0 0 1.191-1.805z" />
                                            <path
                                                d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z" />
                                            <path
                                                d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5a7.028 7.028 0 0 0 2.79-.588l-.77-.771A5.944 5.944 0 0 1 8 11.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                                            <path
                                                d="M8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8l-.195.288a7.028 7.028 0 0 1-2.79.588l.77.771A5.944 5.944 0 0 0 8 11.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8 13.134 13.134 0 0 1 3.35 5.47z" />
                                            <path fill-rule="evenodd"
                                                d="M14.146 1.146a.5.5 0 0 1 .708 0l1.5 1.5a.5.5 0 0 1-.708.708l-1.5-1.5a.5.5 0 0 1 0-.708zm-13 13a.5.5 0 0 1 .708 0l1.5 1.5a.5.5 0 0 1-.708.708l-1.5-1.5a.5.5 0 0 1 0-.708z" />
                                        </svg>
                                    </button>
                                </div>
                                @if ($errors->get('password'))
                                    <ul class="list-unstyled ps-0 mt-1">
                                        @foreach ((array) $errors->get('password') as $message)
                                            <li class="text-danger mb-1">{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input form-check-input-light fs-14" type="checkbox"
                                        id="rememberMe" name="remember">
                                    <label class="form-check-label" for="rememberMe">Keep me signed in</label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-semibold py-2">Login</button>
                            </div>
                        </form>
                    </div>

                    <p class="text-center text-muted mt-4 mb-0">
                        ©
                        <script>
                            document.write(new Date().getFullYear())
                        </script> Admin Dashboard
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('userPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'text') {
                        eyeIcon.style.display = 'none';
                        eyeSlashIcon.style.display = 'inline-block';
                    } else {
                        eyeIcon.style.display = 'inline-block';
                        eyeSlashIcon.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endsection
