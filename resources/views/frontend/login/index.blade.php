<div class="overlay-section">
    <div class="overlay-title">
        <h2>
            <svg class="icon icon-person">
                <use xlink:href="/static/images/symbol-defs.svg#icon-person"></use>
            </svg>{{ __('Login') }}
        </h2>
    </div>

    <form id="form-login">
        <div class="form-group">
            <label for="email" class="control-label">{{ __('Email Address') }}</label>
            <input type="email" class="form-control required email" id="email" name="email">
        </div>
        <div class="form-group">
            <label for="password" class="control-label">{{ __('Password') }}</label>
            <input type="password" class="form-control required" id="password" name="password">
        </div>
        <div class="forgot-pass">
            <a id="ra-forgot" href="{{ url('/' . $locale . '/login/forgot') }}">{{ __('Forgot Password?') }}</a>
        </div>
        <div class="alert alert-danger print-error-msg" style="display:none"></div>
        <button type="submit" class="btn btn-primary" id="btnLogin">{{ __('Login') }}</button>
    </form>
    <div class="social-login">
        <ul class="list-unstyled clearfix">
            <li class="fb">
                <a href="{{ $authUrl ?? '#' }}">
                    <svg class="icon icon-facebook">
                        <use xlink:href="/static/images/symbol-defs.svg#icon-facebook"></use>
                    </svg>{{ __('Login with Facebook') }}
                </a>
            </li>
            <li class="gp">
                <a href="{{ url('/' . $locale . '/login/google_login') }}">
                    <svg class="icon icon-gplus">
                        <use xlink:href="/static/images/symbol-defs.svg#icon-gplus"></use>
                    </svg>{{ __('Login with Google') }}
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="overlay-section">
    <div class="overlay-title">
        <h2>
            <svg class="icon icon-person">
                <use xlink:href="/static/images/symbol-defs.svg#icon-person"></use>
            </svg>{{ __("Don't have an account?") }}
        </h2>
        <a id="ra-register" href="{{ url('/' . $locale . '/login/register') }}" class="btn btn-secondary">{{ __('REGISTER NOW') }}</a>
    </div>
</div>
@if($cartItemCount > 0)
<div class="overlay-section">
    <div class="overlay-title">
        <h2>
            <svg class="icon icon-person">
                <use xlink:href="/static/images/symbol-defs.svg#icon-person"></use>
            </svg>{{ __('Login as Guest') }}
        </h2>
    </div>

    <form id="form-login-guest">
        <div class="form-group hidden">
            <label for="email-guest" class="control-label">{{ __('Email Address') }}</label>
            <input type="email" class="form-control email" id="email-guest" name="email-guest">
        </div>
        <button type="submit" class="btn btn-primary" id="btnLoginGuest">{{ __('Login') }}</button>
    </form>
</div>
@endif
<script src="{{ $resourceUrl ?? url('/resources/') . '/' }}scripts/login.js?v=0.9"></script>

