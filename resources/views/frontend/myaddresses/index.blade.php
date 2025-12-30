@extends('frontend.layouts.app')

@section('content')
<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('My Addresses') }}</li>
        </ol>
        <h1><span><span class="before-icon"></span>{{ __('My Addresses') }}<span class="after-icon"></span></span></h1>
    </div>
    <div class="inside-content">
        <div class="container">
            <div class="my-addresses">
                <div class="address-list">
                    @php
                        $cnt = 1;
                        $kuwaitCountryId = 1; // Assuming Kuwait country ID is 1
                        $qatarCountryId = 7; // Assuming Qatar country ID is 7
                    @endphp
                    @foreach($addresses as $address)
                        @php
                            $area = $locale == 'ar' ? $address->areanameAR : $address->areaname;
                            $country = $locale == 'ar' ? $address->countrynameAR : $address->countryname;
                            $shipping = $address->firstname . ' ' . $address->lastname;
                            
                            if($area != "") {
                                $shipping .= "<br>" . __('Area') . " : " . $area;
                            }
                            if($address->block_number != "") {
                                $shipping .= "<br>" . __('Block') . " : " . $address->block_number;
                            }
                            if($address->street_number != "") {
                                $shipping .= "<br>" . __('Street') . " : " . $address->street_number;
                            }
                            if($address->avenue_number != "") {
                                $shipping .= "<br>" . __('Avenue') . " : " . $address->avenue_number;
                            }
                            if($address->house_number != "") {
                                $shipping .= "<br>" . __('House / Building') . " : " . $address->house_number;
                            }
                            if($address->floor_number != "") {
                                $shipping .= "<br>" . __('Floor Number') . " : " . $address->floor_number;
                            }
                            if($address->flat_number != "") {
                                $shipping .= "<br>" . __('Flat Number') . " : " . $address->flat_number;
                            }
                            if($address->address != "") {
                                $shipping .= "<br>" . __('Additional details') . " : " . nl2br($address->address);
                            }
                            
                            if($address->countryid != $kuwaitCountryId) {
                                if($address->city != "") {
                                    $shipping .= "<br>" . __('City') . " : " . nl2br($address->city);
                                }
                            }
                            
                            $shipping .= "<br>" . __('Country') . " : " . $country;
                            
                            if($address->countryid == $qatarCountryId && $address->securityid != "") {
                                $shipping .= "<br>" . __('Civil ID Number') . " : " . $address->securityid;
                            }
                            
                            $shipping .= "<br>" . __('Tel/Mobile') . " : " . $address->mobile;
                            
                            if(count($addresses) > 0 && $cnt == 1) {
                                echo '<div class="row">';
                            }
                        @endphp
                        <div class="col-sm-6">
                            <div class="address-item">
                                <h4>{{ $address->title }}</h4>
                                <p>{!! $shipping !!}</p>
                                <a href="{{ url('/' . $locale . '/editaddress/' . $address->addressid) }}">{{ __('Edit Address') }}</a>
                                &nbsp;&nbsp;&nbsp;<a href="#" class="removeAddress" data-id="{{ $address->addressid }}">{{ __('Remove Address') }}</a>
                            </div>
                        </div>
                        @php
                            if($cnt % 2 == 0) {
                                echo '</div><div class="row">';
                            }
                            $cnt++;
                        @endphp
                    @endforeach
                    @if(count($addresses) > 0 && $cnt >= 1)
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-sm-6">
                            <a class="btn btn-outline-primary" href="{{ url('/' . $locale . '/addnewaddress') }}">{{ __('ADD NEW ADDRESS') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="{{ $resourceUrl ?? url('/resources/') . '/' }}scripts/myaddresses.js?v=0.9"></script>
@endsection

