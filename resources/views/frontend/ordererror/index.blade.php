@extends('frontend.layouts.app')

@section('content')
<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('Payment') }}</li>
        </ol>
        <h1><span><span class="before-icon"></span>{{ __('Payment Incomplete') }}<span class="after-icon"></span></span></h1>
    </div>
    <div class="inside-content">
        <div class="container">
            <div class="article text-center">
                <table width="100%" cellspacing="1" cellpadding="1">
                    <tr>
                        <td align="center" class="msg">
                            <div class="error">
                                {{ __('Transaction was not successful') }}<br><br>
                                {{ __('Your Order was not completed.') }} {{ __('Please try again later.') }}<br><br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <table width="70%" border="0" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC" col="2">
                                <tr>
                                    <td colspan="2" align="center" class="msg">
                                        <strong class="text">{{ __('Transaction Details') }}</strong><br><br>
                                    </td>
                                </tr>
                                @if ($paymentid != "")
                                <tr>
                                    <td width="26%" class="tdfixed">{{ __('Payment ID') }} :</td>
                                    <td width="74%" class="tdwhite">{{ $paymentid }}</td>
                                </tr>
                                @endif
                                @if ($result != "")
                                <tr>
                                    <td width="26%" class="tdfixed">{{ __('Result') }} :</td>
                                    <td width="74%" class="tdwhite">{{ $result }}</td>
                                </tr>
                                @endif
                                @if ($trackid != "")
                                <tr>
                                    <td width="26%" class="tdfixed">{{ __('Track ID') }} :</td>
                                    <td width="74%" class="tdwhite">{{ $trackid }}</td>
                                </tr>
                                @endif
                                @if ($errorText != "")
                                <tr>
                                    <td width="26%" class="tdfixed">{{ __('Error Message') }} :</td>
                                    <td width="74%" class="tdwhite">{{ $errorText }}</td>
                                </tr>
                                @endif
                                @if ($amt != "")
                                <tr>
                                    <td width="26%" class="tdfixed">{{ __('Amount') }} :</td>
                                    <td width="74%" class="tdwhite">{{ number_format($amt, 3) }} {{ $currencyCode ?? 'KWD' }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="tdfixed">&nbsp;</td>
                                    <td class="tdwhite">
                                        <a href="{{ route('home', ['locale' => $locale]) }}" class="btn btn-primary">{{ __('Continue Shopping') }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

