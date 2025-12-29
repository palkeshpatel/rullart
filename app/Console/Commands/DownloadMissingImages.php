<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DownloadMissingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:download 
                            {--source=https://www.rullart.com/ : Source URL to download from}
                            {--check-only : Only check which images are missing, don\'t download}
                            {--type=all : Type of images to download (all, products, homegallery, category)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing images from live site (https://www.rullart.com/)';

    protected $sourceUrl;
    protected $storagePath;
    protected $downloaded = 0;
    protected $failed = 0;
    protected $skipped = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sourceUrl = rtrim($this->option('source'), '/');
        $this->storagePath = public_path('resources/storage');
        
        // Create storage directory if it doesn't exist
        if (!File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true);
            $this->info("Created storage directory: {$this->storagePath}");
        }

        $type = $this->option('type');
        $checkOnly = $this->option('check-only');

        $this->info("Starting image download process...");
        $this->info("Source URL: {$this->sourceUrl}");
        $this->info("Storage Path: {$this->storagePath}");
        $this->info("Check Only: " . ($checkOnly ? 'Yes' : 'No'));
        $this->newLine();

        if ($type === 'all' || $type === 'products') {
            $this->downloadProductImages($checkOnly);
        }

        if ($type === 'all' || $type === 'homegallery') {
            $this->downloadHomeGalleryImages($checkOnly);
        }

        if ($type === 'all' || $type === 'category') {
            $this->downloadCategoryImages($checkOnly);
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Downloaded: {$this->downloaded}");
        $this->info("Failed: {$this->failed}");
        $this->info("Skipped (already exists): {$this->skipped}");

        return 0;
    }

    protected function downloadProductImages($checkOnly = false)
    {
        $this->info("=== Product Images ===");
        
        // Get all product images
        $products = DB::table('products')
            ->select('productid', 'productcode', 'photo1', 'shortdescr')
            ->where('ispublished', 1)
            ->whereNotNull('photo1')
            ->where('photo1', '!=', '')
            ->where('photo1', '!=', 'noimage.jpg')
            ->get();

        $this->info("Found {$products->count()} products with images");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $filename = $product->photo1;
            $thumbnailName = 'thumb-' . $filename;
            
            $localPath = $this->storagePath . '/' . $thumbnailName;
            
            // Check if file already exists
            if (File::exists($localPath)) {
                $this->skipped++;
                $bar->advance();
                continue;
            }

            if ($checkOnly) {
                $this->newLine();
                $this->warn("Missing: {$thumbnailName}");
                $this->failed++;
                $bar->advance();
                continue;
            }

            // Try to download from live site
            $sourceUrl = $this->sourceUrl . '/resources/storage/' . $thumbnailName;
            
            if ($this->downloadImage($sourceUrl, $localPath, $thumbnailName)) {
                $this->downloaded++;
            } else {
                $this->failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function downloadHomeGalleryImages($checkOnly = false)
    {
        $this->info("=== Home Gallery Images ===");
        
        $gallery = DB::table('homegallery')
            ->select('homegalleryid', 'title', 'photo', 'photo_mobile', 'photo_ar', 'photo_mobile_ar')
            ->where('ispublished', 1)
            ->get();

        $images = [];
        foreach ($gallery as $item) {
            if (!empty($item->photo)) {
                $images[] = ['name' => $item->photo, 'type' => 'photo'];
            }
            if (!empty($item->photo_mobile)) {
                $images[] = ['name' => $item->photo_mobile, 'type' => 'photo_mobile'];
            }
            if (!empty($item->photo_ar)) {
                $images[] = ['name' => $item->photo_ar, 'type' => 'photo_ar'];
            }
            if (!empty($item->photo_mobile_ar)) {
                $images[] = ['name' => $item->photo_mobile_ar, 'type' => 'photo_mobile_ar'];
            }
        }

        $this->info("Found " . count($images) . " home gallery images");

        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        foreach ($images as $image) {
            $filename = $image['name'];
            $localPath = $this->storagePath . '/' . $filename;
            
            if (File::exists($localPath)) {
                $this->skipped++;
                $bar->advance();
                continue;
            }

            if ($checkOnly) {
                $this->newLine();
                $this->warn("Missing: {$filename}");
                $this->failed++;
                $bar->advance();
                continue;
            }

            $sourceUrl = $this->sourceUrl . '/resources/storage/' . $filename;
            
            if ($this->downloadImage($sourceUrl, $localPath, $filename)) {
                $this->downloaded++;
            } else {
                $this->failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function downloadCategoryImages($checkOnly = false)
    {
        $this->info("=== Category Images ===");
        
        $categories = DB::table('category')
            ->select('categoryid', 'categorycode', 'category', 'photo')
            ->where('ispublished', 1)
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->get();

        $this->info("Found {$categories->count()} categories with images");

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        foreach ($categories as $category) {
            $filename = $category->photo;
            $localPath = $this->storagePath . '/' . $filename;
            
            if (File::exists($localPath)) {
                $this->skipped++;
                $bar->advance();
                continue;
            }

            if ($checkOnly) {
                $this->newLine();
                $this->warn("Missing: {$filename}");
                $this->failed++;
                $bar->advance();
                continue;
            }

            $sourceUrl = $this->sourceUrl . '/resources/storage/' . $filename;
            
            if ($this->downloadImage($sourceUrl, $localPath, $filename)) {
                $this->downloaded++;
            } else {
                $this->failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function downloadImage($sourceUrl, $localPath, $filename)
    {
        try {
            $response = Http::timeout(30)->get($sourceUrl);
            
            if ($response->successful()) {
                File::put($localPath, $response->body());
                return true;
            } else {
                $this->newLine();
                $this->error("Failed to download {$filename}: HTTP {$response->status()}");
                return false;
            }
        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Error downloading {$filename}: " . $e->getMessage());
            return false;
        }
    }
}
