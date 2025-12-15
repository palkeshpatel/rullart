<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $query = Country::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('country', 'like', "%{$search}%")
                  ->orWhere('countryAR', 'like', "%{$search}%")
                  ->orWhere('countrycode', 'like', "%{$search}%");
            });
        }

        // Filter by published status
        if ($request->has('published') && $request->published !== '') {
            $query->where('ispublished', $request->published);
        }

        $countries = $query->orderBy('countryid', 'asc')->paginate(25);

        return view('admin.masters.countries', compact('countries'));
    }
}
