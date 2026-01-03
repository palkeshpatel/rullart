@extends('frontend.layouts.app')

@section('content')
    @php
        $dir = $locale == 'ar' ? 'dir="rtl"' : '';
        $backurl =
            request()->header('referer') ?:
            route('category.index', ['locale' => $locale, 'categoryCode' => $productData->categorycode]);
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
                @if (!empty($parentcategory ?? ''))
                    <li class="breadcrumb-item product active">
                        <a
                            href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $parentcategorycode ?? $productData->categorycode]) }}">
                            {{ $parentcategory ?? '' }}
                        </a>
                    </li>
                @endif
            </ol>
            <h1>
                <span>
                    <span class="before-icon"></span>
                    <a
                        href="{{ route('category.index', ['locale' => $locale, 'categoryCode' => $productData->categorycode]) }}">
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
                                @foreach ($photos as $photo)
                                    @if (!empty($photo) && $photo != 'noimage.jpg')
                                        <div>
                                            <a class="img-zoom" href="{{ asset('storage/upload/product/' . $photo) }}">
                                                <img src="{{ asset('storage/upload/product/' . $photo) }}"
                                                    data-imagezoom="{{ asset('storage/upload/product/' . $photo) }}"
                                                    data-zoomviewsize="[600,600]">
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                                @if (!empty($productData->video))
                                    <div class="video-slide">
                                        <video id="myVideo1" class="slide-video slide-media" loop muted preload="metadata"
                                            poster="{{ !empty($productData->videoposter) ? asset('storage/upload/product/' . $productData->videoposter) : asset('storage/playvideo.png') }}"
                                            playsinline controls="true" autoplay>
                                            <source src="{{ asset('storage/upload/product/' . $productData->video) }}"
                                                type="video/mp4" />
                                        </video>
                                    </div>
                                @endif
                            </div>
                            {{-- Product Thumbnails --}}
                            <div class="prod-thumbs-wrapper">
                                <div id="prodThumbs" class="prod-thumbs">
                                    @foreach ($photos as $photo)
                                        @if (!empty($photo) && $photo != 'noimage.jpg')
                                            <div class="thumb-item">
                                                <a href="javascript:;">
                                                    <img src="{{ asset('storage/upload/product/' . $photo) }}"
                                                        width="80" height="93">
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if (!empty($productData->videoposter))
                                        <div class="thumb-item">
                                            <a href="javascript:;">
                                                <img src="{{ asset('storage/upload/product/' . $productData->videoposter) }}"
                                                    width="80" height="93">
                                            </a>
                                        </div>
                                    @elseif(!empty($productData->video))
                                        <div class="thumb-item">
                                            <a href="javascript:;">
                                                <img src="{{ asset('storage/playvideo.png') }}" width="80"
                                                    height="93">
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product Details --}}
                    <div class="col-sm-6">
                        <div class="product-details">
                            <h2 class="product-title">{{ $productData->title }}</h2>
                            <input type="hidden" id="imageurl" value="{{ $imageUrl }}">
                            <p class="product-brief">
                                {!! nl2br(e($productData->shortdescr ?? '')) !!}
                            </p>
                            <p class="product-price">
                                @if ($discount > 0)
                                    <span
                                        class="standard-price">{{ number_format($productData->price * $currencyRate, 0) }}
                                        {{ $currencyCode }}</span>
                                @endif
                                <span class="actual-price" id="price">{{ number_format($finalPrice, 0) }}
                                    {{ $currencyCode }}</span>
                            </p>

                            {{-- Quantity and Size Selection --}}
                            <div class="product-attributes">
                                <div class="row">
                                    @if (!$isSoldOut)
                                        <div class="select-qty">
                                            <span class="lbl">{{ __('Qty') }}</span>
                                            <select id="selectQty" class="cs">
                                                @php
                                                    $qty = $productData->qty ?? 0;
                                                    if ($qty > 10) {
                                                        $qty = 10;
                                                    }
                                                @endphp
                                                @for ($i = 1; $i <= $qty; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    @endif

                                    @if (isset($sizes) && $sizes->count() > 0 && !$isSoldOut)
                                        @php
                                            $nosize = 0;
                                            foreach ($sizes as $size) {
                                                if (
                                                    isset($size->filtervaluecode) &&
                                                    $size->filtervaluecode == 'no-size' &&
                                                    $size->qty > 0
                                                ) {
                                                    $nosize = 1;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if ($nosize == 0)
                                            <div class="select-size">
                                                <span class="lbl">{{ __('Size') }}</span>
                                                <select id="selectSize" class="cs">
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach ($sizes as $size)
                                                        <option value="{{ $size->filtervalueid ?? $size->filtervalue }}">
                                                            {{ $size->filtervalue }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="alert alert-danger" id="hdnSize" style="display: none">
                                {{ __('Please select product size.') }}
                            </div>

                            {{-- Gift Message Options --}}
                            @if ($showGiftMessage && !$isSoldOut)
                                <div class="gift-occasion">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="giftoccasion"
                                                class="giftoccasion">{{ __('Add Gift Message') }}
                                            @if ($giftMessageCharge > 0)
                                                ({{ __('additional fee of') }}
                                                {{ number_format($giftMessageCharge * $currencyRate, 0) }}
                                                {{ $currencyCode }} {{ __('applies') }})
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                <div class="gift-options hide">
                                    <div class="row">
                                        <div class="ocassion">
                                            <span class="lbl">{{ __('Gift Occassion') }}</span>
                                            <select id="giftmessageid" class="cs select-occassion">
                                                <option value="0">{{ __('Select') }}</option>
                                                @foreach ($messages as $message)
                                                    <option value="{{ $message->messageid }}">
                                                        {{ $locale == 'ar' ? $message->messageAR ?? $message->message : $message->message }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="message">
                                            <span class="lbl">{{ __('Gift Message') }}</span>
                                            <textarea class="input-message" placeholder="{{ __('Type your message here.') }}" id="giftmessage" name="giftmessage"
                                                maxlength="150"></textarea>
                                        </div>
                                        @if (!$isSoldOut)
                                            <div class="ocassion">
                                                <span class="lbl">{{ __('Quantity') }}</span>
                                                <select id="selectQty2" class="cs select-occassion">
                                                    @php
                                                        $qty = $productData->qty ?? 0;
                                                        if ($qty > 10) {
                                                            $qty = 10;
                                                        }
                                                    @endphp
                                                    @for ($i = 1; $i <= $qty; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if (!$internationDelivery)
                                <div class="alert alert-danger" id="hdnInternationDelivery">
                                    {{ __('This product cannot be shipped outside') }}
                                    {{ config('app.default_country', 'Kuwait') }}
                                </div>
                            @endif

                            {{-- Add to Cart and Wishlist --}}
                            <div class="product-action">
                                <div class="row">
                                    @if ($internationDelivery)
                                        <div class="col-md-6">
                                            <input type="hidden" name="hdnProductId" id="hdnProductId"
                                                value="{{ $productData->productid }}">
                                            @if (!$isSoldOut)
                                                <button id="addToCart" type="button" class="btn btn-primary">
                                                    <svg class="icon icon-bag">
                                                        <use
                                                            xlink:href="{{ $resourceUrl }}images/symbol-defs.svg#icon-bag">
                                                        </use>
                                                    </svg>{{ __('ADD TO BAG') }}
                                                </button>
                                            @else
                                                <button id="addToCartSoldout" type="button"
                                                    class="btn btn-primary disabled">{{ __('SOLD OUT') }}</button>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="col-md-6">
                                        @if ($wishlistcnt == 0)
                                            <button id="addToWish" type="button" class="btn btn-outline-primary">
                                                <svg class="icon icon-heart">
                                                    <use
                                                        xlink:href="{{ $resourceUrl }}images/symbol-defs.svg#icon-heart">
                                                    </use>
                                                </svg>{{ __('ADD TO WISH LIST') }}
                                            </button>
                                        @else
                                            <button id="addToWish1" type="button"
                                                class="btn btn-outline-primary">{{ __('Item is added in wishlist') }}</button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Accordion Sections --}}
                            <div class="accordion-group" id="accordion">
                                {{-- Description --}}
                                <div class="accordion">
                                    <div class="accordion-heading show">
                                        <h4 class="accordion-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                                {{ __('Description') }}
                                                <span class="plus-minus">
                                                    <svg class="icon icon-plus">
                                                        <use
                                                            xlink:href="{{ $resourceUrl }}images/symbol-defs.svg#icon-plus">
                                                        </use>
                                                    </svg>
                                                </span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="accordion-collapse collapse in">
                                        <div class="accordion-body" {!! $dir !!}>
                                            {!! nl2br($productData->longdescr ?? '') !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Shipping & Delivery --}}
                                <div class="accordion">
                                    <div class="accordion-heading">
                                        <h4 class="accordion-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                                                {{ __('Shipping & Delivery') }}
                                                <span class="plus-minus">
                                                    <svg class="icon icon-plus">
                                                        <use
                                                            xlink:href="{{ $resourceUrl }}images/symbol-defs.svg#icon-plus">
                                                        </use>
                                                    </svg>
                                                </span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseTwo" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            @if (!empty($deliveryReturns))
                                                <ul class="diamond-list">
                                                    @foreach (explode("\n", $deliveryReturns) as $line)
                                                        @if (trim($line))
                                                            <li>{{ trim($line) }}</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Share This --}}
                            <div class="share-this">
                                {{ __('Share This') }}
                                <ul class="list-unstyled clearfix">
                                    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51de59db515459b4"></script>
                                    <div class="addthis_inline_share_toolbox"></div>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Related Products --}}
        @if (isset($relatedProducts) && $relatedProducts->count() > 0)
            <section class="related-products">
                <div class="container">
                    <h3>{{ __('Related Products') }}</h3>
                    <div class="row">
                        @foreach ($relatedProducts as $related)
                            @php
                                $relatedTitle =
                                    $locale == 'ar'
                                        ? $related->title ?? $related->shortdescr
                                        : $related->shortdescr ?? $related->title;
                                $relatedPrice = isset($related->sellingprice)
                                    ? $related->sellingprice
                                    : $related->price;
                                $relatedFinalPrice = $relatedPrice * $currencyRate;
                                $relatedPhoto = $related->photo1 ?? '';
                            @endphp
                            <div class="col-xs-6 col-sm-3">
                                <div class="product-item">
                                    <a
                                        href="{{ route('product.show', ['locale' => $locale, 'category' => $related->categorycode, 'product' => $related->productcode]) }}">
                                        <span class="product-image">
                                            <img src="{{ asset('storage/upload/product/' . $relatedPhoto) }}"
                                                alt="{{ $relatedTitle }}">
                                        </span>
                                        <span class="product-content">
                                            <span class="product-title">{{ $relatedTitle }}</span>
                                            <span class="product-price">
                                                <span class="actual-price">{{ number_format($relatedFinalPrice, 0) }}
                                                    {{ $currencyCode }}</span>
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
