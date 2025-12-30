@extends('frontend.layouts.app')

@section('content')
<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('My Profile') }}</li>
        </ol>
        <h1><span><span class="before-icon"></span>{{ __('My Profile') }}<span class="after-icon"></span></span></h1>
    </div>
    <div class="inside-content">
        <div class="container">
            <div class="my-account">
                <h2>{{ __('Edit Profile') }}</h2>
                <form id="formProfile">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="control-label">{{ __('First Name') }}</label>
                        <input class="form-control required" id="firstname" name="firstname" value="{{ Session::get('firstname') }}">
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="control-label">{{ __('Last Name') }}</label>
                        <input class="form-control required" id="lastname" name="lastname" value="{{ Session::get('lastname') }}">
                    </div>
                    <div class="form-group">
                        <label for="emailAddress" class="control-label">{{ __('Email Address') }}</label>
                        <input type="email" class="form-control required email" id="emailAddress" name="emailAddress" disabled value="{{ Session::get('email') }}">
                    </div>
                    <button type="button" class="btn btn-primary" id="btnUpdateProfile">{{ __('Update Profile') }}</button>
                    <div class="alert alert-danger print-error-msg" style="display:none"></div>
                    <div class="alert alert-success" style="display:none"><strong>{{ __('Success!') }}</strong> {{ __('Profile updated successfully') }}</div>
                </form>
            </div>
            @if(Session::get('login_type') == 'Register')
            <div class="my-account">
                <h2>{{ __('Change Password') }}</h2>
                <form id="formChangePassword">
                    @csrf
                    <div class="form-group">
                        <label for="currentPassword" class="control-label">{{ __('Current Password') }}</label>
                        <input type="password" class="form-control required" id="currentPassword" name="currentPassword">
                        <span class="help-block"></span>
                    </div>
                    <div class="form-group">
                        <label for="newPassword" class="control-label">{{ __('New Password') }}</label>
                        <input type="password" class="form-control required" id="newPassword" name="newPassword">
                        <span class="help-block"></span>
                    </div>
                    <div class="form-group">
                        <label for="confirmNewPassword" class="control-label">{{ __('Confirm New Password') }}</label>
                        <input type="password" class="form-control required" equalto="#newPassword" id="confirmNewPassword" name="confirmNewPassword">
                        <span class="help-block"></span>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnUpdatePassword">{{ __('Update Password') }}</button>
                    <div class="alert alert-danger print-error-msg" style="display:none"></div>
                    <div class="alert alert-success" style="display:none"><strong>{{ __('Success!') }}</strong> {{ __('Password changed successfully') }}</div>
                </form>
            </div>
            @endif
        </div>
    </div>
</main>
<script src="{{ $resourceUrl ?? url('/resources/') . '/' }}scripts/myprofile.js?v=0.9"></script>
@endsection

