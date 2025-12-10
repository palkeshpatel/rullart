@extends('layouts.base', ['title' => 'Admin Login'])

@section('content')

<div class="auth-box overflow-hidden align-items-center d-flex">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-md-6 col-sm-8">
                <div class="auth-brand text-center mb-4">
                    <a href="{{ route('admin.dashboard') }}" class="logo-dark">
                        <img src="/images/logo-black.png" alt="dark logo" height="32">
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="logo-light">
                        <img src="/images/logo.png" alt="logo" height="32">
                    </a>
                    <h4 class="fw-bold mt-3">Admin Login</h4>
                    <p class="text-muted w-lg-75 mx-auto">Enter your admin credentials to access the admin panel.</p>
                </div>

                <div class="card p-4 rounded-4">
                    <form method="POST" action="{{ route('admin.login.store') }}">

                        @csrf

                        <div class="mb-3">
                            <label for="userName" class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="userName" name="user" value="{{ old('user') }}" required autofocus>
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
                            <label for="userPassword" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="userPassword" name="password" required>
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
                                <input class="form-check-input form-check-input-light fs-14" type="checkbox" id="rememberMe" name="remember">
                                <label class="form-check-label" for="rememberMe">Keep me signed in</label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold py-2">Login</button>
                        </div>
                    </form>
                </div>

                <p class="text-center text-muted mt-4 mb-0">
                    © <script>document.write(new Date().getFullYear())</script> Admin Dashboard
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@endsection

