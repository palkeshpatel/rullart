<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Get image URL - uses actual path from database (no fallback)
     * 
     * @param string $filename Original filename from database (e.g., "13642-IMG_1519.webp")
     * @param string $prefix Prefix to add (thumb-, detail-, etc.)
     * @return string Full image URL
     */
    public static function url($filename, $prefix = 'thumb-')
    {
        // Use filename as-is from database (even if file doesn't exist)
        if (empty($filename)) {
            $filename = '';
        }
        
        $imagePath = $prefix . $filename;
        
        // Use resources/storage folder for product images (matches CI project)
        // This will generate: http://127.0.0.1:8000/resources/storage/thumb-{filename}
        // Same as CI: $this->image_url . 'storage/thumb-' . $photo
        return url('resources/storage/' . $imagePath);
    }
}

