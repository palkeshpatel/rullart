@extends('frontend.layouts.app')

@section('content')
<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('My Orders') }}</li>
        </ol>
        <h1><span><span class="before-icon"></span>{{ __('My Orders') }}<span class="after-icon"></span></span></h1>
    </div>
    <div class="inside-content">
        <div class="container-fluid">
            @if($totalorders > 0)
            <div class="my-orders">
                @foreach($orders['list'] as $order)
                @php
                    $orderno = $order->orderno;
                    $orderDate = $order->orderdate;
                    if($locale == 'ar') {
                        // For Arabic, you might want to use a helper function for Arabic date
                        $formattedDate = date('d F Y h:i a', strtotime($orderDate));
                    } else {
                        $formattedDate = date('d F Y h:i a', strtotime($orderDate));
                    }
                    $status = $locale == 'ar' ? $order->statusAR : $order->status;
                    $total = $order->total * $order->currencyrate;
                    $currencyCode = $order->currencycode;
                    $decimal = $currencyCode == 'KWD' ? 0 : 0; // Using 0 as per user's requirement
                    $formattedTotal = number_format($total, $decimal) . ' ' . __($currencyCode);
                @endphp
                <div class="order-row">
                    <div class="row">
                        <div class="order-col">
                            <h4>{{ __('ORDER #') }}</h4>
                            <p>{{ $orderno }}</p>
                        </div>
                        <div class="order-col">
                            <h4>{{ __('DATE') }}</h4>
                            <p>{{ $formattedDate }}</p>
                        </div>
                        <div class="order-col qty-col">
                            <h4>{{ __('QTY') }}</h4>
                            <p>{{ $order->itemqty }}</p>
                        </div>
                        <div class="order-col">
                            <h4>{{ __('ORDER TOTAL') }}</h4>
                            <p>{{ $formattedTotal }}</p>
                        </div>
                        <div class="order-col">
                            <h4>{{ __('STATUS') }}</h4>
                            <p class="{{ $order->classname }}">
                                {{ $status }}
                                @if($order->status == 'Pending')
                                <a class="cancel-link" href="{{ url('/' . $locale . '/cancelorder/' . $orderno) }}">{{ __('Cancel Order') }}</a>
                                @endif
                            </p>
                        </div>
                    </div>
                    <a class="order-link" href="{{ url('/' . $locale . '/orderdetails/' . $orderno) }}">
                        <svg class="icon icon-arrow-right">
                            <use xlink:href="/static/images/symbol-defs.svg#icon-arrow-right"></use>
                        </svg>
                    </a>
                </div>
                @endforeach
                @if($totalorders > 5)
                <div class="text-center">
                    <ul class="pagination">
                        @for($i = 1; $i <= $noofpage; $i++)
                        <li class="{{ $i == $currentpage ? 'active' : '' }}">
                            <a href="{{ url('/' . $locale . '/myorders?page=' . $i) }}" id="{{ $i }}">{{ $i }}</a>
                        </li>
                        @endfor
                    </ul>
                </div>
                @endif
            </div>
            @else
            <div class="my-orders">
                <div class="no-results">
                    <h3>{{ __("We couldn't find any orders!") }}</h3>
                </div>
            </div>
            @endif
        </div>
    </div>
</main>
@endsection

