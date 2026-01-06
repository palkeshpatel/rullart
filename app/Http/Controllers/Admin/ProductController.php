<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;
use Exception;

class ProductController extends Controller
{
    use ImageUploadTrait;
    public function index(Request $request)
    {
        // ============================================
        // NEW DATATABLES IMPLEMENTATION
        // ============================================

        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // ============================================
        // OLD IMPLEMENTATION (COMMENTED FOR REFERENCE)
        // ============================================
        /*
        $query = Product::with('category');

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

        // Get categories for dropdown with subcategory count
        $categories = Category::select('category.*')
            ->selectRaw('(SELECT COUNT(*) FROM category as sub WHERE sub.parentid = category.categoryid AND sub.ispublished = 1) as subcategory_count')
            ->orderBy('category')
            ->get();

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.products-table', compact('products'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $products])->render(),
            ]);
        }

        return view('admin.products.index', compact('products', 'categories'));
        */

        // Get categories for dropdown with subcategory count
        $categories = Category::select('category.*')
            ->selectRaw('(SELECT COUNT(*) FROM category as sub WHERE sub.parentid = category.categoryid AND sub.ispublished = 1) as subcategory_count')
            ->orderBy('category')
            ->get();

        return view('admin.products.index', compact('categories'));
    }

    /**
     * Get DataTables data
     */
    private function getDataTablesData(Request $request)
    {
        try {
            // Base query for counting (without selectRaw to avoid issues)
            $countQuery = Product::query();

            // Get total records count (before filtering)
            $totalRecords = $countQuery->count();

            // Build base query for data (with quantity calculation)
            $query = Product::with('category')
                ->select('products.*')
                ->selectRaw('IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=products.productid AND filtercode=\'size\'), 0) as quantity');

            // Build count query for filtered results (without selectRaw)
            $filteredCountQuery = Product::query();

            // Filter by category
            $category = $request->input('category');
            if (!empty($category)) {
                $query->where('products.fkcategoryid', $category);
                $filteredCountQuery->where('fkcategoryid', $category);
            }

            // Filter by published status - only if explicitly set to 0 or 1
            // Don't filter if published is null, empty, or the string "null"
            $published = $request->input('published');
            if ($published !== null && $published !== '' && $published !== 'null' && ($published === '0' || $published === '1' || $published === 0 || $published === 1)) {
                $publishedValue = (int)$published;
                $query->where('products.ispublished', $publishedValue);
                $filteredCountQuery->where('ispublished', $publishedValue);
            }

            // Get filtered count (after filters but before search)
            $filteredCount = $filteredCountQuery->count();

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('products.title', 'like', "%{$searchValue}%")
                        ->orWhere('products.titleAR', 'like', "%{$searchValue}%")
                        ->orWhere('products.productcode', 'like', "%{$searchValue}%")
                        ->orWhere('products.price', 'like', "%{$searchValue}%")
                        ->orWhere('products.sellingprice', 'like', "%{$searchValue}%");
                });

                // Apply same search to count query
                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('titleAR', 'like', "%{$searchValue}%")
                        ->orWhere('productcode', 'like', "%{$searchValue}%")
                        ->orWhere('price', 'like', "%{$searchValue}%")
                        ->orWhere('sellingprice', 'like', "%{$searchValue}%");
                });
            }

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            $columns = [
                'productid',
                'productcode',
                'title',
                'fkcategoryid',
                'price',
                'discount',
                'sellingprice',
                'photo1',
                'quantity',
                'ispublished',
                'updateddate'
            ];

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

            // Debug logging
            Log::info('DataTables Query Debug', [
                'totalRecords' => $totalRecords,
                'filteredCount' => $filteredCount,
                'filteredAfterSearch' => $filteredAfterSearch,
                'published_input' => $request->input('published'),
                'published_type' => gettype($request->input('published')),
                'category_input' => $request->input('category'),
                'query_sql' => $query->toSql(),
                'query_bindings' => $query->getBindings(),
                'count_query_sql' => $filteredCountQuery->toSql(),
                'count_query_bindings' => $filteredCountQuery->getBindings()
            ]);

            $products = $query->skip($start)->take($length)->get();

            Log::info('DataTables Products Retrieved', [
                'products_count' => $products->count()
            ]);

            // Format data for DataTables
            $data = [];
            foreach ($products as $product) {
                $data[] = [
                    'productid' => $product->productid,
                    'productcode' => $product->productcode ?? '',
                    'title' => $product->title ?? '',
                    'category' => $product->category->category ?? 'N/A',
                    'price' => number_format($product->price ?? 0, 3),
                    'discount' => number_format($product->discount ?? 0, 2),
                    'sellingprice' => number_format($product->sellingprice ?? 0, 3),
                    'photo' => $product->photo1 ? asset('storage/upload/product/thumb-' . $product->photo1) : null,
                    'quantity' => $product->quantity ?? 0,
                    'ispublished' => $product->ispublished ? 'Yes' : 'No',
                    'updateddate' => $product->updateddate ? \Carbon\Carbon::parse($product->updateddate)->format('d/M/Y') : 'N/A',
                    'action' => $product->productid // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('DataTables Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        // Get categories for dropdown with subcategory count
        $categories = Category::select('category.*')
            ->selectRaw('(SELECT COUNT(*) FROM category as sub WHERE sub.parentid = category.categoryid AND sub.ispublished = 1) as subcategory_count')
            ->orderBy('category')
            ->get();

        // Get colors (filtervalues where fkfilterid = 2)
        $colors = DB::table('filtervalues')
            ->where('fkfilterid', 2)
            ->where('isactive', 1)
            ->orderBy('displayorder')
            ->orderBy('filtervalue')
            ->get();

        // Get sizes (filtervalues where fkfilterid = 3)
        $sizes = DB::table('filtervalues')
            ->where('fkfilterid', 3)
            ->where('isactive', 1)
            ->orderBy('displayorder')
            ->orderBy('filtervalue')
            ->get();

        // Get occasions
        $occasions = \App\Models\Occassion::where('ispublished', 1)
            ->orderBy('occassion')
            ->get();

        // Return JSON for AJAX modal requests (for backward compatibility)
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.products.partials.product-form', [
                    'product' => null,
                    'categories' => $categories,
                    'colors' => $colors,
                    'sizes' => $sizes,
                    'occasions' => $occasions
                ])->render(),
            ]);
        }

        return view('admin.products.create', compact('categories', 'colors', 'sizes', 'occasions'));
    }

    public function store(Request $request)
    {
        // Normalize inputs (trim whitespace from productcode)
        $productcode = trim($request->productcode ?? '');
        $title = trim($request->title ?? '');
        $titleAR = trim(preg_replace('/\s+/u', ' ', $request->titleAR ?? ''));

        $request->merge([
            'productcode' => $productcode,
            'title' => $title,
            'titleAR' => $titleAR,
        ]);

        // Custom validation: Check if productcode already exists (with detailed logging)
        if (!empty($productcode)) {
            // Check exact match
            $exactMatch = Product::where('productcode', $productcode)->first();
            // Check case-insensitive match
            $caseInsensitiveMatch = Product::whereRaw('LOWER(productcode) = ?', [strtolower($productcode)])->first();

            if ($exactMatch) {
                Log::warning('Product code validation failed: Exact match found', [
                    'input_productcode' => $productcode,
                    'input_length' => strlen($productcode),
                    'existing_productid' => $exactMatch->productid,
                    'existing_productcode' => $exactMatch->productcode,
                    'existing_length' => strlen($exactMatch->productcode),
                    'existing_title' => $exactMatch->title,
                    'codes_identical' => $productcode === $exactMatch->productcode,
                    'codes_equal' => $productcode == $exactMatch->productcode
                ]);
            } elseif ($caseInsensitiveMatch && $caseInsensitiveMatch->productcode !== $productcode) {
                Log::warning('Product code validation: Case-insensitive match found', [
                    'input_productcode' => $productcode,
                    'existing_productcode' => $caseInsensitiveMatch->productcode,
                    'existing_productid' => $caseInsensitiveMatch->productid
                ]);
            }
        }

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
            'video_file' => 'nullable|mimes:mp4,avi,mov,wmv,flv,webm|max:102400',
            'videoposter_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video' => 'nullable|string|max:500',
            'videoposter' => 'nullable|string|max:500',
            'ispublished' => 'nullable',
            'isnew' => 'nullable',
            'ispopular' => 'nullable',
            'isgift' => 'nullable',
            'internation_ship' => 'nullable',
            'productcategoryid' => 'nullable|integer',
            'productcategoryid2' => 'nullable|integer',
            'productcategoryid3' => 'nullable|integer',
        ], [
            'fkcategoryid.required' => 'Category is required.',
            'fkcategoryid.exists' => 'Selected category does not exist.',
            'title.required' => 'Title [EN] is required.',
            'title.unique' => 'This product title (EN) already exists.',
            'titleAR.required' => 'Title [AR] is required.',
            'titleAR.unique' => 'This product title (AR) already exists.',
            'productcode.required' => 'Product Code is required.',
            'productcode.unique' => 'This product code already exists.',
            'price.required' => 'Product Price [KWD] is required.',
            'sellingprice.required' => 'Selling Price [KWD] is required.',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['isnew'] = $request->has('isnew') ? 1 : 0;
        $validated['ispopular'] = $request->has('ispopular') ? 1 : 0;
        $validated['isgift'] = $request->has('isgift') ? 1 : 0;
        $validated['internation_ship'] = $request->has('internation_ship') ? 1 : 0;
        $validated['discount'] = $validated['discount'] ?? 0;

        // shortdescr and shortdescrAR are NOT NULL in database, so always set them (empty string if not provided)
        $validated['shortdescr'] = $validated['shortdescr'] ?? '';
        $validated['shortdescrAR'] = $validated['shortdescrAR'] ?? '';
        
        // metatitle is NOT NULL in database, so always set it (empty string if not provided)
        $validated['metatitle'] = $validated['metatitle'] ?? '';
        $validated['metatitleAR'] = $validated['metatitleAR'] ?? '';
        
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

        // Handle video file upload
        if ($request->hasFile('video_file')) {
            $videoFile = $request->file('video_file');
            $videoName = time() . '_' . uniqid() . '_video.' . $videoFile->getClientOriginalExtension();
            $videoFile->storeAs('upload/product', $videoName, 'public');
            $validated['video'] = $videoName;
        }

        // Handle video poster file upload
        if ($request->hasFile('videoposter_file')) {
            $validated['videoposter'] = $this->uploadImage($request->file('videoposter_file'), null, 'product');
        }

        try {
            DB::beginTransaction();
            
            $product = Product::create($validated);
            
            // Refresh to ensure we have the auto-generated ID
            $product->refresh();
            
            // Ensure product was created and has an ID
            if (!$product || !$product->productid) {
                DB::rollBack();
                throw new \Exception('Failed to create product. Product ID is missing.');
            }
            
            Log::info('Product created successfully', [
                'productid' => $product->productid,
                'productcode' => $product->productcode
            ]);

            // Handle productsfilter (sizes, colors, occasions)
            $storeId = 1; // Default store ID

            // Save sizes with quantities
            if ($request->has('sizes')) {
                $sizes = $request->input('sizes', []);
                foreach ($sizes as $sizeData) {
                    if (isset($sizeData['filtervalueid']) && isset($sizeData['qty'])) {
                        DB::table('productsfilter')->insert([
                            'fkproductid' => $product->productid,
                            'fkfiltervalueid' => $sizeData['filtervalueid'],
                            'filtercode' => 'size',
                            'qty' => $sizeData['qty'] ?? 0,
                            'fkstoreid' => $storeId,
                            'barcode' => $sizeData['barcode'] ?? null,
                        ]);
                    }
                }
            }

            // Save color
            if ($request->has('color') && $request->color) {
                \DB::table('productsfilter')->insert([
                    'fkproductid' => $product->productid,
                    'fkfiltervalueid' => $request->color,
                    'filtercode' => 'color',
                    'qty' => 0,
                    'fkstoreid' => $storeId,
                    'barcode' => null,
                ]);
            }

            // Save occasions
            if ($request->has('occasions') && is_array($request->occasions)) {
                foreach ($request->occasions as $occasionId) {
                    \DB::table('productsfilter')->insert([
                        'fkproductid' => $product->productid,
                        'fkfiltervalueid' => $occasionId,
                        'filtercode' => 'occassion',
                        'qty' => 0,
                        'fkstoreid' => $storeId,
                        'barcode' => null,
                    ]);
                }
            }

            DB::commit();
            
            // Always return JSON for AJAX requests, otherwise redirect
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'data' => $product,
                    'redirect' => route('admin.products')
                ]);
            }

            return redirect()->route('admin.products')
                ->with('success', 'Product created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors (including unique constraint)
            Log::warning('Product validation failed', [
                'errors' => $e->errors(),
                'productcode' => $request->productcode ?? 'N/A',
                'title' => $request->title ?? 'N/A'
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check your input.',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            // Handle database constraint violations
            $errorMessage = 'An error occurred while saving the product.';
            $errorField = 'productcode';

            Log::error('Product creation database error', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'productcode' => $request->productcode ?? 'N/A',
                'sql' => $e->getSql() ?? 'N/A'
            ]);

            if ($e->getCode() == 23000) {
                // Check for NOT NULL constraint violations first
                if (strpos($e->getMessage(), 'cannot be null') !== false) {
                    if (strpos($e->getMessage(), 'metatitle') !== false) {
                        $errorMessage = 'Meta Title is required. Please fill in the Meta Title field.';
                        $errorField = 'metatitle';
                    } elseif (strpos($e->getMessage(), 'shortdescr') !== false) {
                        $errorMessage = 'Short Description is required.';
                        $errorField = 'shortdescr';
                    } else {
                        $errorMessage = 'A required field is missing. Please check all required fields.';
                        $errorField = 'general';
                    }
                } elseif (strpos($e->getMessage(), 'productcode') !== false || strpos($e->getMessage(), 'uniqueProductCode') !== false) {
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
                Log::error('Product creation error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while saving the product: ' . $e->getMessage();
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
            
            Log::error('Product creation error: ' . $e->getMessage());

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
        $product = Product::with('category')->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.products.partials.product-view', compact('product'))->render(),
            ]);
        }

        return view('admin.products.show', compact('product'));
    }

    public function edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        // Get categories for dropdown with subcategory count
        $categories = Category::select('category.*')
            ->selectRaw('(SELECT COUNT(*) FROM category as sub WHERE sub.parentid = category.categoryid AND sub.ispublished = 1) as subcategory_count')
            ->orderBy('category')
            ->get();

        // Get colors (filtervalues where fkfilterid = 2)
        $colors = DB::table('filtervalues')
            ->where('fkfilterid', 2)
            ->where('isactive', 1)
            ->orderBy('displayorder')
            ->orderBy('filtervalue')
            ->get();

        // Get sizes (filtervalues where fkfilterid = 3)
        $sizes = DB::table('filtervalues')
            ->where('fkfilterid', 3)
            ->where('isactive', 1)
            ->orderBy('displayorder')
            ->orderBy('filtervalue')
            ->get();

        // Get occasions
        $occasions = \App\Models\Occassion::where('ispublished', 1)
            ->orderBy('occassion')
            ->get();

        // Get product filters (sizes, colors, occasions)
        $productFilters = DB::table('productsfilter')
            ->where('fkproductid', $id)
            ->get()
            ->groupBy('filtercode');

        // Get product sizes with quantities
        $productSizes = DB::table('productsfilter as pf')
            ->join('filtervalues as fv', 'fv.filtervalueid', '=', 'pf.fkfiltervalueid')
            ->where('pf.fkproductid', $id)
            ->where('pf.filtercode', 'size')
            ->select('pf.fkfiltervalueid', 'pf.qty', 'pf.barcode', 'fv.filtervalue', 'fv.filtervalueAR')
            ->get();

        // Return JSON for AJAX modal requests (for backward compatibility)
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.products.partials.product-form', [
                    'product' => $product,
                    'categories' => $categories,
                    'colors' => $colors,
                    'sizes' => $sizes,
                    'occasions' => $occasions,
                    'productFilters' => $productFilters,
                    'productSizes' => $productSizes
                ])->render(),
            ]);
        }

        return view('admin.products.edit', compact('product', 'categories', 'colors', 'sizes', 'occasions', 'productFilters', 'productSizes'));
    }



    public function update(Request $request, $id)
    {
        $product = Product::where('productid', $id)->firstOrFail();

        /* -------------------------------------------------
     | Normalize inputs (important for AR / encoding)
     |--------------------------------------------------*/
        $request->merge([
            'title'     => trim($request->title),
            'titleAR'   => trim(preg_replace('/\s+/u', ' ', $request->titleAR)),
            'productcode' => trim($request->productcode),
        ]);

        /* -------------------------------------------------
     | Validation
     |--------------------------------------------------*/
        $validated = $request->validate([
            'fkcategoryid' => 'required|integer|exists:category,categoryid',

            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'title')
                    ->ignore($product->productid, 'productid'),
            ],

            'titleAR' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'titleAR')
                    ->ignore($product->productid, 'productid'),
            ],

            'productcode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'productcode')
                    ->ignore($product->productid, 'productid'),
            ],

            'shortdescr'     => 'nullable|string|max:1800',
            'shortdescrAR'   => 'nullable|string|max:1800',
            'longdescr'      => 'nullable|string',
            'longdescrAR'    => 'nullable|string',

            'price'          => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'sellingprice'   => 'required|numeric|min:0',

            'photo1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo4' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo5' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',

            'video_file'       => 'nullable|mimes:mp4,avi,mov,wmv,flv,webm|max:102400',
            'videoposter_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ], [
            'title.unique'     => 'This product title (EN) already exists.',
            'titleAR.unique'   => 'This product title (AR) already exists.',
            'productcode.unique' => 'This product code already exists.',
        ]);

        /* -------------------------------------------------
     | Boolean flags
     |--------------------------------------------------*/
        $validated['ispublished']      = $request->boolean('ispublished');
        $validated['isnew']            = $request->boolean('isnew');
        $validated['ispopular']        = $request->boolean('ispopular');
        $validated['isgift']           = $request->boolean('isgift');
        $validated['internation_ship'] = $request->boolean('internation_ship');

        $validated['discount']    = $validated['discount'] ?? 0;
        
        // shortdescr and shortdescrAR are NOT NULL in database, so always set them (empty string if not provided)
        $validated['shortdescr'] = $validated['shortdescr'] ?? '';
        $validated['shortdescrAR'] = $validated['shortdescrAR'] ?? '';
        
        // metatitle is NOT NULL in database, so always set it (empty string if not provided)
        $validated['metatitle'] = $validated['metatitle'] ?? '';
        $validated['metatitleAR'] = $validated['metatitleAR'] ?? '';
        
        $validated['updatedby']   = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        /* -------------------------------------------------
     | Handle image uploads
     |--------------------------------------------------*/
        for ($i = 1; $i <= 5; $i++) {
            if ($request->hasFile("photo{$i}")) {
                $validated["photo{$i}"] = $this->uploadImage(
                    $request->file("photo{$i}"),
                    $product->{"photo{$i}"},
                    'product'
                );
            } else {
                $validated["photo{$i}"] = $product->{"photo{$i}"};
            }
        }

        /* -------------------------------------------------
     | Video upload
     |--------------------------------------------------*/
        if ($request->hasFile('video_file')) {
            if ($product->video) {
                $this->deleteImage($product->video, 'product');
            }

            $videoName = time() . '_' . uniqid() . '.' .
                $request->video_file->getClientOriginalExtension();

            $request->video_file->storeAs('upload/product', $videoName, 'public');
            $validated['video'] = $videoName;
        }

        if ($request->hasFile('videoposter_file')) {
            $validated['videoposter'] = $this->uploadImage(
                $request->videoposter_file,
                $product->videoposter,
                'product'
            );
        }

        /* -------------------------------------------------
     | DB Transaction (important)
     |--------------------------------------------------*/
        DB::beginTransaction();

        try {
            $product->update($validated);

            // Remove old filters
            DB::table('productsfilter')
                ->where('fkproductid', $product->productid)
                ->delete();

            $storeId = 1;

            // Sizes
            foreach ($request->input('sizes', []) as $size) {
                if (!empty($size['filtervalueid'])) {
                    DB::table('productsfilter')->insert([
                        'fkproductid'     => $product->productid,
                        'fkfiltervalueid' => $size['filtervalueid'],
                        'filtercode'     => 'size',
                        'qty'            => $size['qty'] ?? 0,
                        'fkstoreid'      => $storeId,
                        'barcode'        => $size['barcode'] ?? null,
                    ]);
                }
            }

            // Color
            if ($request->filled('color')) {
                DB::table('productsfilter')->insert([
                    'fkproductid'     => $product->productid,
                    'fkfiltervalueid' => $request->color,
                    'filtercode'     => 'color',
                    'qty'            => 0,
                    'fkstoreid'      => $storeId,
                ]);
            }

            // Occasions
            foreach ((array)$request->occasions as $occasionId) {
                DB::table('productsfilter')->insert([
                    'fkproductid'     => $product->productid,
                    'fkfiltervalueid' => $occasionId,
                    'filtercode'     => 'occassion',
                    'qty'            => 0,
                    'fkstoreid'      => $storeId,
                ]);
            }

            DB::commit();

            // Handle both AJAX and regular form submissions
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data'    => $product->fresh(),
                    'redirect' => route('admin.products')
                ]);
            }

            return redirect()->route('admin.products')
                ->with('success', 'Product updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Product update failed', ['error' => $e->getMessage()]);

            // Handle both AJAX and regular form submissions
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update product: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])
                ->withInput();
        }
    }


    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        try {
            // Delete photos if they exist
            for ($i = 1; $i <= 5; $i++) {
                $photoField = "photo{$i}";
                if ($product->$photoField) {
                    $this->deleteImage($product->$photoField, 'product');
                }
            }

            // Delete video and poster if they exist
            if ($product->video) {
                $this->deleteImage($product->video, 'product');
            }
            if ($product->videoposter) {
                $this->deleteImage($product->videoposter, 'product');
            }

            $product->delete();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);
            }

            return redirect()->route('admin.products')
                ->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            Log::error('Product deletion error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the product.'
                ], 422);
            }

            return back()->withErrors(['error' => 'An error occurred while deleting the product.']);
        }
    }

    /**
     * Remove image from product (via AJAX with confirmation)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeImage(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $column = $request->input('column'); // 'photo1', 'photo2', 'photo3', 'photo4', 'photo5', 'video', 'videoposter'

        // Validate column name
        $validColumns = ['photo1', 'photo2', 'photo3', 'photo4', 'photo5', 'video', 'videoposter'];
        if (!in_array($column, $validColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid column name'
            ], 400);
        }

        // Remove image using trait
        $this->removeImageFromModel($product, $column, 'product');

        return response()->json([
            'success' => true,
            'message' => 'Image removed successfully'
        ]);
    }

    /**
     * Get subcategories based on category ID
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubcategories(Request $request)
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json([
                'success' => false,
                'message' => 'Category ID is required'
            ], 400);
        }

        // Get subcategories where parentid matches the selected category
        // Following CI project pattern: get categories where parentid = selected category ID
        $subcategories = Category::where('parentid', $categoryId)
            ->where('ispublished', 1)  // Filter published categories only (matching CI project pattern)
            ->orderBy('displayorder')
            ->orderBy('category')
            ->get(['categoryid', 'category', 'categoryAR', 'categorycode']);

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }
}
