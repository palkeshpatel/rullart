<div class="modal fade" id="countryViewModal" tabindex="-1" aria-labelledby="countryViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="countryViewModalLabel">Country Details - {{ $country->countryname }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Country ID:</th>
                                <td>{{ $country->countryid }}</td>
                            </tr>
                            <tr>
                                <th>Country Name (EN):</th>
                                <td>{{ $country->countryname }}</td>
                            </tr>
                            <tr>
                                <th>Country Name (AR):</th>
                                <td>{{ $country->countrynameAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>ISO Code:</th>
                                <td>{{ $country->isocode ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Currency Code:</th>
                                <td>{{ $country->currencycode ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Currency Rate:</th>
                                <td>{{ number_format($country->currencyrate ?? 0, 6) }}</td>
                            </tr>
                            <tr>
                                <th>Currency Symbol:</th>
                                <td>{{ $country->currencysymbol ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Shipping Charge:</th>
                                <td>{{ number_format($country->shipping_charge ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Free Shipping Over:</th>
                                <td>{{ number_format($country->free_shipping_over ?? 0, 3) }}</td>
                            </tr>
                            <tr>
                                <th>Shipping Days:</th>
                                <td>{{ $country->shipping_days ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Shipping Days (AR):</th>
                                <td>{{ $country->shipping_daysAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($country->isactive)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-country-btn" data-country-id="{{ $country->countryid }}">
                    <i class="ti ti-edit me-1"></i> Edit Country
                </button>
            </div>
        </div>
    </div>
</div>

