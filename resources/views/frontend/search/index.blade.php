@extends('frontend.layouts.app')

@section('content')
<input type="hidden" id="hdnResourceURL" value="{{ $resourceUrl }}">
<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a>
            </li>
            <li class="breadcrumb-item">{{ __('Search Results') }}</li>
        </ol>
        <h1>
            <span>
                <span class="before-icon"></span>{{ $search }}<span class="after-icon"></span>
            </span>
        </h1>
        @if(isset($collections['productcnt']) && $collections['productcnt'] > 0)
            <p class="results-num">
                {{ $collections['productcnt'] }} {{ __('Results found') }}
            </p>
            <div class="sort-by">
                <select class="cs" name="sortby" id="sortby">
                    <option value="relevance" {{ $sortby == 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                    <option value="lowtohigh" {{ $sortby == 'lowtohigh' ? 'selected' : '' }}>{{ __('Low to High Price') }}</option>
                    <option value="hightolow" {{ $sortby == 'hightolow' ? 'selected' : '' }}>{{ __('High to Low Price') }}</option>
                </select>
            </div>
        @endif
    </div>
    
    <div class="inside-content">
        <div id="divShowLoading" class="p-loader" style="display:none;"></div>
        
        @if(isset($collections['productcnt']) && $collections['productcnt'] > 0)
            <div class="container-fluid">
                <a class="btn filter-toggle hidden-lg hidden-md" href="javascript:;" id="filterToggle">
                    <span class="icon-plus"></span>{{ __('Refine Results') }}
                </a>
                <div class="row">
                    <div id="colFilters" class="col-md-3 col-filters">
                        {{-- Side filter will be added here if needed --}}
                    </div>
                    <div class="col-md-9 col-catalog">
                        <div class="row catalog-items">
                            @foreach($collections['products'] as $product)
                                @php
                                    $photo = $product->photo1 ?? 'noimage.jpg';
                                    $productTitle = $locale == 'ar' 
                                        ? ($product->title ?? $product->shortdescr) 
                                        : ($product->shortdescr ?? $product->title);
                                    $price = isset($product->sellingprice) ? $product->sellingprice : $product->price;
                                    $finalPrice = $price * $currencyRate;
                                    $discount = isset($product->discount) ? $product->discount : 0;
                                    $isSoldOut = isset($product->qty) && $product->qty <= 0;
                                @endphp
                                <div class="col-xs-6 col-sm-4">
                                    <div class="product-item">
                                        <a href="{{ route('product.show', ['locale' => $locale, 'category' => $product->categorycode, 'product' => $product->productcode]) }}" title="{{ $productTitle }}">
                                            <span class="product-image">
                                                <img src="{{ asset('storage/upload/product/' . $photo) }}" alt="{{ $productTitle }}">
                                            </span>
                                            <span class="product-content">
                                                <span class="product-title">{{ $productTitle }}</span>
                                                <span class="product-price">
                                                    @if($discount > 0)
                                                        <span class="standard-price">{{ number_format($product->price * $currencyRate, 0) }} {{ $currencyCode }}</span>
                                                    @endif
                                                    <span class="actual-price">{{ number_format($finalPrice, 0) }} {{ $currencyCode }}</span>
                                                </span>
                                            </span>
                                            @if($discount > 0)
                                                <span class="product-discount">-{{ round(($discount / $product->price) * 100) }}%</span>
                                            @endif
                                            @if($isSoldOut)
                                                <p class="sold-out">{{ __('SOLD OUT') }}</p>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @php
                            $showFooter = '';
                            if(isset($collections['totalpage']) && $collections['totalpage'] > $page) {
                                $showall = request()->get('showall', '');
                                $showFooter = $showall == 'yes' ? 'hidden' : '';
                            } else {
                                $showFooter = 'hidden';
                            }
                        @endphp
                        
                        <div class="catalog-footer {{ $showFooter }}">
                            <a class="btn btn-load" href="#" id="showall">{{ __('SHOW MORE PRODUCTS') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="container-fluid">
                <div class="no-results">
                    <h3>{{ __("We couldn't find any matches!") }}</h3>
                    <p class="small">
                        {{ __('Please check the spelling or try searching something else') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</main>

@push('scripts')
<script src="{{ $resourceUrl }}scripts/search.js?v=0.9"></script>
<script src="{{ $resourceUrl }}scripts/product.js?v=0.9"></script>
<script>
    // Handle sort by change
    document.getElementById('sortby')?.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('sortby', this.value);
        window.location.href = url.toString();
    });
    
    // Handle show more products
    document.getElementById('showall')?.addEventListener('click', function(e) {
        e.preventDefault();
        const url = new URL(window.location.href);
        url.searchParams.set('showall', 'yes');
        url.searchParams.set('page', {{ $page + 1 }});
        window.location.href = url.toString();
    });
</script>
@endpush
@endsection

