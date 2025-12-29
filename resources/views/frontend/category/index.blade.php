@extends('frontend.layouts.app')

@section('content')
@php
    $categoryTitle = $collections['category'] ? ($locale == 'ar' ? $collections['category']->categoryAR : $collections['category']->category) : ($metaTitle ?? __('All Products'));
    $productcnt = $collections['productcnt'] ?? 0;
    $totalpage = $collections['totalpage'] ?? 1;
    $page = request()->get('page', 1);
    $main = request()->get('main', 0);
    $sortby = request()->get('sortby', 'relevance');
@endphp

<style>
    .other-details-accordion {
        max-width: 600px;
        margin: 20px auto;
        font-family: Montserrat,Arial,sans-serif;
        border: 1px solid #ccc;
        border-radius: 6px;
        overflow: hidden;
    }

    .other-details-accordion .accordion-item {
        border-bottom: 1px solid #ddd;
        position: relative;
    }

    .other-details-accordion .accordion-toggle {
        display: none;
    }

    .other-details-accordion .accordion-label {
        display: flex;
        justify-content: start;
        align-items: center;
        padding: 15px 20px;
        background: #f7f7f7;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .other-details-accordion .accordion-label:hover {
        background: #e2e2e2;
    }

    .other-details-accordion .title {
        font-size: 16px;
    }

    .other-details-accordion .other-details-visible-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #999;
        border-radius: 4px;
        display: inline-block;
        position: relative;
        margin-right: 1rem;
        margin-left: 1rem;
    }

    .other-details-accordion .accordion-toggle:checked+.accordion-label .other-details-visible-checkbox::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 5px;
        width: 4px;
        height: 9px;
        border: solid #1b1b1b;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .other-details-accordion .accordion-body {
        max-height: 0;
        overflow: hidden;
        background: #fff;
        padding: 0 20px;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }

    .other-details-accordion .accordion-toggle:checked~.accordion-body {
        max-height: 300px;
        padding: 15px 20px;
        overflow: scroll;
    }
    
    @media (max-width: 767px) {
        .sort-by-mobile-bottom {
            bottom: -125px !important;
        }
    }
</style>

<main class="inside">
    @if(isset($collections['category']) && $collections['category']->photo)
    <div class="inside-hero">
        <img src="{{ url('storage/' . $collections['category']->photo) }}" alt="{{ $categoryTitle }}">
    </div>
    @endif
    
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a>
            </li>
            @if(isset($isall) && $isall)
                <li class="breadcrumb-item active" dir="ltr">{{ __('All') }}</li>
            @else
                <li class="breadcrumb-item active" dir="ltr">{{ $categoryTitle }}</li>
            @endif
        </ol>
        
        <h1>
            <span>
                <span class="before-icon"></span>
                {{ isset($isall) && $isall ? __('All') : $categoryTitle }}
                <span class="after-icon"></span>
            </span>
        </h1>
        
        @if($productcnt >= 0)
            <p class="results-num">
                {{ $productcnt }} {{ __('Results Found') }}</p>
            
            <div class="sort-by {{ !empty($collections['category']->category_description ?? '') ? 'sort-by-mobile-bottom' : '' }}">
                <select class="cs" name="sortby" id="sortby">
                    <option value="relevance" {{ $sortby == 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                    <option value="lowtohigh" {{ $sortby == 'lowtohigh' ? 'selected' : '' }}>{{ __('Price Low to High') }}</option>
                    <option value="hightolow" {{ $sortby == 'hightolow' ? 'selected' : '' }}>{{ __('Price High to Low') }}</option>
                </select>
            </div>
        @endif
    </div>
    
    <div class="inside-content">
        <div id="divShowLoading" class="p-loader" style="display:none;"></div>
        
        @if($productcnt >= 0)
            <div class="container-fluid">
                @if(!empty($collections['category']->category_description ?? ''))
                    <div class="hidden-lg hidden-md" style="margin:3rem !important;">
                        <p class="text-center m-3 fs-3">{{ $collections['category']->category_description ?? '' }}</p>
                    </div>
                @endif
                
                <a class="btn filter-toggle hidden-lg hidden-md" href="javascript:;" id="filterToggle">
                    <span class="icon-plus"></span>{{ __('Refine Results') }}
                </a>
                
                <div class="row" style="display:flex; flex-wrap:wrap; clear: both;">
                    <div id="colFilters" class="col-md-3 col-filters vsl">
                        @include('frontend.category.sidefilter', ['collections' => $collections, 'locale' => $locale, 'categoryCode' => $categoryCode ?? ''])
                    </div>
                    
                    @if($productcnt > 0)
                        <div class="col-md-9 col-catalog">
                            @if(!empty($collections['category']->category_description ?? ''))
                                <div class="hidden-sm hidden-xs" style="margin:0 0 30px 0 !important;">
                                    <p class="text-center m-3 fs-3">{{ $collections['category']->category_description ?? '' }}</p>
                                </div>
                            @endif
                            
                            <div class="row catalog-items">
                                @foreach($collections['products'] as $row)
                                    @php
                                        $photo = $row->photo1 ?? '';
                                        if (empty($photo)) {
                                            $photo = 'noimage.jpg';
                                        }
                                        $productTitle = $row->title ?? '';
                                        $price = $row->price ?? 0;
                                        $sellingPrice = $row->sellingprice ?? $price;
                                        $discount = $row->discount ?? 0;
                                        $finalPrice = $sellingPrice * $currencyRate;
                                        $isSoldOut = isset($row->qty) && $row->qty <= 0;
                                    @endphp
                                    
                                    <div class="col-xs-6 col-sm-4">
                                        <div class="product-item">
                                            <a href="{{ route('product.show', ['locale' => $locale, 'category' => $row->categorycode, 'product' => $row->productcode]) }}">
                                                <span class="product-image">
                                                    <img src="{{ url('storage/thumb-' . $photo) }}" alt="{{ $productTitle }}">
                                                </span>
                                                <span class="product-content">
                                                    <span class="product-title">{{ $productTitle }}</span>
                                                    <span class="product-price">
                                                        @if($discount > 0)
                                                            @php
                                                                $decimal = $currencyCode == 'KWD' ? 3 : 2;
                                                                $standardPrice = number_format($price * $currencyRate, $decimal);
                                                            @endphp
                                                            <span class="standard-price">{{ $standardPrice }} {{ $currencyCode }}</span>
                                                        @endif
                                                        @php
                                                            $decimal = $currencyCode == 'KWD' ? 3 : 2;
                                                            $actualPrice = number_format($finalPrice, $decimal);
                                                        @endphp
                                                        <span class="actual-price">{{ $actualPrice }} {{ $currencyCode }}</span>
                                                    </span>
                                                </span>
                                                @if($discount > 0)
                                                    @php
                                                        $discountPercent = is_numeric($discount) && $price > 0 ? round(($discount / $price) * 100) : 0;
                                                    @endphp
                                                    <span class="product-discount">-{{ number_format($discountPercent, 0) }}%</span>
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
                                if ($totalpage > $page) {
                                    $showall = request()->get('showall', '');
                                    $showfooter = ($showall == 'yes') ? 'hidden' : '';
                                } else {
                                    $showfooter = 'hidden';
                                }
                            @endphp
                            
                            <div class="catalog-footer {{ $showfooter }}">
                                <a class="btn btn-load" href="#" id="showall">{{ __('SHOW MORE PRODUCTS') }}</a>
                            </div>
                            
                            @if(!empty($collections['category']->category_other_details_title ?? ''))
                                <div class="other-details-accordion">
                                    <div class="accordion-item">
                                        <input type="checkbox" id="acc1" class="accordion-toggle" />
                                        <label for="acc1" class="accordion-label">
                                            <span class="other-details-visible-checkbox"></span>
                                            <span class="title">{{ $collections['category']->category_other_details_title ?? '' }}</span>
                                        </label>
                                        <div class="accordion-body">
                                            <p>{!! $collections['category']->category_other_details ?? '' !!}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var accordionLabels = document.querySelectorAll('.accordion-label');
                                        accordionLabels.forEach(function(label) {
                                            label.addEventListener('click', function(event) {
                                                var checkbox = this.previousElementSibling;
                                                if (checkbox && checkbox.classList.contains('accordion-toggle')) {
                                                    event.preventDefault();
                                                    checkbox.checked = !checkbox.checked;
                                                    event.stopPropagation();
                                                }
                                            });
                                        });
                                    });
                                </script>
                            @endif
                        </div>
                    @else
                        <div class="container-fluid">
                            <div class="no-results">
                                <h3>{{ __('No products found !!!') }}</h3>
                                <p class="small">{{ __('Please check other categories.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="container-fluid">
                <div class="no-results">
                    <h3>{{ __('No products found !!!') }}</h3>
                    <p class="small">{{ __('Please check other categories.') }}</p>
                </div>
            </div>
        @endif
    </div>
</main>

<input type="hidden" id="hdnResourceURL" value="{{ $imageUrl }}">
<input type="hidden" id="hdnPageNo" value="1">

@push('scripts')
<script src="{{ $resourceUrl }}scripts/category.js?v=0.9"></script>
<script src="{{ $resourceUrl }}scripts/product.js?v=0.9"></script>
@endpush
@endsection
