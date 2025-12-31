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
            @foreach($processedItems as $item)
                <div class="cart-item clearfix {{ $item->strike }}">
                    <div class="media">
                        <a href="{{ route('product.show', ['locale' => $locale, 'category' => $item->categorycode, 'product' => $item->productcode]) }}">
                            <img src="{{ \App\Helpers\ImageHelper::url($item->image, 'gallary-') }}" width="80" height="93" alt="{{ $item->title }}">
                        </a>
                    </div>
                    <div class="data">
                        <p class="product-title">
                            <a href="{{ route('product.show', ['locale' => $locale, 'category' => $item->categorycode, 'product' => $item->productcode]) }}">
                                {{ str_replace(':', '/', $item->title) }}
                            </a>
                        </p>
                        <p class="qty">{{ __('Qty') }} : {{ $item->qty }}</p>
                        <p class="price {{ $item->strike }}">
                            {{ number_format($item->calculated_subtotal * $currencyRate, 0) }} {{ $currencyCode }}
                        </p>
                        @if($item->size != "0")
                            <p class="qty">{{ __('Size') }} : {{ $item->sizename }}</p>
                        @endif
                    </div>

                    @if($showGiftMessage == "Yes" && $item->giftmessageid > 0)
                        <div class="gift">
                            <form name="frmmessage" class="frmmessage">
                                <div class="gift-occasion">
                                    <label>
                                        {{ __('Gift Message') }}({{ __('additional fee of') }}
                                        {{ number_format($giftMessageCharge * $currencyRate, 0) }} {{ $currencyCode }})
                                    </label>
                                    <span class="remove-gift-options" data-type="{{ $item->gift_type }}">Remove</span>
                                </div>
                                <div class="gift-options">
                                    <div class="row">
                                        <div class="ocassion">
                                            <select class="cs select-occassion giftmessageid" name="giftmessageid">
                                                <option value="0">{{ __('Select') }}</option>
                                                @foreach($messages ?? [] as $message)
                                                    <option value="{{ $message->messageid }}" {{ $message->messageid == $item->giftmessageid ? 'selected' : '' }}>
                                                        {{ $locale == 'ar' ? ($message->messageAR ?? $message->message) : ($message->message ?? $message->messageAR) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="message">
                                            <textarea name="giftmessage" maxlength="150" class="input-message giftmessagetext" 
                                                placeholder="{{ __('Type your message here.') }}">{{ str_replace("\n", "", $item->giftmessage) }}</textarea>
                                        </div>
                                        
                                        @if($item->giftqty)
                                            <div class="message">
                                                {{ __('Gift Qty') }} : {{ $item->giftqty }}
                                            </div>
                                        @endif
                                        
                                        <div class="alert alert-danger giftmsgselect" style="display: none">
                                            {{ __('Please select Gift Occassion.') }}
                                        </div>
                                        <div class="btns hide">
                                            <button type="button" class="btn btn-secondary btngiftmessage">{{ __('Save') }}</button>
                                        </div>
                                        <input type="hidden" name="hdnrowid" class="hdnrowid" value="{{ $item->cartitemid }}">
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <a class="delete-item" href="javascript:;" onclick="remove_cart('{{ $item->cartitemid }}')">
                        <svg class="icon icon-close">
                            <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
                        </svg>
                    </a>
                </div>
            @endforeach

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

