<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
                            {--type=category : Type of images to download (all, products, homegallery, category)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing images from live site (https://www.rullart.com/)';

    protected $sourceUrl;
    protected $downloaded = 0;
    protected $failed = 0;
    protected $skipped = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sourceUrl = rtrim($this->option('source'), '/');
        $type = $this->option('type');
        $checkOnly = $this->option('check-only');

        $this->info("Starting image download process...");
        $this->info("Source URL: {$this->sourceUrl}");
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

        $products = DB::table('products')
            ->select('productid', 'productcode', 'photo1', 'photo2', 'photo3', 'photo4', 'photo5', 'shortdescr')
            ->where('ispublished', 1)
            ->get();

        $images = [];
        foreach ($products as $product) {
            for ($i = 1; $i <= 5; $i++) {
                $photoField = 'photo' . $i;
                if (!empty($product->$photoField)) {
                    $images[] = [
                        'filename' => $product->$photoField,
                        'productid' => $product->productid,
                        'productcode' => $product->productcode,
                        'type' => $photoField
                    ];
                }
            }
        }

        $this->info("Found " . count($images) . " product images to check");

        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        $subdirectory = 'product';
        $storagePath = storage_path('app/public/upload/' . $subdirectory);

        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info("Created directory: {$storagePath}");
        }

        foreach ($images as $image) {
            $filename = $image['filename'];
            $localPath = $storagePath . '/' . $filename;

            if (File::exists($localPath)) {
                $this->skipped++;
                $bar->advance();
                continue;
            }

            if ($checkOnly) {
                $this->newLine();
                $this->warn("Missing: {$filename} (Product: {$image['productcode']})");
                $this->failed++;
                $bar->advance();
                continue;
            }

            $sourceUrl = $this->sourceUrl . '/storage/upload/' . $subdirectory . '/' . $filename;
            $oldSourceUrl = $this->sourceUrl . '/resources/storage/' . $filename;

            $downloaded = false;

            if ($this->downloadImage($sourceUrl, $localPath, $filename)) {
                $this->downloaded++;
                $downloaded = true;
            } else {
                $this->line("  Trying old path for: {$filename}");
                if ($this->downloadImage($oldSourceUrl, $localPath, $filename)) {
                    $this->downloaded++;
                    $downloaded = true;
                }
            }

            if (!$downloaded) {
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
                $images[] = ['filename' => $item->photo, 'type' => 'photo'];
            }
            if (!empty($item->photo_mobile)) {
                $images[] = ['filename' => $item->photo_mobile, 'type' => 'photo_mobile'];
            }
            if (!empty($item->photo_ar)) {
                $images[] = ['filename' => $item->photo_ar, 'type' => 'photo_ar'];
            }
            if (!empty($item->photo_mobile_ar)) {
                $images[] = ['filename' => $item->photo_mobile_ar, 'type' => 'photo_mobile_ar'];
            }
        }

        $this->info("Found " . count($images) . " home gallery images to check");

        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        $subdirectory = 'homegallery';
        $storagePath = storage_path('app/public/upload/' . $subdirectory);

        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info("Created directory: {$storagePath}");
        }

        foreach ($images as $image) {
            $filename = $image['filename'];
            $localPath = $storagePath . '/' . $filename;

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

            $sourceUrl = $this->sourceUrl . '/storage/upload/' . $subdirectory . '/' . $filename;
            $oldSourceUrl = $this->sourceUrl . '/resources/storage/' . $filename;

            $downloaded = false;

            if ($this->downloadImage($sourceUrl, $localPath, $filename)) {
                $this->downloaded++;
                $downloaded = true;
            } else {
                $this->line("  Trying old path for: {$filename}");
                if ($this->downloadImage($oldSourceUrl, $localPath, $filename)) {
                    $this->downloaded++;
                    $downloaded = true;
                }
            }

            if (!$downloaded) {
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
            ->select('categoryid', 'categorycode', 'category', 'photo', 'photo_mobile')
            ->where('ispublished', 1)
            ->get();

        $images = [];
        foreach ($categories as $category) {
            if (!empty($category->photo)) {
                $images[] = [
                    'filename' => $category->photo,
                    'categoryid' => $category->categoryid,
                    'category' => $category->category,
                    'type' => 'photo'
                ];
            }
            if (!empty($category->photo_mobile)) {
                $images[] = [
                    'filename' => $category->photo_mobile,
                    'categoryid' => $category->categoryid,
                    'category' => $category->category,
                    'type' => 'photo_mobile'
                ];
            }
        }

        $this->info("Found " . count($images) . " category images to check");

        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        $subdirectory = 'category';
        $storagePath = storage_path('app/public/upload/' . $subdirectory);

        // Create directory if it doesn't exist
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info("Created directory: {$storagePath}");
        }

        foreach ($images as $image) {
            $filename = $image['filename'];
            $localPath = $storagePath . '/' . $filename;

            // Check if file already exists
            if (File::exists($localPath)) {
                $this->skipped++;
                $bar->advance();
                continue;
            }

            if ($checkOnly) {
                $this->newLine();
                $this->warn("Missing: {$filename} (Category: {$image['category']})");
                $this->failed++;
                $bar->advance();
                continue;
            }

            // Try new path first: /storage/upload/category/filename
            $sourceUrl = $this->sourceUrl . '/storage/upload/' . $subdirectory . '/' . $filename;

            // If that fails, try old path: /resources/storage/filename
            $oldSourceUrl = $this->sourceUrl . '/resources/storage/' . $filename;

            $downloaded = false;

            // Try new path first
            if ($this->downloadImage($sourceUrl, $localPath, $filename)) {
                $this->downloaded++;
                $downloaded = true;
            } else {
                // Fallback to old path
                $this->line("  Trying old path for: {$filename}");
                if ($this->downloadImage($oldSourceUrl, $localPath, $filename)) {
                    $this->downloaded++;
                    $downloaded = true;
                }
            }

            if (!$downloaded) {
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

            if ($response->successful() && $response->header('Content-Type') && strpos($response->header('Content-Type'), 'image') !== false) {
                File::put($localPath, $response->body());
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}