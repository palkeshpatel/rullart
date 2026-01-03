<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

trait ImageUploadTrait
{
    /**
     * Upload image to storage/app/public/upload/{subdirectory}/
     * 
     * @param UploadedFile|null $file
     * @param string|null $oldImage Old image filename to delete
     * @param string $subdirectory Subdirectory name (e.g., 'category', 'product')
     * @return string|null Uploaded filename or null
     */
    protected function uploadImage(?UploadedFile $file, ?string $oldImage = null, string $subdirectory = 'upload'): ?string
    {
        Log::info('ImageUploadTrait: uploadImage called', [
            'has_file' => $file !== null,
            'is_valid' => $file ? $file->isValid() : false,
            'old_image' => $oldImage,
            'subdirectory' => $subdirectory,
        ]);

        if (!$file || !$file->isValid()) {
            Log::warning('ImageUploadTrait: Invalid file or file not provided', [
                'file' => $file ? $file->getClientOriginalName() : 'null',
                'is_valid' => $file ? $file->isValid() : false,
            ]);
            return null;
        }

        // Delete old image if exists
        if ($oldImage) {
            Log::info('ImageUploadTrait: Deleting old image', ['old_image' => $oldImage, 'subdirectory' => $subdirectory]);
            $this->deleteImage($oldImage, $subdirectory);
        }

        // Generate filename: timestamp_originalname
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Build storage path with subdirectory
        $storagePath = 'upload/' . $subdirectory;
        
        Log::info('ImageUploadTrait: Preparing to upload', [
            'original_name' => $file->getClientOriginalName(),
            'generated_filename' => $filename,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'storage_path' => 'storage/app/public/' . $storagePath,
            'subdirectory' => $subdirectory,
        ]);

        // Ensure upload directory exists
        $uploadPath = storage_path('app/public/' . $storagePath);
        if (!file_exists($uploadPath)) {
            Log::info('ImageUploadTrait: Creating upload directory', ['path' => $uploadPath]);
            mkdir($uploadPath, 0755, true);
        }

        // Store in storage/app/public/upload/{subdirectory}/
        try {
            $storedPath = $file->storeAs($storagePath, $filename, 'public');
            
            Log::info('ImageUploadTrait: File stored successfully', [
                'stored_path' => $storedPath,
                'full_path' => storage_path('app/public/' . $storedPath),
                'file_exists' => Storage::disk('public')->exists($storagePath . '/' . $filename),
            ]);

            // Verify file was actually saved
            $fullPath = storage_path('app/public/' . $storagePath . '/' . $filename);
            if (file_exists($fullPath)) {
                Log::info('ImageUploadTrait: File verified on disk', [
                    'full_path' => $fullPath,
                    'file_size' => filesize($fullPath),
                ]);
            } else {
                Log::error('ImageUploadTrait: File NOT found on disk after upload', [
                    'expected_path' => $fullPath,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('ImageUploadTrait: Error storing file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
        
        return $filename;
    }

    /**
     * Delete image from storage
     * 
     * @param string|null $imageFilename
     * @param string $subdirectory Subdirectory name (e.g., 'category', 'product')
     * @return bool
     */
    protected function deleteImage(?string $imageFilename, string $subdirectory = 'upload'): bool
    {
        if (!$imageFilename) {
            Log::info('ImageUploadTrait: deleteImage called with null filename');
            return false;
        }

        $path = 'upload/' . $subdirectory . '/' . $imageFilename;
        $fullPath = storage_path('app/public/' . $path);
        
        Log::info('ImageUploadTrait: Attempting to delete image', [
            'filename' => $imageFilename,
            'path' => $path,
            'full_path' => $fullPath,
            'subdirectory' => $subdirectory,
            'exists_in_storage' => Storage::disk('public')->exists($path),
            'exists_on_disk' => file_exists($fullPath),
        ]);
        
        if (Storage::disk('public')->exists($path)) {
            $deleted = Storage::disk('public')->delete($path);
            Log::info('ImageUploadTrait: Delete result', [
                'deleted' => $deleted,
                'still_exists' => Storage::disk('public')->exists($path),
            ]);
            return $deleted;
        }

        Log::warning('ImageUploadTrait: Image not found for deletion', ['path' => $path]);
        return false;
    }

    /**
     * Remove image from model and storage
     * 
     * @param object $model
     * @param string $column Column name (e.g., 'photo', 'photo_mobile')
     * @param string $subdirectory Subdirectory name (e.g., 'category', 'product')
     * @return bool
     */
    protected function removeImageFromModel(object $model, string $column, string $subdirectory = 'upload'): bool
    {
        if (!isset($model->$column)) {
            return false;
        }

        $imageFilename = $model->$column;
        
        // Delete from storage
        $this->deleteImage($imageFilename, $subdirectory);
        
        // Update model - set column to empty string
        $model->update([$column => '']);
        
        return true;
    }

    /**
     * Upload multiple images (for products with photo1-5)
     * 
     * @param array $files Array of UploadedFile objects
     * @param object|null $model Existing model with old images
     * @param string $prefix Column prefix (e.g., 'photo' for photo1, photo2...)
     * @return array Array of uploaded filenames
     */
    protected function uploadMultipleImages(array $files, ?object $model = null, string $prefix = 'photo'): array
    {
        $uploaded = [];
        
        foreach ($files as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            
            $column = $prefix . ($index + 1); // photo1, photo2, etc.
            $oldImage = $model ? $model->$column : null;
            
            // Note: uploadMultipleImages doesn't support subdirectory yet
            // If needed, add $subdirectory parameter to this method
            $filename = $this->uploadImage($file, $oldImage);
            if ($filename) {
                $uploaded[$column] = $filename;
            }
        }
        
        return $uploaded;
    }

    /**
     * Get image URL
     * 
     * @param string|null $filename
     * @param string $subdirectory Subdirectory name (e.g., 'category', 'product')
     * @return string|null
     */
    protected function getImageUrl(?string $filename, string $subdirectory = 'upload'): ?string
    {
        if (!$filename) {
            return null;
        }
        
        return Storage::disk('public')->url('upload/' . $subdirectory . '/' . $filename);
    }
}

