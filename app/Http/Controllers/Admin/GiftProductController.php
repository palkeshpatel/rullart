<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GiftProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('isgift', 1);

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('fkcategoryid', $request->category);
        }

        // Filter by published status
        if ($request->has('published') && $request->published !== '') {
            $query->where('ispublished', $request->published);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('titleAR', 'like', "%{$search}%")
                  ->orWhere('productcode', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $products = $query->orderBy('productid', 'desc')->paginate($perPage);

        // Get categories for dropdown
        $categories = Category::orderBy('category')->get();

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.gift-products-table', compact('products'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $products])->render(),
            ]);
        }

        return view('admin.gift-products.index', compact('products', 'categories'));
    }

    public function create(Request $request)
    {
        // Get categories for dropdown
        $categories = Category::orderBy('category')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products.partials.gift-product-form', ['product' => null, 'categories' => $categories])->render(),
            ]);
        }

        return view('admin.gift-products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fkcategoryid' => 'required|integer|exists:category,categoryid',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'title')
            ],
            'titleAR' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'titleAR')
            ],
            'productcode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'productcode')
            ],
            'shortdescr' => 'required|string|max:1800',
            'shortdescrAR' => 'required|string|max:1800',
            'longdescr' => 'nullable|string',
            'longdescrAR' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'sellingprice' => 'required|numeric|min:0',
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|string|max:500',
            'videoposter' => 'nullable|string|max:500',
            'ispublished' => 'nullable',
            'isnew' => 'nullable',
            'ispopular' => 'nullable',
            'internation_ship' => 'nullable',
            'productcategoryid' => 'nullable|integer',
            'productcategoryid2' => 'nullable|integer',
            'productcategoryid3' => 'nullable|integer',
        ], [
            'fkcategoryid.required' => 'Category is required.',
            'fkcategoryid.exists' => 'Selected category does not exist.',
            'title.required' => 'Product title (EN) is required.',
            'title.unique' => 'This product title (EN) already exists.',
            'titleAR.required' => 'Product title (AR) is required.',
            'titleAR.unique' => 'This product title (AR) already exists.',
            'productcode.required' => 'Product code is required.',
            'productcode.unique' => 'This product code already exists.',
            'shortdescr.required' => 'Short description (EN) is required.',
            'shortdescrAR.required' => 'Short description (AR) is required.',
            'price.required' => 'Price is required.',
            'sellingprice.required' => 'Selling price is required.',
        ]);

        // Handle boolean fields - Gift products always have isgift = 1
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['isnew'] = $request->has('isnew') ? 1 : 0;
        $validated['ispopular'] = $request->has('ispopular') ? 1 : 0;
        $validated['isgift'] = 1; // Always set to 1 for gift products
        $validated['internation_ship'] = $request->has('internation_ship') ? 1 : 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Create uploads directory if it doesn't exist
        $uploadPath = public_path('uploads/products');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Handle file uploads (photo1-5)
        for ($i = 1; $i <= 5; $i++) {
            if ($request->hasFile("photo{$i}")) {
                $photo = $request->file("photo{$i}");
                $photoName = time() . '_' . uniqid() . '_photo' . $i . '.' . $photo->getClientOriginalExtension();
                $photo->move($uploadPath, $photoName);
                $validated["photo{$i}"] = $photoName;
            } else {
                $validated["photo{$i}"] = '';
            }
        }

        try {
            $product = Product::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift product created successfully',
                    'data' => $product
                ]);
            }

            return redirect()->route('admin.gift-products')
                ->with('success', 'Gift product created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while saving the gift product.';
            $errorField = 'productcode';

            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'productcode') !== false) {
                    $errorMessage = 'This product code already exists. Please choose a different code.';
                    $errorField = 'productcode';
                } elseif (strpos($e->getMessage(), 'title') !== false && strpos($e->getMessage(), 'titleAR') === false) {
                    $errorMessage = 'This product title (EN) already exists. Please choose a different title.';
                    $errorField = 'title';
                } elseif (strpos($e->getMessage(), 'titleAR') !== false) {
                    $errorMessage = 'This product title (AR) already exists. Please choose a different title.';
                    $errorField = 'titleAR';
                } else {
                    $errorMessage = 'A database constraint violation occurred. Please check your input.';
                }
            } else {
                Log::error('Gift product creation error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while saving the gift product: ' . $e->getMessage();
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => [$errorField => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors([$errorField => $errorMessage])->withInput();
        } catch (\Exception $e) {
            Log::error('Gift product creation error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                    'errors' => ['general' => ['An unexpected error occurred.']]
                ], 422);
            }

            return back()->withErrors(['general' => 'An unexpected error occurred.'])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $product = Product::with('category')->where('isgift', 1)->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products.partials.gift-product-view', compact('product'))->render(),
            ]);
        }

        return view('admin.gift-products.show', compact('product'));
    }

    public function edit(Request $request, $id)
    {
        $product = Product::where('isgift', 1)->findOrFail($id);
        $categories = Category::orderBy('category')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products.partials.gift-product-form', ['product' => $product, 'categories' => $categories])->render(),
            ]);
        }

        return view('admin.gift-products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('isgift', 1)->findOrFail($id);

        // Fix for PUT requests with FormData
        if (
            $request->isMethod('put') && empty($request->all()) &&
            $request->header('Content-Type') &&
            str_contains($request->header('Content-Type'), 'multipart/form-data')
        ) {
            $content = $request->getContent();
            $boundary = null;

            if (preg_match('/boundary=([^;]+)/', $request->header('Content-Type'), $matches)) {
                $boundary = '--' . trim($matches[1]);
            }

            if ($boundary && $content) {
                $parts = explode($boundary, $content);
                $parsedData = [];

                foreach ($parts as $part) {
                    if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*?)(?=\r?\n--|$)/s', $part, $matches)) {
                        $fieldName = $matches[1];
                        $fieldValue = trim($matches[2], "\r\n");

                        if ($fieldName !== '_method') {
                            $parsedData[$fieldName] = $fieldValue;
                        }
                    }
                }

                if (!empty($parsedData)) {
                    $request->merge($parsedData);
                }
            }
        }

        $validated = $request->validate([
            'fkcategoryid' => 'required|integer|exists:category,categoryid',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'title')->ignore($product->productid, 'productid')
            ],
            'titleAR' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'titleAR')->ignore($product->productid, 'productid')
            ],
            'productcode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'productcode')->ignore($product->productid, 'productid')
            ],
            'shortdescr' => 'required|string|max:1800',
            'shortdescrAR' => 'required|string|max:1800',
            'longdescr' => 'nullable|string',
            'longdescrAR' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'sellingprice' => 'required|numeric|min:0',
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|string|max:500',
            'videoposter' => 'nullable|string|max:500',
            'ispublished' => 'nullable',
            'isnew' => 'nullable',
            'ispopular' => 'nullable',
            'internation_ship' => 'nullable',
            'productcategoryid' => 'nullable|integer',
            'productcategoryid2' => 'nullable|integer',
            'productcategoryid3' => 'nullable|integer',
        ], [
            'fkcategoryid.required' => 'Category is required.',
            'fkcategoryid.exists' => 'Selected category does not exist.',
            'title.required' => 'Product title (EN) is required.',
            'title.unique' => 'This product title (EN) already exists.',
            'titleAR.required' => 'Product title (AR) is required.',
            'titleAR.unique' => 'This product title (AR) already exists.',
            'productcode.required' => 'Product code is required.',
            'productcode.unique' => 'This product code already exists.',
            'shortdescr.required' => 'Short description (EN) is required.',
            'shortdescrAR.required' => 'Short description (AR) is required.',
            'price.required' => 'Price is required.',
            'sellingprice.required' => 'Selling price is required.',
        ]);

        // Handle boolean fields - Gift products always have isgift = 1
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['isnew'] = $request->has('isnew') ? 1 : 0;
        $validated['ispopular'] = $request->has('ispopular') ? 1 : 0;
        $validated['isgift'] = 1; // Always set to 1 for gift products
        $validated['internation_ship'] = $request->has('internation_ship') ? 1 : 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Create uploads directory if it doesn't exist
        $uploadPath = public_path('uploads/products');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Handle file uploads (only if new files are uploaded)
        for ($i = 1; $i <= 5; $i++) {
            if ($request->hasFile("photo{$i}")) {
                // Delete old photo if exists
                if ($product->{"photo{$i}"} && file_exists(public_path('uploads/products/' . $product->{"photo{$i}"}))) {
                    unlink(public_path('uploads/products/' . $product->{"photo{$i}"}));
                }
                $photo = $request->file("photo{$i}");
                $photoName = time() . '_' . uniqid() . '_photo' . $i . '.' . $photo->getClientOriginalExtension();
                $photo->move($uploadPath, $photoName);
                $validated["photo{$i}"] = $photoName;
            } else {
                // Keep existing photo if not uploading new one
                $validated["photo{$i}"] = $product->{"photo{$i}"} ?? '';
            }
        }

        try {
            $product->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift product updated successfully',
                    'data' => $product
                ]);
            }

            return redirect()->route('admin.gift-products')
                ->with('success', 'Gift product updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while updating the gift product.';
            $errorField = 'productcode';

            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'productcode') !== false) {
                    $errorMessage = 'This product code already exists. Please choose a different code.';
                    $errorField = 'productcode';
                } elseif (strpos($e->getMessage(), 'title') !== false && strpos($e->getMessage(), 'titleAR') === false) {
                    $errorMessage = 'This product title (EN) already exists. Please choose a different title.';
                    $errorField = 'title';
                } elseif (strpos($e->getMessage(), 'titleAR') !== false) {
                    $errorMessage = 'This product title (AR) already exists. Please choose a different title.';
                    $errorField = 'titleAR';
                } else {
                    $errorMessage = 'A database constraint violation occurred. Please check your input.';
                }
            } else {
                Log::error('Gift product update error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while updating the gift product: ' . $e->getMessage();
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => [$errorField => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors([$errorField => $errorMessage])->withInput();
        } catch (\Exception $e) {
            Log::error('Gift product update error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                    'errors' => ['general' => ['An unexpected error occurred.']]
                ], 422);
            }

            return back()->withErrors(['general' => 'An unexpected error occurred.'])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::where('isgift', 1)->findOrFail($id);

        try {
            // Delete photos if they exist
            for ($i = 1; $i <= 5; $i++) {
                $photoField = "photo{$i}";
                if ($product->$photoField && file_exists(public_path('uploads/products/' . $product->$photoField))) {
                    unlink(public_path('uploads/products/' . $product->$photoField));
                }
            }

            // Instead of deleting, we can unset the isgift flag, or actually delete
            // For now, let's just remove the isgift flag so it's no longer a gift product
            $product->update(['isgift' => 0]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift product removed successfully'
                ]);
            }

            return redirect()->route('admin.gift-products')
                ->with('success', 'Gift product removed successfully');
        } catch (\Exception $e) {
            Log::error('Gift product deletion error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while removing the gift product.'
                ], 422);
            }

            return back()->withErrors(['error' => 'An error occurred while removing the gift product.']);
        }
    }
}
