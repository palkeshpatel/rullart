<div class="overlay-section">
    <div class="overlay-title">
        <h2>
            <svg class="icon icon-heart">
                <use xlink:href="/static/images/symbol-defs.svg#icon-heart"></use>
            </svg>{{ __('My Wishlist') }}
        </h2>
    </div>
    <div class="wish-list">
        @if($wishlistItems && $wishlistItems->count() > 0)
            @foreach($wishlistItems as $item)
                @php
                    $photo = $item->photo1 ?? '';
                    if (empty($photo)) {
                        $photo = 'noimage.jpg';
                    }
                    // CI uses 'title' which is already set based on locale (shortdescr or shortdescrAR)
                    $productTitle = $item->title ?? '';
                @endphp
                <div class="wish-item clearfix">
                    <div class="media">
                        <a href="{{ route('product.show', ['locale' => $locale, 'category' => $item->categorycode, 'product' => $item->productcode]) }}">
                            <img src="{{ url('storage/gallary-' . $photo) }}" width="80" height="93" alt="{{ $productTitle }}">
                        </a>
                    </div>
                    <div class="data">
                        <p class="product-title">
                            <a href="{{ route('product.show', ['locale' => $locale, 'category' => $item->categorycode, 'product' => $item->productcode]) }}">
                                {{ $productTitle }}
                            </a>
                        </p>
                    </div>
                    <a class="delete-item" href="javascript:;" onclick="remove_wishlist('{{ $item->fkproductid }}')">
                        <svg class="icon icon-close">
                            <use xlink:href="/static/images/symbol-defs.svg#icon-close"></use>
                        </svg>
                    </a>
                </div>
            @endforeach
        @else
            <div class="alert alert-warning">
                {{ __('Your Wishlist is empty') }}
            </div>
        @endif
    </div>
</div>

