<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Http\Request;

class RoutingController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Redirector|RedirectResponse|View
    {
        if (Auth::user()) {
            return redirect('/admin/dashboard');
        } else {
            return redirect('/admin/login');
        }
    }

    /**
     * Display a view based on the first route param
     */
    public function root(Request $request, $first): View
    {
        // Use View::make to explicitly specify the view path
        return ViewFacade::make($first);
    }

    /**
     * second level route
     */
    public function secondLevel(Request $request, $first, $second): View
    {
        // For admin routes, use the same view structure without admin prefix
        // The route prefix 'admin' should not affect the view path
        $viewPath = $first . '.' . $second;
        
        // Use View::make to explicitly specify the view path
        return ViewFacade::make($viewPath);
    }

    /**
     * third level route
     */
    public function thirdLevel(Request $request, $first, $second, $third): View
    {
        // Use View::make to explicitly specify the view path
        $viewPath = $first . '.' . $second . '.' . $third;
        return ViewFacade::make($viewPath);
    }
}
