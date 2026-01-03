@extends('frontend.layouts.app')

@section('content')
    @php
        $locale = app()->getLocale();
        $currencyCode = Session::get('currencycode', config('app.default_currencycode', 'KWD'));
        $currencyRate = Session::get('currencyrate', 1);
        $creditcardChecked = $currencyCode == 'KWD' ? '' : 'checked';
    @endphp

    <main class="inside">
        <div class="inside-header">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Checkout') }}</li>
            </ol>
            <h1><span><span class="before-icon"></span>{{ __('Checkout') }}<span class="after-icon"></span></span></h1>
        </div>
        <div class="inside-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8 col-lg-8 col-checkout">
                        <div class="checkout-steps">
                            <ul class="list-unstyled clearfix">
                                <li class="col-xs-4 complete">
                                    <a class="step"
                                        href="{{ route('checkout.index', ['locale' => $locale]) }}">{{ __('Shipping & Billing') }}</a>
                                </li>
                                <li class="col-xs-4 active">
                                    <span class="step">{{ __('Payment') }}</span>
                                </li>
                                <li class="col-xs-4">
                                    <span class="step">{{ __('Reciept') }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="checkout-content">
                            <form id="formPayment" method="post">
                                @csrf
                                <div class="checkout-section">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h2>{{ __('Select Payment Method') }}</h2>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="payment-method">
                                                <ul class="method-list list-unstyled row">
                                                    @if ($currencyCode == 'KWD' && $defaultCountryId == 1)
                                                        <li class="col-sm-6">
                                                            <label>
                                                                <input id="knet" name="method" value="Knet"
                                                                    type="radio" checked required>
                                                                {{ __('Knet') }}
                                                                <img src="{{ $imageUrl }}images/knet.png"
                                                                    alt="Knet">
                                                            </label>
                                                        </li>
                                                    @endif
                                                    @if ($defaultCountryId == 1)
                                                        <li class="col-sm-6">
                                                            <label>
                                                                <input id="creditcard" name="method" value="Credit Card"
                                                                    type="radio" {{ $creditcardChecked }} required>
                                                                {{ __('Credit Card') }}
                                                                <img src="{{ $imageUrl }}images/cc.png"
                                                                    alt="Credit Card">
                                                            </label>
                                                        </li>
                                                    @endif
                                                    <li class="col-sm-6 apple-pay" id="apple-pay">
                                                        <label>
                                                            <input id="applepay" name="method" value="Apple Pay"
                                                                type="radio" required>
                                                            {{ __('Apple Pay') }}
                                                            <img style="height:60%"
                                                                src="{{ $imageUrl }}images/apple-pay.jpg"
                                                                alt="Apple Pay">
                                                        </label>
                                                    </li>
                                                    @if ($showTabby)
                                                        <li class="col-sm-6">
                                                            <label>
                                                                <input id="tabby" name="method" value="tabby"
                                                                    type="radio" required>
                                                                &nbsp;{{ __('Pay in 4. No interest, no fees') }}
                                                                <img src="{{ $imageUrl }}images/tabby-new.png"
                                                                    alt="{{ __('Tabby') }}">
                                                                <div id="tabbyCard"></div>
                                                            </label>
                                                            <div style="margin-top: 10px;">
                                                                <p><strong>{{ __('Split your purchase') }}</strong>
                                                                    {{ __('in 4 monthly payments') }}</p>
                                                                <ul style="list-style: none; padding-left: 0;">
                                                                    <li
                                                                        style="display: flex; align-items: center; margin-bottom: 5px;">
                                                                        <span
                                                                            style="color: green; margin-right: 5px;">✓</span>
                                                                        {{ __('No processing fees') }}
                                                                    </li>
                                                                    <li
                                                                        style="display: flex; align-items: center; margin-bottom: 5px;">
                                                                        <span
                                                                            style="color: green; margin-right: 5px;">✓</span>
                                                                        {{ __('Use any card') }}
                                                                    </li>
                                                                    <li
                                                                        style="display: flex; align-items: center; margin-bottom: 5px;">
                                                                        <span
                                                                            style="color: green; margin-right: 5px;">✓</span>
                                                                        {{ __('Buyer protection') }}
                                                                    </li>
                                                                </ul>
                                                                <a href="javascript:;" id="viewTabbyOptions"
                                                                    style="color: #007bff; text-decoration: underline;">{{ __('View options') }}</a>
                                                            </div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="checkout-section">
                                    <div class="address-list">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="address-item">
                                                    <h4>{{ __('Delivery Address') }}</h4>
                                                    <p>
                                                        {!! $shippingFormatted !!}
                                                    </p>
                                                    <a
                                                        href="{{ route('checkout.index', ['locale' => $locale]) }}">{{ __('Edit Address') }}</a>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="address-item">
                                                    <h4>{{ __('Billing Address') }}</h4>
                                                    <p>
                                                        {!! $billingFormatted !!}
                                                    </p>
                                                    <a
                                                        href="{{ route('checkout.index', ['locale' => $locale]) }}">{{ __('Edit Address') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($asGift == 'on')
                                        <div class="gift-message">
                                            <h4>{{ __('Gift Message') }}</h4>
                                            <p>
                                                {!! nl2br($giftMessage) !!}
                                            </p>
                                            <a
                                                href="{{ route('checkout.index', ['locale' => $locale]) }}">{{ __('Edit Message') }}</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="checkout-section">
                                    <div class="row">
                                        <div class="col-proceed">
                                            <button id="btnProceedPayment" type="submit"
                                                class="btn btn-primary">{{ __('Confirm & Pay now') }}</button>
                                        </div>
                                        <div class="col-continue">
                                            <button id="btnContinue" type="button"
                                                class="btn btn-outline-primary">{{ __('GO BACK') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <div class="overlay-section">
                            <div class="overlay-title col-cart">
                                <h2>
                                    <svg class="icon icon-bag">
                                        <use xlink:href="/static/images/symbol-defs.svg#icon-bag"></use>
                                    </svg>
                                    {{ __('My Shopping Bag') }}
                                </h2>
                            </div>
                            <div class="cart-list">
                                @foreach ($cartItems as $item)
                                    @php
                                        $strike = '';
                                        $internation_ship = $item->internation_ship ?? 1;
                                        $giftmessageid = $item->giftmessageid ?? 0;
                                        $giftqty = $item->giftqty ?? 0;
                                        $giftmessage_charge = $settingsArr['Gift Message Charge'] ?? 0;

                                        $subtotal = $item->subtotal ?? 0;
                                        if (!empty($giftmessageid) && !empty($giftqty)) {
                                            $giftmessage_charge = $giftmessage_charge * $giftqty;
                                            $subtotal = $subtotal + $giftmessage_charge;
                                        }

                                        if (
                                            $internation_ship == 0 &&
                                            ($shippingAddr['country'] ?? '') != config('app.default_country')
                                        ) {
                                            $strike = 'strike';
                                        }

                                        $productcode = $item->productcode ?? '';
                                        $categorycode = $item->categorycode ?? '';
                                        $gifttitle =
                                            $locale == 'ar'
                                                ? $item->gifttitleAR ?? ($item->gifttitle ?? '')
                                                : $item->gifttitle ?? '';
                                    @endphp
                                    <div class="cart-item clearfix">
                                        <div class="media">
                                            <a
                                                href="{{ route('product.show', ['locale' => $locale, 'category' => $categorycode, 'product' => $productcode]) }}">
                                                <img src="{{ asset('storage/upload/product/' . ($item->image ?? '')) }}"
                                                    width="80" height="93" alt="{{ $item->title ?? '' }}">
                                            </a>
                                        </div>
                                        <div class="data">
                                            <p class="product-title">
                                                <a
                                                    href="{{ route('product.show', ['locale' => $locale, 'category' => $categorycode, 'product' => $productcode]) }}">
                                                    {{ str_replace(':', '/', $item->title ?? '') }}
                                                </a>
                                            </p>
                                            <p class="qty">{{ __('Qty') }} : {{ $item->qty ?? 0 }}</p>
                                            <p class="price {{ $strike }}">
                                                {{ number_format($subtotal * $currencyRate, 3) }} {{ $currencyCode }}
                                            </p>
                                            @if (($item->size ?? 0) != '0')
                                                <p class="qty">{{ __('Size') }} : {{ __($item->sizename ?? '') }}
                                                </p>
                                            @endif
                                            @if ($strike != '')
                                                <div class="internationaldelivery">
                                                    {{ __('This product cannot be shipped outside') }}
                                                    {{ config('app.default_country') }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="gift">
                                            @if ($gifttitle != '')
                                                <p class="occassion">{{ $gifttitle }}</p>
                                            @endif
                                            @if (!empty($item->giftmessage))
                                                <p class="message">{!! nl2br($item->giftmessage) !!}</p>
                                            @endif
                                            @if ($giftqty > 0)
                                                <p class="message">{{ __('Gift Card') }} : {{ $giftqty }}</p>
                                            @endif
                                        </div>
                                        <a class="delete-item hidden" href="javascript:;"
                                            onclick="remove_cart('{{ $item->shoppingcartitemid ?? '' }}')">
                                            <svg class="icon icon-close">
                                                <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
                                            </svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="overlay-section" id="divItemTotal">
                            <table class="cart-table">
                                <tr>
                                    <td>
                                        {{ __('Item Total') }}
                                        <input type="hidden" name="itemtotal" id="itemtotal"
                                            value="{{ number_format($itemTotal, 3, '.', '') }}">
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($itemTotal * $currencyRate, 3) }} {{ $currencyCode }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('Shipping Price') }}</td>
                                    <td class="text-right">
                                        {{ number_format($shippingCharge * $currencyRate, 3) }} {{ $currencyCode }}
                                    </td>
                                </tr>
                                @if ($discountValue > 0)
                                    <tr>
                                        <td>{{ __('Coupon Code Discount') }}</td>
                                        <td class="text-right">
                                            {{ number_format($discountValue * $currencyRate, 3) }} {{ $currencyCode }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($vat > 0)
                                    <tr>
                                        <td>{{ __('VAT') }}</td>
                                        <td class="text-right">
                                            {{ number_format($vat * $currencyRate, 3) }} {{ $currencyCode }}
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="overlay-section">
                            <table class="total-table">
                                <tr>
                                    <td><strong>{{ __('Total') }}</strong></td>
                                    <td class="text-right">
                                        <strong>{{ number_format($cartTotal * $currencyRate, 3) }}
                                            {{ $currencyCode }}</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        @if ($minimumOrderAmount > 0 && $cartTotal < $minimumOrderAmount)
                            <div class="alert alert-warning">
                                {{ __('Total amount must be over') }}
                                <strong>{{ number_format($minimumOrderAmount * $currencyRate, 3) }}
                                    {{ $currencyCode }}</strong>
                            </div>
                            <script>
                                document.getElementById("btnProceedPayment").disabled = true;
                            </script>
                        @else
                            <script>
                                document.getElementById("btnProceedPayment").disabled = false;
                            </script>
                        @endif
                        @php
                            // Determine which terms message to show
                            $termsMessageKey = '';
                            if (($shippingAddr['country'] ?? '') == 'Kuwait') {
                                if ($deliveryMethod == 'Avenues Mall Delivery') {
                                    $termsMessageKey = 'kuwait_avenue_checkout_msg';
                                } elseif ($shippingMethod == 'express') {
                                    $termsMessageKey = 'kuwait_express_checkout_msg';
                                } else {
                                    $termsMessageKey = 'kuwait_checkout_msg';
                                }
                            } else {
                                if ($defaultCountryId == 1) {
                                    $termsMessageKey = 'custom_msg';
                                } else {
                                    $termsMessageKey = 'custom_msg_qatar';
                                }
                            }
                            // Get the translated message from common.php file
                            $termsMessage = trans('common.' . $termsMessageKey, [], $locale);
                            // If translation not found (returns the key), try direct access
                            if (strpos($termsMessage, 'common.') === 0 || $termsMessage == $termsMessageKey) {
                                // Try loading directly from common.php
                                $commonTranslations = trans('common', [], $locale);
                                $termsMessage = $commonTranslations[$termsMessageKey] ?? $termsMessageKey;
                            }
                            // Replace PHP_EOL and newlines with <br/> for display
                            $termsMessage = str_replace(["\r\n", "\r", "\n"], '<br/>', $termsMessage);
                        @endphp
                        <div class="checkbox">
                            <label style="color: red;">
                                <input id="chk_kuwait_delivery" name="chk_kuwait_delivery" type="checkbox">
                                {!! $termsMessage !!}
                            </label>
                        </div>
                        <div class="alert alert-danger" id="divCheckboxMessage" style="display: none;">
                            {{ __('select_checkbox') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @if ($showTabby && ($currencyCode == 'KWD' || $currencyCode == 'SAR'))
        <script src="https://checkout.tabby.ai/tabby-card.js"></script>
        <script>
            new TabbyCard({
                selector: '#tabbyCard',
                currency: '{{ $currencyCode }}',
                lang: '{{ $locale }}',
                price: {{ number_format($cartTotal, 3, '.', '') }},
                size: 'wide',
                theme: 'black',
                header: true
            });
        </script>
    @endif

    <script>
        // Check if the browser is Safari
        var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        // Show the Apple Pay li element if the browser is Safari
        if (isSafari) {
            document.getElementById('apple-pay').style.display = 'block';
        }
    </script>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize form validation for payment form
                $('#formPayment').validate({
                    highlight: function(element) {
                        $(element).parent().addClass("has-error");
                    },
                    unhighlight: function(element) {
                        $(element).parent().removeClass("has-error");
                    },
                    onfocusout: false,
                    invalidHandler: function(form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            validator.errorList[0].element.focus();
                        }
                    }
                });
            });
        </script>
        <script src="{{ $resourceUrl }}scripts/checkout.js?v=0.9"></script>
    @endpush
@endsection
