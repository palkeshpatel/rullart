@extends('frontend.layouts.app')

@section('content')
@php
    $dir = $locale == 'ar' ? 'dir="rtl"' : '';
    $backurl = request()->header('referer') ?: route('category.index', ['locale' => $locale, 'category' => $productData->categorycode]);
    $isSoldOut = isset($productData->qty) && $productData->qty <= 0;
    $price = isset($productData->sellingprice) ? $productData->sellingprice : $productData->price;
    $finalPrice = $price * $currencyRate;
    $discount = isset($productData->discount) ? $productData->discount : 0;
@endphp

<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a>
            </li>
            @if(!empty($parentcategory ?? ''))
                <li class="breadcrumb-item product active">
                    <a href="{{ route('category.index', ['locale' => $locale, 'category' => $parentcategorycode ?? $productData->categorycode]) }}">
                        {{ $parentcategory ?? '' }}
                    </a>
                </li>
            @endif
        </ol>
        <h1>
            <span>
                <span class="before-icon"></span>
                <a href="{{ route('category.index', ['locale' => $locale, 'category' => $productData->categorycode]) }}">
                    {{ $productData->category ?? '' }}
                </a>
                <span class="after-icon"></span>
            </span>
        </h1>
        <a href="{{ $backurl }}" class="go-back">
            <svg class="icon icon-arrow-right">
                <use xlink:href="{{ $resourceUrl }}images/symbol-defs.svg#icon-arrow-right"></use>
            </svg>{{ __('Back to Results') }}
        </a>
    </div>
    
    <div class="inside-content">
        <div class="container-fluid">
            <div class="row">
                {{-- Product Gallery --}}
                <div class="col-sm-6">
                    <div class="prod-gallery">
                        <div id="prodSlider" class="prod-slider" {!! $dir !!}>
                            @foreach($photos as $photo)
                                @if(!empty($photo))
                                    <div>
                                        <a class="img-zoom" href="{{ \App\Helpers\ImageHelper::url($photo, '') }}">
                                            <img src="{{ \App\Helpers\ImageHelper::url($photo, 'detail-') }}"
                                                 data-imagezoom="{{ \App\Helpers\ImageHelper::url($photo, '') }}"
                                                 data-zoomviewsize="[600,600]">
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Product Details --}}
                <div class="col-sm-6">
                    <div class="prod-details">
                        <h2 class="prod-title">{{ $productData->title }}</h2>
                        
                        <div class="prod-price">
                            @if($discount > 0)
                                <span class="standard-price">{{ number_format($productData->price * $currencyRate, 3) }} {{ $currencyCode }}</span>
                                <span class="actual-price">{{ number_format($finalPrice, 3) }} {{ $currencyCode }}</span>
                                <span class="product-discount">-{{ round(($discount / $productData->price) * 100) }}%</span>
                            @else
                                <span class="actual-price">{{ number_format($finalPrice, 3) }} {{ $currencyCode }}</span>
                            @endif
                        </div>

                        @if($isSoldOut)
                            <p class="sold-out">{{ __('SOLD OUT') }}</p>
                        @endif

                        {{-- Size Selection --}}
                        @if(isset($sizes) && $sizes->count() > 0)
                            <div class="prod-size">
                                <label>{{ __('Select Size') }}</label>
                                <select id="productSize" class="form-control">
                                    <option value="">{{ __('Select Size') }}</option>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size->filtervalue }}" data-qty="{{ $size->qty }}">
                                            {{ $size->filtervalue }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Add to Cart --}}
                        <div class="prod-actions">
                            <button id="btnAddToCart" class="btn btn-primary {{ $isSoldOut ? 'hidden' : '' }}" 
                                    data-productid="{{ $productData->productid }}"
                                    data-productcode="{{ $productData->productcode }}">
                                {{ __('Add to Cart') }}
                            </button>
                            <button class="btn btn-secondary {{ !$isSoldOut ? 'hidden' : '' }}" disabled>
                                {{ __('Sold Out') }}
                            </button>
                            
                            <button id="btnAddToWishlist" class="btn btn-wishlist" 
                                    data-productid="{{ $productData->productid }}">
                                <svg class="icon icon-heart">
                                    <use xlink:href="/static/images/symbol-defs.svg#icon-heart"></use>
                                </svg>
                            </button>
                        </div>

                        {{-- Product Description --}}
                        @if(!empty($productData->longdescr))
                            <div class="prod-description">
                                <h3>{{ __('Description') }}</h3>
                                <div {!! $dir !!}>
                                    {!! nl2br(e($productData->longdescr)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <section class="related-products">
            <div class="container">
                <h3>{{ __('Related Products') }}</h3>
                <div class="row">
                    @foreach($relatedProducts as $related)
                        @php
                            $relatedTitle = $locale == 'ar' ? ($related->title ?? $related->shortdescr) : ($related->shortdescr ?? $related->title);
                            $relatedPrice = isset($related->sellingprice) ? $related->sellingprice : $related->price;
                            $relatedFinalPrice = $relatedPrice * $currencyRate;
                            $relatedPhoto = $related->photo1 ?? '';
                        @endphp
                        <div class="col-xs-6 col-sm-3">
                            <div class="product-item">
                                <a href="{{ route('product.show', ['locale' => $locale, 'category' => $related->categorycode, 'product' => $related->productcode]) }}">
                                    <span class="product-image">
                                        <img src="{{ \App\Helpers\ImageHelper::url($relatedPhoto, 'thumb-') }}" alt="{{ $relatedTitle }}">
                                    </span>
                                    <span class="product-content">
                                        <span class="product-title">{{ $relatedTitle }}</span>
                                        <span class="product-price">
                                            <span class="actual-price">{{ number_format($relatedFinalPrice, 3) }} {{ $currencyCode }}</span>
                                        </span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>

@push('scripts')
<script src="{{ $resourceUrl }}scripts/details.js?v=0.9"></script>
<script src="{{ $resourceUrl }}scripts/product.js?v=0.9"></script>
@endpush
@endsection

