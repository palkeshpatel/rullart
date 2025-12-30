@extends('frontend.layouts.app')

@section('content')
    @php
        $locale = app()->getLocale();
        $currencyCode = Session::get('currencycode', config('app.default_currencycode', 'KWD'));
        $currencyRate = Session::get('currencyrate', 1);
        $shippingCountry = Session::get('shipping_country', config('app.default_country', 'Kuwait'));
        $shippingCharge = Session::get('shipping_charge', 0);
        $freeShippingOver = Session::get('free_shipping_over', 0);
        $freeShippingText = Session::get('free_shipping_text', '');
        $vatPercent = Session::get('vat_percent', 0);
        
        // Get settings (CI uses 'name' as key and 'details' as value)
        $giftMessageCharge = DB::table('settings')
            ->where('name', 'Gift Message Charge')
            ->value('details') ?? 0;
        $showGiftMessage = DB::table('settings')
            ->where('name', 'Show Gift Message')
            ->value('details') ?? 'No';
    @endphp

    <div class="overlay-section">
        <div class="overlay-title">
            <h2>
                <svg class="icon icon-bag">
                    <use xlink:href="/static/images/symbol-defs.svg#icon-bag"></use>
                </svg>
                {{ __('My Shopping Bag') }}
            </h2>
        </div>

        @if($shoppingcartitems && $shoppingcartitems->count() > 0)
            <div class="cart-list">
                @php
                    $total = 0;
                    $cartrow = 1;
                @endphp

                @foreach($shoppingcartitems as $data)
                    @php
                        $strike = "";
                        $internation_ship = $data->internation_ship ?? 1;
                        $giftmessageid = $data->giftmessageid ?? 0;
                        $giftqty = $data->giftqty ?? "";
                        $subtotal = $data->subtotal;
                        
                        // Calculate gift message charge
                        if(!empty($giftqty) && !empty($giftmessageid)) {
                            $giftmessage_charge = $giftMessageCharge * $giftqty;
                            $subtotal = $data->subtotal + $giftmessage_charge;
                        }
                        
                        if ($internation_ship == 0 && $shippingCountry != config('app.default_country', 'Kuwait')) {
                            $strike = "strike";
                        } else {
                            $total = $total + $subtotal;
                        }
                        
                        $giftmessage = $data->giftmessage ?? "";
                        $gifttitle = $data->gifttitle ?? "";
                        $productcode = $data->productcode;
                        $categorycode = $data->categorycode;
                    @endphp

                    <div class="cart-item clearfix {{ $strike }}">
                        <div class="media">
                            <a href="{{ route('product.show', ['locale' => $locale, 'category' => $categorycode, 'product' => $productcode]) }}">
                                <img src="{{ \App\Helpers\ImageHelper::url($data->image, 'gallary-') }}" width="80" height="93" alt="{{ $data->title }}">
                            </a>
                        </div>
                        <div class="data">
                            <p class="product-title">
                                <a href="{{ route('product.show', ['locale' => $locale, 'category' => $categorycode, 'product' => $productcode]) }}">
                                    {{ str_replace(':', '/', $data->title) }}
                                </a>
                            </p>
                            <p class="qty">{{ __('Qty') }} : {{ $data->qty }}</p>
                            <p class="price {{ $strike }}">
                                {{ number_format($subtotal * $currencyRate, 0) }} {{ $currencyCode }}
                            </p>
                            @if($data->size != "0")
                                <p class="qty">{{ __('Size') }} : {{ $data->sizename }}</p>
                            @endif
                        </div>

                        @if($showGiftMessage == "Yes" && $giftmessageid > 0)
                            <div class="gift">
                                <form name="frmmessage" class="frmmessage">
                                    <div class="gift-occasion">
                                        <label>
                                            {{ __('Gift Message') }}({{ __('additional fee of') }}
                                            {{ number_format($giftMessageCharge * $currencyRate, 0) }} {{ $currencyCode }})
                                        </label>
                                        <span class="remove-gift-options" data-type="{{ $data->gift_type ?? 0 }}">Remove</span>
                                    </div>
                                    <div class="gift-options">
                                        <div class="row">
                                            <div class="ocassion">
                                                <select class="cs select-occassion giftmessageid" name="giftmessageid">
                                                    <option value="0">{{ __('Select') }}</option>
                                                    @foreach($messages ?? [] as $message)
                                                        @php
                                                            $messageText = $locale == 'ar' ? ($message->messageAR ?? $message->message) : ($message->message ?? $message->messageAR);
                                                            $selected = $message->messageid == $giftmessageid ? 'selected' : '';
                                                        @endphp
                                                        <option value="{{ $message->messageid }}" {{ $selected }}>{{ $messageText }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="message">
                                                <textarea name="giftmessage" maxlength="150" class="input-message giftmessagetext" 
                                                    placeholder="{{ __('Type your message here.') }}">{{ str_replace("\n", "", $giftmessage) }}</textarea>
                                            </div>
                                            
                                            @if($giftqty)
                                                <div class="message">
                                                    {{ __('Gift Qty') }} : {{ $giftqty }}
                                                </div>
                                            @endif
                                            
                                            <div class="alert alert-danger giftmsgselect" style="display: none">
                                                {{ __('Please select Gift Occassion.') }}
                                            </div>
                                            <div class="btns hide">
                                                <button type="button" class="btn btn-secondary btngiftmessage">{{ __('Save') }}</button>
                                            </div>
                                            <input type="hidden" name="hdnrowid" class="hdnrowid" value="{{ $data->cartitemid }}">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <a class="delete-item" href="javascript:;" onclick="remove_cart('{{ $data->cartitemid }}')">
                            <svg class="icon icon-close">
                                <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
                            </svg>
                        </a>
                    </div>

                    @php $cartrow++; @endphp
                @endforeach

                @php
                    // Calculate discount from coupon
                    $couponvalue = Session::get('couponvalue', 0);
                    $couponcode = Session::get('couponcode', '');
                    $coupontype = Session::get('coupontype', '');
                    $fkcoupontypeid = Session::get('fkcoupontypeid', 0);
                    $couponcategoryid = Session::get('couponcategoryid', []);
                    $discountvalue = 0;
                    
                    if ($couponvalue > 0) {
                        if ($coupontype == "category") {
                            $coupontotal = 0;
                            foreach ($shoppingcartitems as $data) {
                                $collectionarr = DB::table('category')
                                    ->where('categoryid', $data->fkcategoryid)
                                    ->get();
                                $applied = 0;
                                foreach ($collectionarr as $collection) {
                                    $internation_ship = $data->internation_ship ?? 1;
                                    if ($internation_ship == 0 && $shippingCountry != config('app.default_country', 'Kuwait')) {
                                        $itemtotal = 0;
                                    } else if (in_array($collection->parentid, $couponcategoryid) && $applied == 0) {
                                        $subtotal = $data->subtotal;
                                        $coupontotal = $coupontotal + $subtotal;
                                        $applied = 1;
                                    }
                                }
                            }
                            
                            if ($fkcoupontypeid == 2) {
                                $discountvalue = min($coupontotal, $couponvalue);
                            } else {
                                $discountvalue = ($coupontotal * $couponvalue) / 100;
                            }
                        } else {
                            $coupontotal = $total;
                            if ($fkcoupontypeid == 2) {
                                $discountvalue = min($coupontotal, $couponvalue);
                            } else {
                                $discountvalue = ($coupontotal * $couponvalue) / 100;
                            }
                        }
                        
                        if ($total < $discountvalue) {
                            $discountvalue = $total;
                        }
                    }
                    
                    // Calculate VAT
                    $vat = 0;
                    if ($vatPercent > 0) {
                        $vat = (($total + $shippingCharge - $discountvalue) * $vatPercent) / 100;
                    }
                    
                    $carttotal = $total - $discountvalue + $vat + $shippingCharge;
                    Session::put('carttotal', $carttotal);
                @endphp

                @if($total > 0)
                    <div class="overlay-section">
                        <table class="cart-table">
                            <tr>
                                <td>{{ __('Item Total') }}</td>
                                <td class="text-right">
                                    {{ number_format($total * $currencyRate, 0) }} {{ $currencyCode }}
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('Shipping Price') }}</td>
                                <td class="text-right">
                                    {{ number_format($shippingCharge * $currencyRate, 0) }} {{ $currencyCode }}
                                </td>
                            </tr>
                            @if($discountvalue > 0)
                                <tr>
                                    <td>{{ __('Coupon Code Discount') }}</td>
                                    <td class="text-right">
                                        <span id="tdGrandTotal">{{ number_format($discountvalue * $currencyRate, 0) }} {{ $currencyCode }}</span>
                                    </td>
                                </tr>
                            @endif
                            @if($vat > 0)
                                <tr>
                                    <td>{{ __('VAT') }}</td>
                                    <td class="text-right">
                                        <span id="tdGrandTotal">{{ number_format($vat * $currencyRate, 0) }} {{ $currencyCode }}</span>
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
                                    <strong>{{ number_format($carttotal * $currencyRate, 0) }} {{ $currencyCode }}</strong>
                                </td>
                            </tr>
                        </table>
                        
                        @if($freeShippingText != "")
                            <div class="free-delivery">
                                <p>{{ $freeShippingText }}</p>
                            </div>
                        @endif
                        
                        <div class="btn-wrapper">
                            <div class="row">
                                <div class="col-proceed">
                                    @if(!Session::get('customerid'))
                                        <a href="#" id="btnCheckoutLogin" class="btn btn-primary">{{ __('Checkout') }}</a>
                                    @else
                                        <a href="{{ route('checkout.index', ['locale' => $locale]) }}" id="btnCheckout" class="btn btn-primary">{{ __('Checkout') }}</a>
                                    @endif
                                </div>
                                <div class="col-continue">
                                    <a href="{{ route('home', ['locale' => $locale]) }}">
                                        <button id="btnContinueShoppingcart1" type="button" class="btn btn-outline-primary">{{ __('CONTINUE SHOPPING') }}</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="alert alert-warning">
                {{ __('Your Shopping Cart is empty') }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="{{ $resourceUrl }}scripts/shoppingcart.js"></script>
        <script>
            $(function() {
                $('.frmmessage select.cs').customSelect();
                $(document).on('click', '.btn-edit', function(e) {
                    e.preventDefault();
                    if ($(this).parent().parent().find('.gift-change').hasClass('show')) {
                        $(this).parent().parent().find('.gift-change').removeClass('show');
                    }
                    $(this).parent().parent().find('.gift-change').addClass('show');
                });
                $(document).on("click", ".remove-gift-options", function(e) {
                    $(this).closest(".gift").remove();
                });
            });
        </script>
    @endpush
@endsection

