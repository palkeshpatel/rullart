<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;

class ProductController extends Controller
{
    use ImageUploadTrait;
    public function index(Request $request)
    {
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

        // Get categories for dropdown
        $categories = Category::orderBy('category')->get();

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.products-table', compact('products'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $products])->render(),
            ]);
        }

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(Request $request)
    {
        // Get categories for dropdown
        $categories = Category::orderBy('category')->get();
        
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

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['isnew'] = $request->has('isnew') ? 1 : 0;
        $validated['ispopular'] = $request->has('ispopular') ? 1 : 0;
        $validated['isgift'] = $request->has('isgift') ? 1 : 0;
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
            $product = Product::create($validated);
            
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

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'data' => $product
                ]);
            }

            return redirect()->route('admin.products')
                ->with('success', 'Product created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while saving the product.';
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
        $categories = Category::orderBy('category')->get();
        
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
        $product = Product::findOrFail($id);

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

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['isnew'] = $request->has('isnew') ? 1 : 0;
        $validated['ispopular'] = $request->has('ispopular') ? 1 : 0;
        $validated['isgift'] = $request->has('isgift') ? 1 : 0;
        $validated['internation_ship'] = $request->has('internation_ship') ? 1 : 0;
        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['updatedby'] = auth()->id() ?? 1;
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

        // Handle video file upload
        $videoFile = $request->file('video_file');
        if (!$videoFile && $request->files->has('video_file')) {
            $videoFile = $request->files->get('video_file');
        }
        
        if ($videoFile && $videoFile->isValid()) {
            // Delete old video if exists
            if ($product->video) {
                $this->deleteImage($product->video, 'product');
            }
            $videoName = time() . '_' . uniqid() . '_video.' . $videoFile->getClientOriginalExtension();
            $videoFile->storeAs('upload/product', $videoName, 'public');
            $validated['video'] = $videoName;
        }
        
        // Handle video poster file upload
        $posterFile = $request->file('videoposter_file');
        if (!$posterFile && $request->files->has('videoposter_file')) {
            $posterFile = $request->files->get('videoposter_file');
        }
        
        if ($posterFile && $posterFile->isValid()) {
            $validated['videoposter'] = $this->uploadImage($posterFile, $product->videoposter, 'product');
        }

        try {
            $product->update($validated);
            
            // Handle productsfilter (sizes, colors, occasions)
            $storeId = 1; // Default store ID
            
            // Delete existing filters
            DB::table('productsfilter')->where('fkproductid', $product->productid)->delete();
            
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

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => $product
                ]);
            }

            return redirect()->route('admin.products')
                ->with('success', 'Product updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while updating the product.';
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
                Log::error('Product update error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while updating the product: ' . $e->getMessage();
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
            Log::error('Product update error: ' . $e->getMessage());

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
}
