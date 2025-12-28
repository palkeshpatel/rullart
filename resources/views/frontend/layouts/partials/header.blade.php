<header class="ra-header">
    <div class="container-fluid">
        <div class="row">
            <a id="menuToggle" class="navbar-toggle">
                <span class="sr-only">{{ __('Toggle navigation') }}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <h1>
                <a href="{{ route('home', ['locale' => $locale]) }}">
                    <img alt="Rullart" src="{{ $resourceUrl }}images/rullart-logo.svg"/>
                </a>
            </h1>
            <nav role="navigation">
                <div class="mobile-top-nav">
                    <ul>
                        <li>
                            @if($locale == 'ar')
                                <a href="{{ route('language.switch', ['locale' => 'en']) }}" class="lang-link">English</a>
                            @else
                                <a href="{{ route('language.switch', ['locale' => 'ar']) }}" class="lang-link">عربي</a>
                            @endif
                        </li>
                    </ul>
                    <a id="closeMenu" class="close-menu" href="javascript:;">
                        <svg class="icon icon-close">
                            <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
                        </svg>
                    </a>
                </div>
                <ul class="list-unstyled clearfix">
                    {{-- By Category --}}
                    <li class="has-sub {{ request()->is('*/category/*') ? 'active' : '' }}">
                        <a href="javascript:;">
                            {{ __('by category') }}
                            <svg class="icon icon-arrow-down">
                                <use xlink:href="/static/images/symbol-defs.svg#icon-arrow-down"></use>
                            </svg>
                        </a>
                        <div class="mega-dropdown">
                            <div class="container">
                                <ul class="list-unstyled clearfix">
                                    @foreach($categoryMenu as $category)
                                        <li class="col-md-6">
                                            <a href="{{ route('category.index', ['locale' => $locale, 'category' => $category->categorycode]) }}">
                                                {{ $locale == 'ar' ? $category->categoryAR : $category->category }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li class="col-md-6">
                                        <a href="{{ route('category.all', ['locale' => $locale]) }}">{{ __('All') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>

                    {{-- By Occasion (Hidden) --}}
                    <li class="has-sub hidden {{ request()->is('*/occassion/*') ? 'active' : '' }}">
                        <a href="javascript:;">
                            {{ __('by occassion') }}
                            <svg class="icon icon-arrow-down">
                                <use xlink:href="/static/images/symbol-defs.svg#icon-arrow-down"></use>
                            </svg>
                        </a>
                        <div class="mega-dropdown">
                            <div class="container">
                                <ul class="list-unstyled clearfix">
                                    @foreach($occassionMenu as $occassion)
                                        <li class="col-md-6">
                                            <a href="{{ route('category.occassion', ['locale' => $locale, 'occassion' => $occassion->occassioncode]) }}">
                                                {{ $locale == 'ar' ? $occassion->occassionAR : $occassion->occassion }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </li>

                    {{-- Gifting (if available) --}}
                    @if(isset($giftPackageMenu) && $giftPackageMenu->count() > 0)
                        <li class="has-sub {{ request()->is('*/gift-package*') ? 'active' : '' }}">
                            <a href="javascript:;">
                                {{ strtolower(__('gifting')) }}
                                <svg class="icon icon-arrow-down">
                                    <use xlink:href="/static/images/symbol-defs.svg#icon-arrow-down"></use>
                                </svg>
                            </a>
                            <div class="mega-dropdown">
                                <div class="container">
                                    <ul class="list-unstyled clearfix">
                                        @foreach($giftPackageMenu as $gift)
                                            <li class="col-md-6">
                                                <a href="{{ route('category.index', ['locale' => $locale, 'category' => $gift->categorycode]) }}">
                                                    {{ $locale == 'ar' ? $gift->categoryAR : $gift->category }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </li>
                    @endif

                    {{-- What's New --}}
                    <li class="{{ request()->is('*/whatsnew*') ? 'active' : '' }}">
                        <a href="{{ route('whatsnew', ['locale' => $locale]) }}">{{ __('whats new') }}</a>
                    </li>

                    {{-- Sale --}}
                    <li class="{{ request()->is('*/sale*') ? 'active' : '' }}">
                        <a href="{{ route('sale', ['locale' => $locale]) }}" class="sale">{{ __('sale') }}</a>
                    </li>

                    {{-- About Us --}}
                    <li class="{{ request()->is('*/about-us*') ? 'active' : '' }}">
                        <a href="{{ route('about', ['locale' => $locale]) }}">{{ __('about us') }}</a>
                    </li>

                    {{-- Contact --}}
                    <li class="{{ request()->is('*/contact*') ? 'active' : '' }}">
                        <a href="{{ route('contact', ['locale' => $locale]) }}">{{ __('contact') }}</a>
                    </li>

                    {{-- User Menu --}}
                    @if(session('logged_in'))
                        <li class="user-name">
                            {{ __('Welcome') }} {{ session('firstname') }}, 
                            <a href="{{ route('login.logout', ['locale' => $locale]) }}">{{ __('Logout') }}</a>
                        </li>
                    @else
                        <li class="user-name hidden" id="liWelcome">
                            {{ __('Welcome') }} <span id="spanFirstName"></span>, 
                            <a href="{{ route('login.logout', ['locale' => $locale]) }}">{{ __('Logout') }}</a>
                        </li>
                    @endif
                </ul>
            </nav>

            {{-- Cart Menu --}}
            <div id="ra-cartMenu" class="ra-cart-menu">
                <ul class="list-unstyled clearfix">
                    @php
                        $showlogin = session('logged_in') ? 'style="display:none;"' : '';
                        $showpostlogin = session('logged_in') ? '' : 'style="display:none;"';
                    @endphp

                    {{-- Login Link --}}
                    <li {!! $showlogin !!}>
                        <a id="ra-login" class="user-link" href="{{ route('frontend.login', ['locale' => $locale]) }}">
                            <svg class="icon icon-person">
                                <use xlink:href="/static/images/symbol-defs.svg#icon-person"></use>
                            </svg>
                        </a>
                    </li>

                    {{-- Post Login Menu --}}
                    <li {!! $showpostlogin !!}>
                        <a id="ra-post-login" class="user-link dropdown-toggle" href="javascript:;" id="dropdownAccount" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <svg class="icon icon-person">
                                <use xlink:href="/static/images/symbol-defs.svg#icon-person"></use>
                            </svg>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownAccount">
                            <li>
                                <a href="{{ route('myorders', ['locale' => $locale]) }}">{{ __('my orders') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('myprofile', ['locale' => $locale]) }}">{{ __('my profile') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('myaddresses', ['locale' => $locale]) }}">{{ __('my addresses') }}</a>
                            </li>
                            <li>
                                <a id="logout" href="{{ route('login.logout', ['locale' => $locale]) }}">{{ __('logout') }}</a>
                            </li>
                        </ul>
                    </li>

                    {{-- Search --}}
                    <li>
                        <a id="ra-search" class="search-link" href="javascript:;">
                            <svg class="icon icon-search">
                                <use xlink:href="/static/images/symbol-defs.svg#icon-search"></use>
                            </svg>
                        </a>
                    </li>

                    {{-- Wishlist --}}
                    <li class="liwishlist">
                        <a id="ra-wishlist" class="wishlist-link" href="{{ route('wishlist', ['locale' => $locale]) }}">
                            <svg class="icon icon-heart">
                                <use xlink:href="/static/images/symbol-defs.svg#icon-heart"></use>
                            </svg>
                            @if($wishlistCount > 0)
                                <span class="badge">{{ $wishlistCount }}</span>
                            @endif
                        </a>
                    </li>

                    {{-- Shopping Cart --}}
                    <li class="licart">
                        @if(request()->is('*/payment*'))
                            <a class="cart-link" href="javascript:;">
                                <svg class="icon icon-bag">
                                    <use xlink:href="/static/images/symbol-defs.svg#icon-bag"></use>
                                </svg>
                                @if($cartCount > 0)
                                    <span class="badge">{{ $cartCount }}</span>
                                @endif
                            </a>
                        @else
                            <a id="ra-cart" class="cart-link" href="{{ route('cart.index', ['locale' => $locale]) }}">
                                <svg class="icon icon-bag">
                                    <use xlink:href="/static/images/symbol-defs.svg#icon-bag"></use>
                                </svg>
                                @if($cartCount > 0)
                                    <span class="badge">{{ $cartCount }}</span>
                                @endif
                            </a>
                        @endif
                    </li>

                    {{-- Currency Selector --}}
                    <li id="dCurrency">
                        <div id="currency" class="currency">
                            <div id="country-currency" class="country-currency">
                                @php
                                    $defaultCountry = strtolower(config('app.default_country', 'Kuwait'));
                                @endphp
                                <img src="{{ $imageUrl }}images/{{ $defaultCountry }}.png" alt="{{ __('Kuwait') }}">
                            </div>
                            <div class="country-currency-selection">
                                <div>
                                    <label>{{ __('Select Currency') }}</label>
                                    <ul class="list-unstyled">
                                        @foreach($currencyArr as $currency)
                                            <li>
                                                <a href="{{ route('currency.switch', ['code' => $currency->currencycode]) }}">
                                                    {{ $currency->currencycode }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

