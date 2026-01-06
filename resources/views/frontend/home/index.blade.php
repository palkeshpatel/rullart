@extends('frontend.layouts.app')

@section('content')
    @php
        // Debug logging for locale in home page
        $currentLocale = $locale ?? app()->getLocale();
        \Log::info('Home View - $locale variable: ' . ($locale ?? 'NOT SET'));
        \Log::info('Home View - app()->getLocale(): ' . app()->getLocale());
        \Log::info('Home View - session("locale"): ' . session('locale', 'NOT SET'));
        \Log::info('Home View - Translation test - "Popular Items": ' . __('Popular Items'));

        // Mobile detection for responsive images (presentation logic - acceptable in view)
        $is_mobile = preg_match(
            '/(android|iphone|ipod|ipad|windows phone|blackberry|mobile)/i',
            request()->userAgent(),
        );
    @endphp

    <section class="hero-content">
        <div>
            <div id="heroSlider" class="hero-slider carousel slide carousel-fade" data-ride="carousel"
                data-interval="{{ $hasVideo ? '6000' : '5000' }}">
                <div class="container-indicators">
                    <div class="container">
                        <ol class="carousel-indicators">
                            @foreach ($homegallery as $index => $item)
                                <li data-target="#heroSlider" data-slide-to="{{ $index }}"
                                    class="{{ $index == 0 ? 'active' : '' }}"></li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                <div class="carousel-inner">
                    @foreach ($homegallery as $index => $item)
                        @php
                            $active = $index == 0 ? 'active' : '';
                            // Select photo based on locale and device
                            if ($locale == 'ar') {
                                $photo =
                                    $is_mobile && !empty($item->photo_mobile_ar)
                                        ? $item->photo_mobile_ar
                                        : $item->photo_ar ?? $item->photo;
                            } else {
                                $photo = $is_mobile && !empty($item->photo_mobile) ? $item->photo_mobile : $item->photo;
                            }
                            // Select title and description based on locale
                            $title = $locale == 'ar' ? $item->titleAR ?? $item->title : $item->title ?? '';
                            $descr = $locale == 'ar' ? $item->descrAR ?? $item->descr : $item->descr ?? '';
                        @endphp

                        @if (!empty($item->videourl))
                            <div class="item {{ $active }} full">
                                <div class="carousel-video">
                                    <video class="slider-video" width="100%" autoplay loop muted preload="auto"
                                        playsinline style="visibility: visible; width: 100%"
                                        poster="{{ asset('storage/upload/homegallery/' . $item->photo) }}">
                                        <source src="{{ asset('storage/upload/homegallery/' . $item->videourl) }}"
                                            type="video/mp4">
                                    </video>
                                </div>
                            </div>
                        @else
                            <div class="item {{ $active }}">
                                @if (!empty($item->link) && $item->link != '-')
                                    <a href="{{ $item->link }}" target="_blank">
                                @endif
                                <img src="{{ asset('storage/upload/homegallery/' . $photo) }}" alt="{{ $title }}"
                                    class="img-responsive fill2">
                                @if (!empty($item->link) && $item->link != '-')
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @php
        // Get page content based on locale
        $pages = \App\Models\Page::where('pagename', 'home')->first();
        if ($pages) {
            $pageTitle = $locale == 'ar' ? $pages->pagetitleAR ?? $pages->pagetitle : $pages->pagetitle ?? '';
            $pageDescription = $locale == 'ar' ? $pages->detailsAR ?? $pages->details : $pages->details ?? '';
        } else {
            $pageTitle = '';
            $pageDescription = '';
        }
    @endphp

    @if (!empty($pageDescription) || !empty($pageTitle))
        <section class="welcome-content">
            <div class="container">
                @if (!empty($pageTitle))
                    <h1>{!! $pageTitle !!}</h1>
                @endif
                @if (!empty($pageDescription))
                    <p>{!! $pageDescription !!}</p>
                @endif
            </div>
        </section>
    @endif

    @if (isset($popular) && $popular->count() > 0)
        <section class="popular-items">
            <div class="container">
                <h2 class="section-title">{{ trans('common.Popular Items') }}</h2>
                <div class="row">
                    @foreach ($popular as $product)
                        @php
                            // Repository maps: title = shortdescr/shortdescrAR, shortdescr = title/titleAR
                            $productTitle =
                                $locale == 'ar'
                                    ? $product->title ?? ($product->shortdescr ?? '')
                                    : $product->shortdescr ?? ($product->title ?? '');
                            $price = isset($product->sellingprice) ? $product->sellingprice : $product->price;
                            $finalPrice = $price * $currencyRate;
                            $discount = isset($product->discount) ? $product->discount : 0;
                            $photo = $product->photo1 ?? '';
                            $isSoldOut = isset($product->qty) && $product->qty <= 0;
                        @endphp
                        <div class="col-xs-6 col-sm-4 col-md-3 product-item">
                            <div class="product-box">
                                <a
                                    href="{{ route('product.show', ['locale' => $locale, 'category' => $product->categorycode, 'product' => $product->productcode]) }}">
                                    <div class="product-image">
                                        <img src="{{ asset('storage/upload/product/' . $photo) }}"
                                            alt="{{ $productTitle }}" class="img-responsive">
                                        @if ($discount > 0)
                                            <span class="discount-badge">{{ round(($discount / $price) * 100) }}%</span>
                                        @endif
                                        @if ($isSoldOut)
                                            <span class="sold-out-badge">{{ __('Sold Out') }}</span>
                                        @endif
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-title">{{ $productTitle }}</h3>
                                        <div class="product-price">
                                            @php
                                                // Translate currency code to Arabic if locale is Arabic
                                                $displayCurrency = $currencyCode;
                                                if ($locale == 'ar') {
                                                    $currencyTranslation = trans('common.' . $currencyCode, [], 'ar');
                                                    // If translation exists and is not the key itself, use it
                                                    if ($currencyTranslation != 'common.' . $currencyCode) {
                                                        $displayCurrency = $currencyTranslation;
                                                    } elseif ($currencyCode == 'KWD' || $currencyCode == 'KD') {
                                                        $displayCurrency = 'دك';
                                                    }
                                                }
                                            @endphp
                                            @if ($discount > 0)
                                                <span class="old-price">{{ number_format($price * $currencyRate, 0) }}
                                                    {{ $displayCurrency }}</span>
                                                <span class="new-price">{{ number_format($finalPrice, 0) }}
                                                    {{ $displayCurrency }}</span>
                                            @else
                                                <span class="price">{{ number_format($finalPrice, 0) }}
                                                    {{ $displayCurrency }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @push('scripts')
        <script src="{{ $resourceUrl }}scripts/main.js?v=0.9"></script>
    @endpush
@endsection
