<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    /**
     * Get category products with filters
     * GET /{locale}/api/category/data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function data(Request $request)
    {
        // TODO: Implement based on CI Category::data_get()
        // Reference: /ruralt-ci/application/controllers/api4/Category.php
        
        return $this->error('Not implemented yet. Please implement based on CI Category controller.');
    }
}
