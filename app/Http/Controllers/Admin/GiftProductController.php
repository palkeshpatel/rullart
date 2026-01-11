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

class GiftProductController extends Controller
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
        return view('admin.gift-products.index', compact('categories'));
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            $countQuery = Product::where('isgift', 1);
            $totalRecords = $countQuery->count();

            // Map shortdescr as title to match frontend/CI project behavior
            $query = Product::with('category')
                ->where('isgift', 1)
                ->select('products.*')
                ->selectRaw('products.shortdescr as display_title, products.title as display_shortdescr')
                ->selectRaw('IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=products.productid AND filtercode=\'size\'), 0) as quantity');
            $filteredCountQuery = Product::where('isgift', 1);

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
                $photoUrl = $product->photo1 ? asset('storage/' . $product->photo1) : null;
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
            Log::error('GiftProduct DataTables Error: ' . $e->getMessage());
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
                'html' => view('admin.gift-products.partials.gift-product-form', [
                    'product' => null,
                    'categories' => $categories,
                    'colors' => $colors,
                    'occasions' => $occasions,
                    'productFilters' => collect()
                ])->render(),
            ]);
        }

        // For now, return modal view (same as AJAX) since gift products should use modal
        return view('admin.gift-products.create', compact('categories', 'colors', 'occasions'));
    }

    public function store(Request $request)
    {
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
        ]);

        // Handle boolean fields - Gift products always have isgift = 1
        $validated['ispublished'] = 1; // Default to published
        $validated['isnew'] = 0;
        $validated['ispopular'] = 0;
        $validated['isgift'] = 1; // Always set to 1 for gift products
        $validated['internation_ship'] = 0;
        $validated['discount'] = $validated['discount'] ?? 0;

        // Use productcategoryid as main category (fkcategoryid) if provided
        $validated['fkcategoryid'] = $request->input('productcategoryid') ?? 0;

        // Handle subcategories - map subcategory1 to productcategoryid2, subcategory2 to productcategoryid3, subcategory3 to productcategoryid4
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

        // productcategoryid4 is not in the form, so set to 0
        $validated['productcategoryid4'] = 0;

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

        // Handle photo fields - set to empty string if not provided (NOT NULL constraint)
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($validated["photo{$i}"])) {
                $validated["photo{$i}"] = '';
            }
        }

        $validated['updatedby'] = Auth::check() ? Auth::id() : 1;
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
            DB::beginTransaction();

            // Remove fields that don't belong to products table
            $productData = $validated;
            unset($productData['barcode'], $productData['quantity'], $productData['occasion'], $productData['color']);

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
                    'message' => 'Gift product created successfully',
                    'data' => $product,
                    'redirect' => route('admin.gift-products')
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
            DB::rollBack();

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
                'html' => view('admin.gift-products.partials.gift-product-form', [
                    'product' => $product,
                    'categories' => $categories,
                    'colors' => $colors,
                    'occasions' => $occasions,
                    'productFilters' => $productFilters
                ])->render(),
            ]);
        }

        return view('admin.gift-products.edit', compact('product', 'categories', 'colors', 'occasions', 'productFilters'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('isgift', 1)->findOrFail($id);

        // Fix for PUT requests with FormData - PHP doesn't populate $_POST or $_FILES for PUT
        // We need to manually parse multipart/form-data to extract both text fields and files
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

                    // Check if this is a file upload (has filename attribute)
                    if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)";\s*filename="([^"]+)"(?:\s*\r?\nContent-Type:\s*([^\r\n]+))?\s*\r?\n\r?\n(.*)/s', $part, $fileMatches)) {
                        $fieldName = $fileMatches[1];
                        $fileName = $fileMatches[2];
                        $contentType = isset($fileMatches[3]) && !empty($fileMatches[3]) ? trim($fileMatches[3]) : 'application/octet-stream';
                        $fileContent = $fileMatches[4];

                        // Remove trailing boundary if present
                        $fileContent = preg_replace('/\r?\n--.*$/s', '', $fileContent);
                        $fileContent = rtrim($fileContent, "\r\n");

                        if (strlen($fileContent) > 0) {
                            // Create temporary file
                            $tempFile = tempnam(sys_get_temp_dir(), 'laravel_upload_');
                            file_put_contents($tempFile, $fileContent);

                            // Create UploadedFile instance
                            $uploadedFile = new UploadedFile(
                                $tempFile,
                                $fileName,
                                $contentType,
                                null,
                                true // test mode
                            );

                            $parsedFiles[$fieldName] = $uploadedFile;
                        }
                    }
                    // Check if this is a regular text field (no filename attribute)
                    elseif (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*)/s', $part, $textMatches)) {
                        $fieldName = $textMatches[1];
                        $fieldValue = $textMatches[2];

                        // Remove trailing boundary if present
                        $fieldValue = preg_replace('/\r?\n--.*$/s', '', $fieldValue);
                        $fieldValue = trim($fieldValue, "\r\n");

                        if ($fieldName !== '_method') {
                            $parsedData[$fieldName] = $fieldValue;
                        }
                    }
                }

                // Merge parsed data
                if (!empty($parsedData)) {
                    $request->merge($parsedData);
                }

                // Add files to request
                if (!empty($parsedFiles)) {
                    foreach ($parsedFiles as $key => $file) {
                        $request->files->set($key, $file);
                    }
                }
            }
        }

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
        ]);

        // Handle boolean fields - Gift products always have isgift = 1
        $validated['ispublished'] = 1; // Default to published
        $validated['isnew'] = 0;
        $validated['ispopular'] = 0;
        $validated['isgift'] = 1; // Always set to 1 for gift products
        $validated['internation_ship'] = 0;
        $validated['discount'] = $validated['discount'] ?? 0;

        // Use productcategoryid as main category (fkcategoryid) if provided
        $validated['fkcategoryid'] = $request->input('productcategoryid') ?? (isset($product) && $product ? $product->fkcategoryid : 0);

        // Handle subcategories - map subcategory1 to productcategoryid2, subcategory2 to productcategoryid3, subcategory3 to productcategoryid4
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

        // productcategoryid4 is not in the form, so keep existing or set to 0
        $validated['productcategoryid4'] = (isset($product) && $product) ? ($product->productcategoryid4 ?? 0) : 0;

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

        // Handle photo fields - keep existing if not uploading new one
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($validated["photo{$i}"]) && isset($product) && $product) {
                $validated["photo{$i}"] = $product->{"photo{$i}"} ?? '';
            } elseif (!isset($validated["photo{$i}"])) {
                $validated["photo{$i}"] = '';
            }
        }

        $validated['updatedby'] = Auth::check() ? Auth::id() : 1;
        $validated['updateddate'] = now();

        // Handle file uploads - check all possible ways files might be accessible
        // Handle photo1-5 uploads using trait
        for ($i = 1; $i <= 5; $i++) {
            $photoFile = $request->file("photo{$i}");

            // If not found, try accessing directly from files bag
            if (!$photoFile && $request->files->has("photo{$i}")) {
                $photoFile = $request->files->get("photo{$i}");
            }

            if ($photoFile && $photoFile->isValid()) {
                $validated["photo{$i}"] = $this->uploadImage($photoFile, $product->{"photo{$i}"}, 'product');
            } else {
                // Keep existing photo if not uploading new one
                $validated["photo{$i}"] = $product->{"photo{$i}"} ?? '';
            }
        }

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
                    'message' => 'Gift product updated successfully',
                    'data' => $product,
                    'redirect' => route('admin.gift-products')
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
                if ($product->$photoField) {
                    $this->deleteImage($product->$photoField, 'product');
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

    /**
     * Remove image from gift product (via AJAX with confirmation)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeImage(Request $request, $id)
    {
        $product = Product::where('isgift', 1)->findOrFail($id);
        $column = $request->input('column'); // 'photo1', 'photo2', 'photo3', 'photo4', 'photo5'

        // Validate column name
        $validColumns = ['photo1', 'photo2', 'photo3', 'photo4', 'photo5'];
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
}