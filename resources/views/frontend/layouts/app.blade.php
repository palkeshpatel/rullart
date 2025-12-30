<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="{{ $locale }}" dir="{{ $locale == 'ar' ? 'rtl' : 'ltr' }}"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" lang="{{ $locale }}" dir="{{ $locale == 'ar' ? 'rtl' : 'ltr' }}"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" lang="{{ $locale }}" dir="{{ $locale == 'ar' ? 'rtl' : 'ltr' }}"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="{{ $locale }}" dir="{{ $locale == 'ar' ? 'rtl' : 'ltr' }}"><!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $metaTitle ?? 'Rullart - Premium Gifts & Accessories' }}</title>
    <meta name="yandex-verification" content="c2015390f47fc726" />
    <meta name="title" content="{{ strip_tags($metaTitle ?? 'Rullart') }}">
    <meta name="description" content="{{ strip_tags($metaDescription ?? '') }}">
    <meta name="keywords" content="{{ $metaKeywords ?? '' }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="info@uno-digital.com">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Open Graph Meta Tags --}}
    <meta property="og:url" content="{{ url()->current() }}" />
    @if(request()->is('*/product/*'))
        <meta property="fb:app_id" content="1774243226002431" />
        <meta property="og:type" content="product" />
        @if(isset($productData) && !empty($productData->shortdescr))
            <meta property="og:title" content="{{ $productData->shortdescr }}" />
            <meta property="og:image" content="{{ !empty($productData->photo1) ? \App\Helpers\ImageHelper::url($productData->photo1, 'detail-') : '' }}" />
            <meta property="og:description" content="{{ strip_tags($productData->longdescr ?? '') }}" />
        @endif
    @else
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Rullart.com" />
        <meta property="og:image" content="{{ \App\Helpers\ImageHelper::url('14644-IMG_3245.JPG', 'detail-') }}" />
        <meta property="og:description" content="{{ strip_tags($metaDescription ?? '') }}" />
    @endif

    <meta name="apple-itunes-app" content="app-id=1485388434" />
    <meta name="google-play-app" content="app-id=com.rullart" />

    {{-- Favicons --}}
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png" />
    <link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
    <link rel="manifest" href="/manifest.json" />
    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png" />
    <meta name="theme-color" content="#ffffff">

    {{-- Stylesheets --}}
    @if($locale == 'ar')
        <link href="https://fonts.googleapis.com/css?family=Cairo:400,700|Baloo+Bhaijaan:400" rel="stylesheet" />
        <link rel="stylesheet" href="{{ $resourceUrl }}styles/main-ar.css?v=1.21">
    @else
        <link href="https://fonts.googleapis.com/css?family=Merriweather:400,700|Montserrat:400,700" rel="stylesheet" />
        <link rel="stylesheet" href="{{ $resourceUrl }}styles/main.css?v=1.21">
    @endif
    <link rel="stylesheet" href="{{ $resourceUrl }}styles/custom.css?v=1.17">
    <script src="{{ $resourceUrl }}min/modernizr.min.js"></script>

    {{-- Google Analytics --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-117946622-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'UA-117946622-1');
    </script>

    {{-- Facebook Pixel --}}
    @if(isset($settingsArr['Facebook Pixel Code']) && !empty($settingsArr['Facebook Pixel Code']))
        @php $fbpcode = $settingsArr['Facebook Pixel Code']; @endphp
        <!-- Meta Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $fbpcode }}');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ $fbpcode }}&ev=PageView&noscript=1" />
        </noscript>
        <!-- End Meta Pixel Code -->
    @endif

    <style>
        .fill2 {
            width: 100%;
            height: 66vw;
            background-color: #EEE;
            background-position: center;
            background-size: cover;
            -o-background-size: cover;
        }
        
        @media (min-width: 992px) {
            .fill2 {
                height: 37vw;
                background-repeat: no-repeat;
            }
        }

        @media (min-width: 1800px) {
            .fill2 {
                height: calc(16*37);
                background-repeat: no-repeat;
            }
        }
        
        .apple-pay {
            display: none;
        }

        .safari .apple-pay {
            display: block;
        }
        
        div#heroSlider .carousel-inner .item {
            padding-right: 0;
        }
        div#heroSlider .carousel-inner .item img {
            width: 100%;
            max-height: 724px;
            object-fit: cover;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="page-wrapper">
        @include('frontend.layouts.partials.header')
        
        <main>
            @yield('content')
        </main>
        
        @include('frontend.layouts.partials.footer')
    </div>

    {{-- Overlay Content --}}
    <div id="overlayContent" class="overlay-content">
        <div class="overlay-header">
            <img src="{{ $resourceUrl }}images/logo-footer.svg" alt="Rullart">
            <a class="close-overlay" id="closeOverlay" href="javascript:;">
                <svg class="icon icon-close">
                    <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
                </svg>
            </a>
        </div>
        <div id="overlayBody" class="overlay-body"></div>
        <div id="overlayLoader" class="overlay-loader">
            <div class="loader">
                <img src="{{ $resourceUrl }}images/ajax-loader.gif"> {{ __('Loading...') }}
            </div>
        </div>
    </div>
    <div id="overlayBg" class="overlay-bg"></div>

    {{-- Loading --}}
    <div id="loading" class="text-center hidden">
        <i class="fa fa-spinner fa-spin blue fa-4x"></i>{{ __('Loading...') }}
    </div>

    {{-- Hidden inputs for JS --}}
    <input type="hidden" name="base_url" id="base_url" value="{{ url('/' . $locale) }}/">
    <input type="hidden" id="hdncurrencyrate" value="{{ $currencyRate }}">
    <input type="hidden" id="hdncurrencycode" value="{{ $currencyCode }}">

    {{-- Scripts --}}
    @php $version = "0.9"; @endphp
    <script src="{{ $resourceUrl }}scripts/scripts.js?v={{ $version }}"></script>
    <script src="{{ $resourceUrl }}scripts/plugins.min.js?v={{ $version }}"></script>
    <script src="{{ $resourceUrl }}scripts/main.js?v={{ $version }}"></script>
    <script src="{{ $resourceUrl }}scripts/common.js?v={{ $version }}"></script>
    <script src="{{ $resourceUrl }}scripts/custom.js?v={{ $version }}"></script>

    {{-- Smart App Banner --}}
    <script type="text/javascript" src="{{ $resourceUrl }}scripts/smart-app-banner.js?v=1.35"></script>
    <script type="text/javascript">
        new SmartBanner({
            daysHidden: 15,
            daysReminder: 90,
            appStoreLanguage: 'us',
            title: 'Custom unique gift store',
            author: 'Download Rullart App',
            button: 'VIEW',
            store: {
                ios: 'On the App Store',
                android: 'In Google Play'
            },
            price: {
                ios: '',
                android: ''
            }
        });
    </script>

    @stack('scripts')

    @if(isset($settingsArr['Google Analytics']) && !empty($settingsArr['Google Analytics']))
        {!! $settingsArr['Google Analytics'] !!}
    @endif

    @if(isset($settingsArr['Bing Analytics']) && !empty($settingsArr['Bing Analytics']))
        <script>{!! $settingsArr['Bing Analytics'] !!}</script>
    @endif
</body>
</html>

