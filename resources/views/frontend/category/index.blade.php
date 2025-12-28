@extends('frontend.layouts.app')

@section('content')
@php
    $categoryTitle = $category ? ($locale == 'ar' ? $category->categoryAR : $category->category) : ($metaTitle ?? __('All Products'));
@endphp

<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a>
            </li>
            @if($category)
                <li class="breadcrumb-item active">{{ $categoryTitle }}</li>
            @endif
        </ol>
        <h1>
            <span>
                <span class="before-icon"></span>{{ $categoryTitle }}<span class="after-icon"></span>
            </span>
        </h1>
    </div>
    
    <div class="inside-content">
        <div class="container-fluid">
            @if(isset($products) && $products->count() > 0)
                <div class="row">
                    @foreach($products as $product)
                        @php
                            $productTitle = $locale == 'ar' ? ($product->title ?? $product->shortdescr) : ($product->shortdescr ?? $product->title);
                            $price = isset($product->sellingprice) ? $product->sellingprice : $product->price;
                            $finalPrice = $price * $currencyRate;
                            $discount = isset($product->discount) ? $product->discount : 0;
                            $photo = $product->photo1 ?? '';
                            $isSoldOut = isset($product->qty) && $product->qty <= 0;
                        @endphp
                        <div class="col-xs-6 col-sm-4 col-md-3">
                            <div class="product-item">
                                <a href="{{ route('product.show', ['locale' => $locale, 'category' => $product->categorycode, 'product' => $product->productcode]) }}" title="{{ $productTitle }}">
                                    <span class="product-image">
                                        <img src="{{ \App\Helpers\ImageHelper::url($photo, 'thumb-') }}" alt="{{ $productTitle }}">
                                        @if($discount > 0)
                                            <span class="product-discount">-{{ round(($discount / $product->price) * 100) }}%</span>
                                        @endif
                                    </span>
                                    <span class="product-content">
                                        <span class="product-title">{{ $productTitle }}</span>
                                        <span class="product-price">
                                            @if($discount > 0)
                                                <span class="standard-price">{{ number_format($product->price * $currencyRate, 3) }} {{ $currencyCode }}</span>
                                            @endif
                                            <span class="actual-price">{{ number_format($finalPrice, 3) }} {{ $currencyCode }}</span>
                                        </span>
                                    </span>
                                    @if($isSoldOut)
                                        <p class="sold-out">{{ __('SOLD OUT') }}</p>
                                    @endif
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if(method_exists($products, 'links'))
                    <div class="pagination-wrapper">
                        {{ $products->links() }}
                    </div>
                @endif
            @else
                <div class="no-products">
                    <p>{{ __('No products found') }}</p>
                </div>
            @endif
        </div>
    </div>
</main>

@push('scripts')
<script src="{{ $resourceUrl }}scripts/category.js?v=0.9"></script>
<script src="{{ $resourceUrl }}scripts/product.js?v=0.9"></script>
@endpush
@endsection

