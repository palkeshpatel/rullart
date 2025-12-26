<table class="table table-bordered table-striped table-hover" id="countriesTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="countryid">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="countryname">
                    Country Name(EN) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="countrynameAR">
                    Country Name(AR) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="isocode">
                    ISO Code <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="currencycode">
                    Currency Code <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="currencyrate">
                    Currency Rate <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="shipping_charge">
                    Shipping Charge <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($countries as $country)
            <tr>
                <td>{{ $country->countryid }}</td>
                <td>{{ $country->countryname }}</td>
                <td>{{ $country->countrynameAR }}</td>
                <td>{{ $country->isocode }}</td>
                <td>{{ $country->currencycode }}</td>
                <td>{{ number_format($country->currencyrate, 6) }}</td>
                <td>{{ number_format($country->shipping_charge ?? 0, 2) }}</td>
                <td>{{ $country->isactive ? 'Yes' : 'No' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-country-btn" 
                           title="View" data-country-id="{{ $country->countryid }}">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-country-btn" 
                           title="Edit" data-country-id="{{ $country->countryid }}">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-country-btn" 
                           title="Delete" data-country-id="{{ $country->countryid }}" 
                           data-country-name="{{ $country->countryname }}">
                            <i class="ti ti-trash fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No countries found</td>
            </tr>
        @endforelse
    </tbody>
</table>

