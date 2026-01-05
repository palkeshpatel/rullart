<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;
use Exception;

class GiftProduct4Controller extends Controller
{
    use ImageUploadTrait;
    
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Get categories for dropdown
        $categories = Category::orderBy('category')->get();

        // Return view for initial page load
        return view('admin.gift-products4.index', compact('categories'));
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            $countQuery = Product::whereNotNull('productcategoryid4')->where('productcategoryid4', '>', 0);
            $totalRecords = $countQuery->count();

            $query = Product::with('category')
                ->whereNotNull('productcategoryid4')
                ->where('productcategoryid4', '>', 0)
                ->select('products.*')
                ->selectRaw('IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=products.productid AND filtercode=\'size\'), 0) as quantity');
            $filteredCountQuery = Product::whereNotNull('productcategoryid4')->where('productcategoryid4', '>', 0);

            // Filter by category
            $category = $request->input('category');
            if (!empty($category)) {
                $query->where('fkcategoryid', $category);
                $filteredCountQuery->where('fkcategoryid', $category);
            }

            // Filter by published status
            $published = $request->input('published');
            if ($published !== null && $published !== '' && ($published === '0' || $published === '1')) {
                $publishedValue = (int)$published;
                $query->where('ispublished', $publishedValue);
                $filteredCountQuery->where('ispublished', $publishedValue);
            }

            $filteredCount = $filteredCountQuery->count();

            // DataTables search
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('titleAR', 'like', "%{$searchValue}%")
                        ->orWhere('productcode', 'like', "%{$searchValue}%");
                });

                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('titleAR', 'like', "%{$searchValue}%")
                        ->orWhere('productcode', 'like', "%{$searchValue}%");
                });
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['productcode', 'title', 'sellingprice', 'fkcategoryid', 'photo1', 'quantity', 'ispublished', 'updateddate', 'productid'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'productid';
            
            if ($orderColumn === 'fkcategoryid') {
                $query->join('category as c_sort', 'c_sort.categoryid', '=', 'products.fkcategoryid');
                $query->orderBy('c_sort.category', $orderDir);
            } else {
                $query->orderBy('products.' . $orderColumn, $orderDir);
            }

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $products = $query->skip($start)->take($length)->get();

            // Format data
            $data = [];
            foreach ($products as $product) {
                $photoUrl = $product->photo1 ? asset('storage/' . $product->photo1) : null;
                $data[] = [
                    'productcode' => $product->productcode ?? '',
                    'title' => $product->title ?? '',
                    'sellingprice' => number_format($product->sellingprice ?? 0, 3),
                    'category' => $product->category->category ?? 'N/A',
                    'photo' => $photoUrl ? '<img src="' . $photoUrl . '" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">' : '<span class="text-muted">-</span>',
                    'quantity' => $product->quantity ?? 0,
                    'isactive' => $product->ispublished ? 'Yes' : 'No',
                    'updateddate' => $product->updateddate ? \Carbon\Carbon::parse($product->updateddate)->format('d/M/Y') : 'N/A',
                    'action' => $product->productid
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('GiftProduct4 DataTables Error: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data.'
            ], 500);
        }
    }

    public function create(Request $request)
    {
        // Get categories for dropdown
        $categories = Category::orderBy('category')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products4.partials.gift-product4-form', ['product' => null, 'categories' => $categories])->render(),
            ]);
        }

        return view('admin.gift-products4.create', compact('categories'));
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
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo4' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo5' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|string|max:500',
            'videoposter' => 'nullable|string|max:500',
            'ispublished' => 'nullable',
            'isnew' => 'nullable',
            'ispopular' => 'nullable',
            'internation_ship' => 'nullable',
            'productcategoryid' => 'nullable|integer',
            'productcategoryid2' => 'nullable|integer',
            'productcategoryid3' => 'nullable|integer',
            'productcategoryid4' => 'required|integer|exists:category,categoryid',
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
            'productcategoryid4.required' => 'Gift Product Category 4 is required.',
            'productcategoryid4.exists' => 'Selected Gift Product Category 4 does not exist.',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['isnew'] = $request->has('isnew') ? 1 : 0;
        $validated['ispopular'] = $request->has('ispopular') ? 1 : 0;
        $validated['isgift'] = 0; // Gift Products 4 are identified by productcategoryid4, not isgift flag
        $validated['internation_ship'] = $request->has('internation_ship') ? 1 : 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Handle file uploads (photo1-5) using trait
        for ($i = 1; $i <= 5; $i++) {
            if ($request->hasFile("photo{$i}")) {
                $validated["photo{$i}"] = $this->uploadImage($request->file("photo{$i}"), null, 'product');
            } else {
                $validated["photo{$i}"] = '';
            }
        }

        try {
            $product = Product::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift Product 4 created successfully',
                    'data' => $product
                ]);
            }

            return redirect()->route('admin.gift-products4')
                ->with('success', 'Gift Product 4 created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = 'An error occurred while saving the gift product 4.';
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
                Log::error('Gift product 4 creation error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while saving the gift product 4: ' . $e->getMessage();
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
            Log::error('Gift product 4 creation error: ' . $e->getMessage());

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
        $product = Product::with('category')
            ->whereNotNull('productcategoryid4')
            ->where('productcategoryid4', '>', 0)
            ->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products4.partials.gift-product4-view', compact('product'))->render(),
            ]);
        }

        return view('admin.gift-products4.show', compact('product'));
    }

    public function edit(Request $request, $id)
    {
        $product = Product::whereNotNull('productcategoryid4')
            ->where('productcategoryid4', '>', 0)
            ->findOrFail($id);
        $categories = Category::orderBy('category')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products4.partials.gift-product4-form', ['product' => $product, 'categories' => $categories])->render(),
            ]);
        }

        return view('admin.gift-products4.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::whereNotNull('productcategoryid4')
            ->where('productcategoryid4', '>', 0)
            ->findOrFail($id);

        // Fix for PUT requests with FormData
        if (
            $request->isMethod('put') &&
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
                $parsedFiles = [];

                foreach ($parts as $index => $part) {
                    $part = trim($part);
                    if (empty($part) || $part === '--') {
                        continue;
                    }

                    if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)";\s*filename="([^"]+)"(?:\s*\r?\nContent-Type:\s*([^\r\n]+))?\s*\r?\n\r?\n(.*)/s', $part, $fileMatches)) {
                        $fieldName = $fileMatches[1];
                        $fileName = $fileMatches[2];
                        $contentType = isset($fileMatches[3]) && !empty($fileMatches[3]) ? trim($fileMatches[3]) : 'application/octet-stream';
                        $fileContent = $fileMatches[4];
                        
                        $fileContent = preg_replace('/\r?\n--.*$/s', '', $fileContent);
                        $fileContent = rtrim($fileContent, "\r\n");

                        if (strlen($fileContent) > 0) {
                            $tempFile = tempnam(sys_get_temp_dir(), 'laravel_upload_');
                            file_put_contents($tempFile, $fileContent);

                            $uploadedFile = new UploadedFile(
                                $tempFile,
                                $fileName,
                                $contentType,
                                null,
                                true
                            );

                            $parsedFiles[$fieldName] = $uploadedFile;
                        }
                    }
                    elseif (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*)/s', $part, $textMatches)) {
                        $fieldName = $textMatches[1];
                        $fieldValue = $textMatches[2];
                        
                        $fieldValue = preg_replace('/\r?\n--.*$/s', '', $fieldValue);
                        $fieldValue = trim($fieldValue, "\r\n");

                        if ($fieldName !== '_method') {
                            $parsedData[$fieldName] = $fieldValue;
                        }
                    }
                }

                if (!empty($parsedData)) {
                    $request->merge($parsedData);
                }

                if (!empty($parsedFiles)) {
                    foreach ($parsedFiles as $key => $file) {
                        $request->files->set($key, $file);
                    }
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
            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo4' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo5' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|string|max:500',
            'videoposter' => 'nullable|string|max:500',
            'ispublished' => 'nullable',
            'isnew' => 'nullable',
            'ispopular' => 'nullable',
            'internation_ship' => 'nullable',
            'productcategoryid' => 'nullable|integer',
            'productcategoryid2' => 'nullable|integer',
            'productcategoryid3' => 'nullable|integer',
            'productcategoryid4' => 'required|integer|exists:category,categoryid',
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
            'productcategoryid4.required' => 'Gift Product Category 4 is required.',
            'productcategoryid4.exists' => 'Selected Gift Product Category 4 does not exist.',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['isnew'] = $request->has('isnew') ? 1 : 0;
        $validated['ispopular'] = $request->has('ispopular') ? 1 : 0;
        $validated['isgift'] = 0; // Gift Products 4 are identified by productcategoryid4, not isgift flag
        $validated['internation_ship'] = $request->has('internation_ship') ? 1 : 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Handle file uploads
        for ($i = 1; $i <= 5; $i++) {
            $photoFile = $request->file("photo{$i}");
            
            if (!$photoFile && $request->files->has("photo{$i}")) {
                $photoFile = $request->files->get("photo{$i}");
            }
            
            if ($photoFile && $photoFile->isValid()) {
                $validated["photo{$i}"] = $this->uploadImage($photoFile, $product->{"photo{$i}"}, 'product');
            } else {
                $validated["photo{$i}"] = $product->{"photo{$i}"} ?? '';
            }
        }

        try {
            $product->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift Product 4 updated successfully',
                    'data' => $product
                ]);
            }

            return redirect()->route('admin.gift-products4')
                ->with('success', 'Gift Product 4 updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = 'An error occurred while updating the gift product 4.';
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
                Log::error('Gift product 4 update error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while updating the gift product 4: ' . $e->getMessage();
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
            Log::error('Gift product 4 update error: ' . $e->getMessage());

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
        $product = Product::whereNotNull('productcategoryid4')
            ->where('productcategoryid4', '>', 0)
            ->findOrFail($id);

        try {
            // Delete photos if they exist
            for ($i = 1; $i <= 5; $i++) {
                $photoField = "photo{$i}";
                if ($product->$photoField) {
                    $this->deleteImage($product->$photoField, 'product');
                }
            }

            // Remove productcategoryid4 instead of deleting
            $product->update(['productcategoryid4' => null]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift Product 4 removed successfully'
                ]);
            }

            return redirect()->route('admin.gift-products4')
                ->with('success', 'Gift Product 4 removed successfully');
        } catch (\Exception $e) {
            Log::error('Gift product 4 deletion error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while removing the gift product 4.'
                ], 422);
            }

            return back()->withErrors(['error' => 'An error occurred while removing the gift product 4.']);
        }
    }

    public function removeImage(Request $request, $id)
    {
        $product = Product::whereNotNull('productcategoryid4')
            ->where('productcategoryid4', '>', 0)
            ->findOrFail($id);
        $column = $request->input('column');

        $validColumns = ['photo1', 'photo2', 'photo3', 'photo4', 'photo5'];
        if (!in_array($column, $validColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid column name'
            ], 400);
        }

        $this->removeImageFromModel($product, $column, 'product');

        return response()->json([
            'success' => true,
            'message' => 'Image removed successfully'
        ]);
    }
}

