<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

            // Map shortdescr as title to match frontend/CI project behavior
            $query = Product::with('category')
                ->whereNotNull('productcategoryid4')
                ->where('productcategoryid4', '>', 0)
                ->select('products.*')
                ->selectRaw('products.shortdescr as display_title, products.title as display_shortdescr')
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

            // DataTables search - search in shortdescr (product title) and title (short description) to match CI behavior
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('products.shortdescr', 'like', "%{$searchValue}%") // Product title
                        ->orWhere('products.shortdescrAR', 'like', "%{$searchValue}%")
                        ->orWhere('products.title', 'like', "%{$searchValue}%") // Short description
                        ->orWhere('products.titleAR', 'like', "%{$searchValue}%")
                        ->orWhere('products.productcode', 'like', "%{$searchValue}%");
                });

                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('shortdescr', 'like', "%{$searchValue}%") // Product title
                        ->orWhere('shortdescrAR', 'like', "%{$searchValue}%")
                        ->orWhere('title', 'like', "%{$searchValue}%") // Short description
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

            // Format data - Match CI project: shortdescr is displayed as title (product title)
            $data = [];
            foreach ($products as $product) {
                $photoUrl = $product->photo1 ? asset('storage/upload/product/' . $product->photo1) : null;
                $data[] = [
                    'productcode' => $product->productcode ?? '',
                    'title' => $product->display_title ?? $product->shortdescr ?? '', // Use shortdescr as title (matching CI/frontend)
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
        
        // Get colors (filtervalues where fkfilterid = 2)
        $colors = DB::table('filtervalues')
            ->where('fkfilterid', 2)
            ->orderBy('displayorder')
            ->orderBy('filtervalue')
            ->get(['filtervalueid', 'filtervalue', 'filtervalueAR']);
        
        // Get occasions
        $occasions = \App\Models\Occassion::where('ispublished', 1)
            ->orderBy('occassion')
            ->get(['occassionid', 'occassion', 'occassionAR']);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products4.partials.gift-product4-form', [
                    'product' => null, 
                    'categories' => $categories,
                    'colors' => $colors,
                    'occasions' => $occasions,
                    'productFilters' => collect()
                ])->render(),
            ]);
        }

        return view('admin.gift-products4.create', compact('categories', 'colors', 'occasions'));
    }

    public function store(Request $request)
    {
        // Normalize inputs (trim whitespace from productcode, title, titleAR) - same as GiftProductController
        $productcode = trim($request->productcode ?? '');
        $title = trim($request->title ?? '');
        $titleAR = trim(preg_replace('/\s+/u', ' ', $request->titleAR ?? ''));

        $request->merge([
            'productcode' => $productcode,
            'title' => $title,
            'titleAR' => $titleAR,
        ]);

        $validated = $request->validate([
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
            'shortdescr' => 'nullable|string|max:1800',
            'shortdescrAR' => 'nullable|string|max:1800',
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
            'productcategoryid' => 'nullable|integer',
            'productcategoryid2' => 'nullable|integer',
            'productcategoryid3' => 'nullable|integer',
            'productcategoryid4' => 'required|integer|exists:category,categoryid',
            'subcategory1' => 'nullable|integer',
            'subcategory2' => 'nullable|integer',
            'subcategory3' => 'nullable|integer',
            'barcode' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'occasion' => 'nullable|integer|exists:occassion,occassionid',
            'color' => 'nullable|integer|exists:filtervalues,filtervalueid',
        ], [
            'title.required' => 'Product title (EN) is required.',
            'title.unique' => 'This product title (EN) already exists.',
            'titleAR.required' => 'Product title (AR) is required.',
            'titleAR.unique' => 'This product title (AR) already exists.',
            'productcode.required' => 'Product code is required.',
            'productcode.unique' => 'This product code already exists.',
            'price.required' => 'Price is required.',
            'sellingprice.required' => 'Selling Price [KWD] is required.',
            'productcategoryid4.required' => 'Gift Product Category 4 is required.',
            'productcategoryid4.exists' => 'Selected Gift Product Category 4 does not exist.',
        ]);

        // Handle boolean fields - Gift Products 4 are identified by productcategoryid4
        $validated['ispublished'] = 1; // Default to published
        $validated['isnew'] = 0;
        $validated['ispopular'] = 0;
        $validated['isgift'] = 0; // Gift Products 4 are identified by productcategoryid4, not isgift flag
        $validated['internation_ship'] = 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        
        // Use productcategoryid as main category (fkcategoryid) if provided
        $validated['fkcategoryid'] = $request->input('productcategoryid') ?? 0;
        
        // Handle subcategories - map subcategory1 to productcategoryid2, subcategory2 to productcategoryid3
        if ($request->has('subcategory1') && $request->subcategory1) {
            $validated['productcategoryid2'] = $request->subcategory1;
        } elseif ($request->has('productcategoryid2') && $request->productcategoryid2) {
            $validated['productcategoryid2'] = $request->productcategoryid2;
        } else {
            $validated['productcategoryid2'] = 0;
        }
        
        if ($request->has('subcategory2') && $request->subcategory2) {
            $validated['productcategoryid3'] = $request->subcategory2;
        } elseif ($request->has('productcategoryid3') && $request->productcategoryid3) {
            $validated['productcategoryid3'] = $request->productcategoryid3;
        } else {
            $validated['productcategoryid3'] = 0;
        }

        // Handle shortdescr - set to empty string if not provided (NOT NULL constraint)
        $validated['shortdescr'] = $validated['shortdescr'] ?? '';
        $validated['shortdescrAR'] = $validated['shortdescrAR'] ?? '';
        
        // Handle longdescr - set to empty string if not provided (NOT NULL constraint)
        $validated['longdescr'] = $validated['longdescr'] ?? '';
        $validated['longdescrAR'] = $validated['longdescrAR'] ?? '';

        // Handle metatitle - set to empty string if not provided (NOT NULL constraint)
        $validated['metatitle'] = $validated['metatitle'] ?? '';
        $validated['metatitleAR'] = $validated['metatitleAR'] ?? '';
        $validated['metakeyword'] = $validated['metakeyword'] ?? '';
        $validated['metakeywordAR'] = $validated['metakeywordAR'] ?? '';
        $validated['metadescr'] = $validated['metadescr'] ?? '';
        $validated['metadescrAR'] = $validated['metadescrAR'] ?? '';
        
        // Handle sellingprice - set to price if not provided (NOT NULL constraint)
        if (!isset($validated['sellingprice']) || $validated['sellingprice'] === null || $validated['sellingprice'] === '') {
            $validated['sellingprice'] = $validated['price'] ?? 0;
        }
        
        // Handle photo fields - set to empty string if not provided (NOT NULL constraint)
        for ($i = 1; $i <= 5; $i++) {
            if ($request->hasFile("photo{$i}")) {
                $validated["photo{$i}"] = $this->uploadImage($request->file("photo{$i}"), null, 'product');
            } else {
                $validated["photo{$i}"] = '';
            }
        }

        $validated['updatedby'] = Auth::check() ? Auth::id() : 1;
        $validated['updateddate'] = now();

        try {
            DB::beginTransaction();
            
            // Double-check productcode doesn't exist (case-insensitive check)
            $checkProductcode = trim($validated['productcode'] ?? '');
            if (!empty($checkProductcode)) {
                $existingProduct = Product::whereRaw('LOWER(productcode) = ?', [strtolower($checkProductcode)])->first();
                if ($existingProduct) {
                    DB::rollBack();
                    Log::warning('Gift Product 4: Product code already exists (pre-insert check)', [
                        'productcode' => $checkProductcode,
                        'existing_productid' => $existingProduct->productid,
                        'existing_productcode' => $existingProduct->productcode,
                    ]);
                    if ($request->ajax() || $request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This product code already exists. Please choose a different code.',
                            'errors' => ['productcode' => ['This product code already exists. Please choose a different code.']]
                        ], 422);
                    }
                    return back()->withErrors(['productcode' => 'This product code already exists. Please choose a different code.'])->withInput();
                }
            }
            
            // Remove fields that don't belong to products table
            $productData = $validated;
            unset($productData['barcode'], $productData['quantity'], $productData['occasion'], $productData['color']);
            
            Log::info('Gift Product 4: Attempting to create product', [
                'productcode' => $productData['productcode'] ?? 'N/A',
                'title' => $productData['title'] ?? 'N/A',
            ]);
            
            $product = Product::create($productData);
            $product->refresh();
            
            if (!$product || !$product->productid) {
                DB::rollBack();
                throw new \Exception('Failed to create product. Product ID is missing.');
            }
            
            $storeId = 1; // Default store ID
            
            // Save color to productsfilter
            if ($request->has('color') && $request->color) {
                DB::table('productsfilter')->insert([
                    'fkproductid' => $product->productid,
                    'fkfiltervalueid' => $request->color,
                    'filtercode' => 'color',
                    'qty' => 0,
                    'fkstoreid' => $storeId,
                    'barcode' => $request->barcode ?? null,
                ]);
            }
            
            // Save occasion to productsfilter
            if ($request->has('occasion') && $request->occasion) {
                DB::table('productsfilter')->insert([
                    'fkproductid' => $product->productid,
                    'fkfiltervalueid' => $request->occasion,
                    'filtercode' => 'occassion',
                    'qty' => $request->quantity ?? 0,
                    'fkstoreid' => $storeId,
                    'barcode' => $request->barcode ?? null,
                ]);
            }
            
            DB::commit();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift Product 4 created successfully',
                    'data' => $product,
                    'redirect' => route('admin.gift-products4')
                ]);
            }

            return redirect()->route('admin.gift-products4')
                ->with('success', 'Gift Product 4 created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            Log::error('Gift Product 4 QueryException', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'productcode' => $request->input('productcode'),
                'validated_productcode' => isset($validated) ? ($validated['productcode'] ?? 'N/A') : 'N/A',
            ]);
            
            $errorMessage = 'An error occurred while saving the gift product 4.';
            $errorField = 'productcode';

            if ($e->getCode() == 23000) {
                // Check for specific column errors - look for "Column 'X' cannot be null" pattern
                if (preg_match("/Column '(\w+)' cannot be null/i", $e->getMessage(), $matches)) {
                    $column = $matches[1] ?? '';
                    if ($column === 'sellingprice') {
                        $errorMessage = 'Selling price is required.';
                        $errorField = 'sellingprice';
                    } elseif ($column === 'productcode') {
                        $errorMessage = 'Product code is required.';
                        $errorField = 'productcode';
                    } else {
                        $errorMessage = "Column '{$column}' cannot be null. Please provide a value.";
                        $errorField = $column;
                    }
                } elseif (preg_match("/Duplicate entry.*for key.*productcode/i", $e->getMessage()) || 
                          (strpos($e->getMessage(), 'productcode') !== false && strpos($e->getMessage(), 'Duplicate') !== false)) {
                    $errorMessage = 'This product code already exists. Please choose a different code.';
                    $errorField = 'productcode';
                } elseif (preg_match("/Duplicate entry.*for key.*title/i", $e->getMessage()) && strpos($e->getMessage(), 'titleAR') === false) {
                    $errorMessage = 'This product title (EN) already exists. Please choose a different title.';
                    $errorField = 'title';
                } elseif (preg_match("/Duplicate entry.*for key.*titleAR/i", $e->getMessage())) {
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
            DB::rollBack();
            
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
        
        // Get colors (filtervalues where fkfilterid = 2)
        $colors = DB::table('filtervalues')
            ->where('fkfilterid', 2)
            ->orderBy('displayorder')
            ->orderBy('filtervalue')
            ->get(['filtervalueid', 'filtervalue', 'filtervalueAR']);
        
        // Get occasions
        $occasions = \App\Models\Occassion::where('ispublished', 1)
            ->orderBy('occassion')
            ->get(['occassionid', 'occassion', 'occassionAR']);
        
        // Get product filters (color, occasion)
        $productFilters = DB::table('productsfilter')
            ->where('fkproductid', $id)
            ->get()
            ->groupBy('filtercode');

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.gift-products4.partials.gift-product4-form', [
                    'product' => $product, 
                    'categories' => $categories,
                    'colors' => $colors,
                    'occasions' => $occasions,
                    'productFilters' => $productFilters
                ])->render(),
            ]);
        }

        return view('admin.gift-products4.edit', compact('product', 'categories', 'colors', 'occasions', 'productFilters'));
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

        // Normalize inputs (trim whitespace from productcode, title, titleAR) - same as GiftProductController
        $productcode = trim($request->productcode ?? '');
        $title = trim($request->title ?? '');
        $titleAR = trim(preg_replace('/\s+/u', ' ', $request->titleAR ?? ''));

        $request->merge([
            'productcode' => $productcode,
            'title' => $title,
            'titleAR' => $titleAR,
        ]);

        $validated = $request->validate([
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
            'shortdescr' => 'nullable|string|max:1800',
            'shortdescrAR' => 'nullable|string|max:1800',
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
            'productcategoryid' => 'nullable|integer',
            'productcategoryid2' => 'nullable|integer',
            'productcategoryid3' => 'nullable|integer',
            'productcategoryid4' => 'required|integer|exists:category,categoryid',
            'subcategory1' => 'nullable|integer',
            'subcategory2' => 'nullable|integer',
            'subcategory3' => 'nullable|integer',
            'barcode' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'occasion' => 'nullable|integer|exists:occassion,occassionid',
            'color' => 'nullable|integer|exists:filtervalues,filtervalueid',
        ], [
            'title.required' => 'Product title (EN) is required.',
            'title.unique' => 'This product title (EN) already exists.',
            'titleAR.required' => 'Product title (AR) is required.',
            'titleAR.unique' => 'This product title (AR) already exists.',
            'productcode.required' => 'Product code is required.',
            'productcode.unique' => 'This product code already exists.',
            'price.required' => 'Price is required.',
            'sellingprice.required' => 'Selling Price [KWD] is required.',
            'productcategoryid4.required' => 'Gift Product Category 4 is required.',
            'productcategoryid4.exists' => 'Selected Gift Product Category 4 does not exist.',
        ]);

        // Handle boolean fields - Gift Products 4 are identified by productcategoryid4
        $validated['ispublished'] = 1; // Default to published
        $validated['isnew'] = 0;
        $validated['ispopular'] = 0;
        $validated['isgift'] = 0; // Gift Products 4 are identified by productcategoryid4, not isgift flag
        $validated['internation_ship'] = 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        
        // Use productcategoryid as main category (fkcategoryid) if provided
        $validated['fkcategoryid'] = $request->input('productcategoryid') ?? (isset($product) && $product ? $product->fkcategoryid : 0);
        
        // Handle subcategories - map subcategory1 to productcategoryid2, subcategory2 to productcategoryid3
        if ($request->has('subcategory1') && $request->subcategory1) {
            $validated['productcategoryid2'] = $request->subcategory1;
        } elseif ($request->has('productcategoryid2') && $request->productcategoryid2) {
            $validated['productcategoryid2'] = $request->productcategoryid2;
        } else {
            $validated['productcategoryid2'] = (isset($product) && $product) ? ($product->productcategoryid2 ?? 0) : 0;
        }
        
        if ($request->has('subcategory2') && $request->subcategory2) {
            $validated['productcategoryid3'] = $request->subcategory2;
        } elseif ($request->has('productcategoryid3') && $request->productcategoryid3) {
            $validated['productcategoryid3'] = $request->productcategoryid3;
        } else {
            $validated['productcategoryid3'] = (isset($product) && $product) ? ($product->productcategoryid3 ?? 0) : 0;
        }

        // Handle shortdescr - set to empty string if not provided (NOT NULL constraint)
        $validated['shortdescr'] = $validated['shortdescr'] ?? '';
        $validated['shortdescrAR'] = $validated['shortdescrAR'] ?? '';
        
        // Handle longdescr - set to empty string if not provided (NOT NULL constraint)
        $validated['longdescr'] = $validated['longdescr'] ?? '';
        $validated['longdescrAR'] = $validated['longdescrAR'] ?? '';

        // Handle metatitle - set to empty string if not provided (NOT NULL constraint)
        $validated['metatitle'] = $validated['metatitle'] ?? '';
        $validated['metatitleAR'] = $validated['metatitleAR'] ?? '';
        $validated['metakeyword'] = $validated['metakeyword'] ?? '';
        $validated['metakeywordAR'] = $validated['metakeywordAR'] ?? '';
        $validated['metadescr'] = $validated['metadescr'] ?? '';
        $validated['metadescrAR'] = $validated['metadescrAR'] ?? '';
        
        // Handle sellingprice - set to price if not provided (NOT NULL constraint)
        if (!isset($validated['sellingprice']) || $validated['sellingprice'] === null || $validated['sellingprice'] === '') {
            $validated['sellingprice'] = $validated['price'] ?? (isset($product) && $product ? ($product->sellingprice ?? $product->price ?? 0) : 0);
        }
        
        // Handle photo fields - keep existing if not uploading new one
        for ($i = 1; $i <= 5; $i++) {
            $photoFile = $request->file("photo{$i}");
            
            if (!$photoFile && $request->files->has("photo{$i}")) {
                $photoFile = $request->files->get("photo{$i}");
            }
            
            if ($photoFile && $photoFile->isValid()) {
                $validated["photo{$i}"] = $this->uploadImage($photoFile, $product->{"photo{$i}"}, 'product');
            } else {
                $validated["photo{$i}"] = (isset($product) && $product) ? ($product->{"photo{$i}"} ?? '') : '';
            }
        }

        $validated['updatedby'] = Auth::check() ? Auth::id() : 1;
        $validated['updateddate'] = now();

        try {
            DB::beginTransaction();
            
            // Remove fields that don't belong to products table
            $productData = $validated;
            unset($productData['barcode'], $productData['quantity'], $productData['occasion'], $productData['color']);
            
            $product->update($productData);
            
            // Delete existing color and occasion filters
            DB::table('productsfilter')
                ->where('fkproductid', $product->productid)
                ->whereIn('filtercode', ['color', 'occassion'])
                ->delete();
            
            $storeId = 1; // Default store ID
            
            // Save color to productsfilter
            if ($request->has('color') && $request->color) {
                DB::table('productsfilter')->insert([
                    'fkproductid' => $product->productid,
                    'fkfiltervalueid' => $request->color,
                    'filtercode' => 'color',
                    'qty' => 0,
                    'fkstoreid' => $storeId,
                    'barcode' => $request->barcode ?? null,
                ]);
            }
            
            // Save occasion to productsfilter
            if ($request->has('occasion') && $request->occasion) {
                DB::table('productsfilter')->insert([
                    'fkproductid' => $product->productid,
                    'fkfiltervalueid' => $request->occasion,
                    'filtercode' => 'occassion',
                    'qty' => $request->quantity ?? 0,
                    'fkstoreid' => $storeId,
                    'barcode' => $request->barcode ?? null,
                ]);
            }
            
            DB::commit();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift Product 4 updated successfully',
                    'data' => $product,
                    'redirect' => route('admin.gift-products4')
                ]);
            }

            return redirect()->route('admin.gift-products4')
                ->with('success', 'Gift Product 4 updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
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
            DB::rollBack();
            
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

