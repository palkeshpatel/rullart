@extends('frontend.layouts.app')

@section('content')
    @php
        $is_mobile = preg_match(
            '/(android|iphone|ipod|ipad|windows phone|blackberry|mobile)/i',
            request()->userAgent(),
        );
        $hasvideo = false;

        foreach ($homegallery as $item) {
            if (!empty($item->videourl)) {
                $hasvideo = true;
                break;
            }
        }
    @endphp

    <section class="hero-content">
        <div>
            <div id="heroSlider" class="hero-slider carousel slide carousel-fade" data-ride="carousel"
                data-interval="{{ $hasvideo ? '6000' : '5000' }}">
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
                            $photo = $is_mobile && !empty($item->photo_mobile) ? $item->photo_mobile : $item->photo;
                            if ($locale == 'ar') {
                                $photo =
                                    $is_mobile && !empty($item->photo_mobile_ar)
                                        ? $item->photo_mobile_ar
                                        : $item->photo_ar ?? $photo;
                            }
                            $title = $locale == 'ar' ? $item->titleAR ?? $item->title : $item->title;
                            $descr = $locale == 'ar' ? $item->descrAR ?? $item->descr : $item->descr;
                        @endphp

                        @if (!empty($item->videourl))
                            <div class="item {{ $active }} full">
                                <div class="carousel-video">
                                    <video class="slider-video" width="100%" autoplay loop muted preload="auto"
                                        playsinline style="visibility: visible; width: 100%"
                                        poster="{{ $resourceUrl }}storage/{{ $item->photo }}">
                                        <source src="{{ $resourceUrl }}storage/{{ $item->videourl }}" type="video/mp4">
                                    </video>
                                </div>
                            </div>
                        @else
                            <div class="item {{ $active }}">
                                @if (!empty($item->link) && $item->link != '-')
                                    <a href="{{ $item->link }}" target="_blank">
                                @endif
                                    <img src="{{ $resourceUrl }}storage/{{ $photo }}" alt="{{ $title }}"
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

    @if (!empty($metaDescription) || !empty($metaTitle))
        <section class="welcome-content">
            <div class="container">
                @if (!empty($metaTitle))
                    <h1>{{ $metaTitle }}</h1>
                @endif
                @if (!empty($metaDescription))
                    <p>{{ $metaDescription }}</p>
                @endif
            </div>
        </section>
    @endif

    @if (isset($popular) && $popular->count() > 0)
        <section class="popular-items">
            <div class="container">
                <h2 class="section-title">{{ __('Popular Items') }}</h2>
                <div class="row">
                    @foreach ($popular as $product)
                        @php
                            $productTitle =
                                $locale == 'ar'
                                    ? $product->title ?? $product->shortdescr
                                    : $product->shortdescr ?? $product->title;
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
                                        <img src="{{ \App\Helpers\ImageHelper::url($photo, 'thumb-') }}"
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
                                            @if ($discount > 0)
                                                <span class="old-price">{{ number_format($price * $currencyRate, 3) }}
                                                    {{ $currencyCode }}</span>
                                                <span class="new-price">{{ number_format($finalPrice, 3) }}
                                                    {{ $currencyCode }}</span>
                                            @else
                                                <span class="price">{{ number_format($finalPrice, 3) }}
                                                    {{ $currencyCode }}</span>
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
