@extends('frontend.layouts.app')

@section('content')
    @php
        $locale = app()->getLocale();
        $currencyCode = Session::get('currencycode', config('app.default_currencycode', 'KWD'));
        $currencyRate = Session::get('currencyrate', 1);
    @endphp

    <main class="inside">
        <div class="inside-header">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Checkout') }}</li>
            </ol>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>{{ __('Checkout') }}</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <h2>{{ __('Shipping Address') }}</h2>
                    
                    @if($addresses && $addresses->count() > 0)
                        <div class="address-list">
                            @foreach($addresses as $address)
                                <div class="address-item">
                                    <input type="radio" name="address_id" value="{{ $address->addressid }}" id="address_{{ $address->addressid }}">
                                    <label for="address_{{ $address->addressid }}">
                                        <strong>{{ $address->title ?? '' }}</strong><br>
                                        {{ $address->address ?? '' }}<br>
                                        {{ $locale == 'ar' ? ($address->areanameAR ?? $address->areaname) : $address->areaname }}<br>
                                        {{ $locale == 'ar' ? ($address->countrynameAR ?? $address->countryname) : $address->countryname }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>{{ __('No addresses found. Please add an address.') }}</p>
                    @endif

                    <h3>{{ __('Add New Address') }}</h3>
                    <form id="formAddressCheckout" name="formAddressCheckout">
                        @csrf
                        <input type="hidden" name="address_id" id="selected_address_id" value="">
                        
                        <div class="form-group">
                            <label for="firstname">{{ __('First Name') }} *</label>
                            <input class="form-control required" name="firstname" id="firstname" value="{{ $shippingAddr['firstname'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="lastname">{{ __('Last Name') }} *</label>
                            <input class="form-control required" name="lastname" id="lastname" value="{{ $shippingAddr['lastname'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="mobile">{{ __('Tel/Mobile') }} *</label>
                            <input class="form-control required" name="mobile" id="mobile" value="{{ $shippingAddr['mobile'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="addressTitle">{{ __('Address Title') }} *</label>
                            <input class="form-control required" name="addressTitle" id="addressTitle" value="{{ $shippingAddr['title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="country">{{ __('Country') }} *</label>
                            <select id="country" name="country" class="form-control required">
                                @foreach($countries as $country)
                                    <option value="{{ $country->countryname }}" {{ ($shippingAddr['country'] ?? '') == $country->countryname ? 'selected' : '' }}>
                                        {{ $locale == 'ar' ? ($country->countrynameAR ?? $country->countryname) : $country->countryname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="area">{{ __('Area') }} *</label>
                            <select id="area" name="area" class="form-control required">
                                <option value="">{{ __('Select') }}</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->areaid }}" {{ ($shippingAddr['fkareaid'] ?? '') == $area->areaid ? 'selected' : '' }}>
                                        {{ $locale == 'ar' ? ($area->areanameAR ?? $area->areaname) : $area->areaname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="block_number">{{ __('Block') }} *</label>
                            <input class="form-control required" name="block_number" id="block_number" value="{{ $shippingAddr['block_number'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="street_number">{{ __('Street') }} *</label>
                            <input class="form-control required" name="street_number" id="street_number" value="{{ $shippingAddr['street_number'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="house_number">{{ __('House / Building') }} *</label>
                            <input class="form-control required" name="house_number" id="house_number" value="{{ $shippingAddr['house_number'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="floor_number">{{ __('Floor Number') }}</label>
                            <input class="form-control" name="floor_number" id="floor_number" value="{{ $shippingAddr['floor_number'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="flat_number">{{ __('Flat Number') }}</label>
                            <input class="form-control" name="flat_number" id="flat_number" value="{{ $shippingAddr['flat_number'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="shipping_method">{{ __('Shipping Method') }}</label>
                            <select name="shipping_method" id="shipping_method" class="form-control">
                                @foreach($shippingMethods as $key => $method)
                                    <option value="{{ $key }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="alert alert-danger print-error-msg" style="display:none"></div>
                        <div class="checkout-section">
                            <div class="row">
                                <div class="col-proceed">
                                    <button id="btnProceed" type="button" class="btn btn-primary">{{ __('PROCEED TO NEXT STEP') }}</button>
                                </div>
                                <div class="col-continue">
                                    <button id="btnContinueShopping" type="button" class="btn btn-outline-primary">{{ __('CONTINUE SHOPPING') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-4">
                    <div class="order-summary">
                        <h2>{{ __('Order Summary') }}</h2>
                        
                        @if($cartItems && $cartItems->count() > 0)
                            <div class="cart-items">
                                @foreach($cartItems as $item)
                                    <div class="cart-item">
                                        <div class="item-name">{{ $item->title ?? '' }}</div>
                                        <div class="item-qty">Qty: {{ $item->qty ?? 0 }}</div>
                                        <div class="item-price">
                                            {{ number_format(($item->subtotal ?? 0) * $currencyRate, 2) }} {{ $currencyCode }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="order-totals">
                            <div class="total-line">
                                <span>{{ __('Subtotal') }}:</span>
                                <span>{{ number_format($totals['subtotal'] * $currencyRate, 2) }} {{ $currencyCode }}</span>
                            </div>
                            <div class="total-line">
                                <span>{{ __('Shipping') }}:</span>
                                <span>{{ number_format($totals['shipping'] * $currencyRate, 2) }} {{ $currencyCode }}</span>
                            </div>
                            @if($totals['vat'] > 0)
                                <div class="total-line">
                                    <span>{{ __('VAT') }} ({{ $totals['vatPercent'] }}%):</span>
                                    <span>{{ number_format($totals['vat'] * $currencyRate, 2) }} {{ $currencyCode }}</span>
                                </div>
                            @endif
                            <div class="total-line total">
                                <span><strong>{{ __('Total') }}:</strong></span>
                                <span><strong>{{ number_format($totals['total'] * $currencyRate, 2) }} {{ $currencyCode }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection

@push('scripts')
    <script src="{{ $resourceUrl }}scripts/checkout.js"></script>
@endpush

