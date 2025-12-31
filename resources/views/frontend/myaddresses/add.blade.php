@extends('frontend.layouts.app')

@section('content')
<main class="inside">
    <div class="inside-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('myaddresses', ['locale' => $locale]) }}">{{ __('My Addresses') }}</a></li>
            <li class="breadcrumb-item active">{{ __('Add New Address') }}</li>
        </ol>
        <h1><span><span class="before-icon"></span>{{ __('Add New Address') }}<span class="after-icon"></span></span></h1>
    </div>
    <div class="inside-content">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <form id="formAddAddress" name="formAddAddress">
                        @csrf
                        <div class="form-group">
                            <label for="firstname">{{ __('First Name') }} *</label>
                            <input class="form-control required" name="firstname" id="firstname" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">{{ __('Last Name') }} *</label>
                            <input class="form-control required" name="lastname" id="lastname" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="mobile">{{ __('Tel/Mobile') }} *</label>
                            <input class="form-control required" name="mobile" id="mobile" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="addressTitle">{{ __('Address Title') }} *</label>
                            <input class="form-control required" name="addressTitle" id="addressTitle" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="country">{{ __('Country') }} *</label>
                            <select id="country" name="country" class="form-control required" required>
                                @foreach($countries as $country)
                                    <option value="{{ $country->countryname }}" {{ $country->countryname == config('app.default_country', 'Kuwait') ? 'selected' : '' }}>
                                        {{ $locale == 'ar' ? ($country->countrynameAR ?? $country->countryname) : $country->countryname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divArea">
                            <label for="area">{{ __('Area') }} *</label>
                            <select id="area" name="area" class="form-control required" required>
                                <option value="">{{ __('Select') }}</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->areaid }}">
                                        {{ $locale == 'ar' ? ($area->areanameAR ?? $area->areaname) : $area->areaname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="divCity" style="display:none;">
                            <label for="city">{{ __('City') }} *</label>
                            <input class="form-control" name="city" id="city" value="">
                        </div>
                        <div class="form-group">
                            <label for="block_number">{{ __('Block') }} *</label>
                            <input class="form-control required" name="block_number" id="block_number" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="street_number">{{ __('Street') }} *</label>
                            <input class="form-control required" name="street_number" id="street_number" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="house_number">{{ __('House / Building') }} *</label>
                            <input class="form-control required" name="house_number" id="house_number" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="floor_number">{{ __('Floor Number') }}</label>
                            <input class="form-control" name="floor_number" id="floor_number" value="">
                        </div>
                        <div class="form-group">
                            <label for="flat_number">{{ __('Flat Number') }}</label>
                            <input class="form-control" name="flat_number" id="flat_number" value="">
                        </div>
                        <div class="form-group" id="divSecurityID" style="display:none;">
                            <label for="securityid">{{ __('Civil ID Number') }}</label>
                            <input class="form-control" name="securityid" id="securityid" value="">
                        </div>
                        <div class="form-group">
                            <label for="address">{{ __('Additional details') }}</label>
                            <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                        </div>
                        <div class="alert alert-danger print-error-msg" style="display:none"></div>
                        <div class="form-group">
                            <button type="submit" id="btnSaveAddress" class="btn btn-primary">{{ __('Save Address') }}</button>
                            <a href="{{ route('myaddresses', ['locale' => $locale]) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="{{ $resourceUrl ?? url('/resources/') . '/' }}scripts/checkout.js?v=0.9"></script>
<script>
$(document).ready(function() {
    $('#formAddAddress').validate({
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

    $("#country").on('change', function(e) {
        var country = $(this).val();
        if (country == 'Qatar') {
            $("#divSecurityID").show();
        } else {
            $("#divSecurityID").hide();
            $("#securityid").val('');
        }
        
        if (country == 'Kuwait') {
            fillArea(country, '');
            $("#divCity").hide();
            $("#divArea").show();
        } else {
            $("#divCity").show();
            $("#divArea").hide();
        }
    });

    $("#formAddAddress").on('submit', function(e) {
        e.preventDefault();
        
        if ($('#formAddAddress').valid()) {
            var btn = $("#btnSaveAddress");
            var text = btn.text();
            btn.text('{{ __("Please wait...") }}');
            btn.attr('disabled', true);
            
            var formData = new FormData($('#formAddAddress')[0]);
            $.ajax({
                url: "{{ route('addnewaddress.save', ['locale' => $locale]) }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "JSON",
                success: function(data) {
                    if (data.status == true) {
                        $(".print-error-msg").css('display', 'none');
                        if (data.redirect) {
                            location.href = data.redirect;
                        } else {
                            location.href = "{{ route('myaddresses', ['locale' => $locale]) }}";
                        }
                    } else {
                        $(".print-error-msg").css('display', 'block');
                        $(".print-error-msg").html(data.msg);
                        btn.text(text);
                        btn.attr('disabled', false);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $(".print-error-msg").css('display', 'block');
                    $(".print-error-msg").html('{{ __("An error occurred. Please try again.") }}');
                    btn.text(text);
                    btn.attr('disabled', false);
                }
            });
        }
    });
});

function fillArea(country, fkareaid) {
    var mySelect = $('#area');
    mySelect.empty();
    mySelect.append($('<option></option>').val('').html('{{ __("Select") }}'));
    $.ajax({
        url: base_url + "areas/getdata",
        data: {
            country: country
        },
        type: "POST",
        dataType: "JSON",
        success: function(data) {
            if (data != 'FALSE') {
                $.each(data, function(key, value) {
                    mySelect.append($('<option></option>').val(value.areaid).html(value.areaname));
                });
                if (fkareaid) {
                    $("#area").val(fkareaid);
                }
            }
        }
    });
}
</script>
@endsection

