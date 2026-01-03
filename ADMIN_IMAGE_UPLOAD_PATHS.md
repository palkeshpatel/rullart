# Admin Side Image Upload Paths - Complete Documentation

This document details where every uploaded image goes in the admin section of the application.

## Base Upload Directory
All images are stored in the `public/uploads/` directory. The full path is:
```
public/uploads/{module_name}/
```

---

## 1. Category Images
**Controller:** `CategoryController.php`  
**Upload Path:** `public/uploads/category/`

### Images Uploaded:
- **Desktop Photo** (`photo`)
  - Naming: `{timestamp}_{original_filename}`
  - Example: `1703123456_category_image.jpg`
  - Max Size: 5MB
  - Formats: jpeg, png, jpg, gif

- **Mobile Photo** (`photo_mobile`)
  - Naming: `{timestamp}_mobile_{original_filename}`
  - Example: `1703123456_mobile_category_image.jpg`
  - Max Size: 5MB
  - Formats: jpeg, png, jpg, gif

### Code Location:
- **Store:** Lines 129-147
- **Update:** Lines 333-359
- **Delete:** Old images are deleted when updating (lines 341-343, 352-354)

---

## 2. Product Images
**Controller:** `ProductController.php`  
**Upload Path:** `public/uploads/products/`

### Images Uploaded:
- **Product Photos** (`photo1`, `photo2`, `photo3`, `photo4`, `photo5`)
  - Naming: `{timestamp}_{uniqid}_photo{number}.{extension}`
  - Example: `1703123456_abc123_photo1.jpg`
  - Max Size: 5MB each
  - Formats: jpeg, png, jpg, gif
  - Up to 5 photos per product

- **Video File** (`video_file`)
  - Naming: `{timestamp}_{uniqid}_video.{extension}`
  - Example: `1703123456_abc123_video.mp4`
  - Stored in same directory as product images

- **Video Poster** (`videoposter_file`)
  - Naming: `{timestamp}_{uniqid}_poster.{extension}`
  - Example: `1703123456_abc123_poster.jpg`
  - Max Size: 5MB
  - Formats: jpeg, png, jpg, gif

### Code Location:
- **Store:** Lines 176-207
- **Update:** Lines 501-545
- **Delete:** Old images are deleted when updating (lines 510-512, 526-528, 538-540)
- **Destroy:** All photos deleted on product deletion (lines 662-667)

---

## 3. Gift Product Images
**Controller:** `GiftProductController.php`  
**Upload Path:** `public/uploads/products/` (Same as regular products)

### Images Uploaded:
- **Product Photos** (`photo1`, `photo2`, `photo3`, `photo4`, `photo5`)
  - Naming: `{timestamp}_{uniqid}_photo{number}.{extension}`
  - Example: `1703123456_abc123_photo1.jpg`
  - Max Size: 5MB each
  - Formats: jpeg, png, jpg, gif
  - Up to 5 photos per gift product

### Code Location:
- **Store:** Lines 148-163
- **Update:** Lines 367-387
- **Delete:** Old images are deleted when updating (lines 376-378)
- **Destroy:** All photos deleted on gift product removal (lines 455-460)

---

## 4. Home Gallery Images
**Controller:** `HomeGalleryController.php`  
**Upload Path:** `public/uploads/homegallery/`

### Images Uploaded:
- **Desktop Photo** (`photo`)
  - Naming: `{timestamp}_{original_filename}`
  - Example: `1703123456_gallery_image.jpg`
  - Max Size: 2MB
  - Formats: jpeg, png, jpg, gif

- **Mobile Photo** (`photo_mobile`)
  - Naming: `{timestamp}_mobile_{original_filename}`
  - Example: `1703123456_mobile_gallery_image.jpg`
  - Max Size: 2MB
  - Formats: jpeg, png, jpg, gif

- **Arabic Desktop Photo** (`photo_ar`)
  - Naming: `{timestamp}_ar_{original_filename}`
  - Example: `1703123456_ar_gallery_image.jpg`
  - Max Size: 2MB
  - Formats: jpeg, png, jpg, gif

- **Arabic Mobile Photo** (`photo_mobile_ar`)
  - Naming: `{timestamp}_mobile_ar_{original_filename}`
  - Example: `1703123456_mobile_ar_gallery_image.jpg`
  - Max Size: 2MB
  - Formats: jpeg, png, jpg, gif

### Code Location:
- **Store:** Lines 114-146
- **Update:** Lines 327-367
- **Delete:** All photos deleted on gallery item deletion (lines 402-413)

---

## 5. Page Images
**Controller:** `PageController.php`  
**Upload Path:** `public/uploads/pages/`

### Images Uploaded:
- **Page Photo** (`photo`)
  - Naming: `{timestamp}_{original_filename}`
  - Example: `1703123456_page_image.jpg`
  - Max Size: 2MB
  - Formats: jpeg, png, jpg, gif
  - Used for various pages: home, aboutus, corporate-gift, franchises, contactus, shipping, newsletter, terms

### Code Location:
- **Update:** Lines 120-129
- **Delete:** Old photo deleted when updating (lines 122-124)

---

## 6. Occasion Images
**Controller:** `OccassionController.php`  
**Upload Path:** `public/uploads/occassion/`

### Images Uploaded:
- **Desktop Photo** (`photo`)
  - Naming: `{timestamp}_{original_filename}`
  - Example: `1703123456_occasion_image.jpg`
  - Max Size: 5MB
  - Formats: jpeg, png, jpg, gif

- **Mobile Photo** (`photo_mobile`)
  - Naming: `{timestamp}_mobile_{original_filename}`
  - Example: `1703123456_mobile_occasion_image.jpg`
  - Max Size: 5MB
  - Formats: jpeg, png, jpg, gif

### Code Location:
- **Store:** Lines 94-112
- **Update:** Lines 276-302
- **Delete:** Old images are deleted when updating (lines 284-286, 295-297)
- **Destroy:** All photos deleted on occasion deletion (lines 367-372)

---

## Summary Table

| Module | Upload Directory | Image Types | Max Size | Naming Pattern |
|--------|-----------------|-------------|----------|----------------|
| **Category** | `public/uploads/category/` | photo, photo_mobile | 5MB | `{timestamp}_{original}` or `{timestamp}_mobile_{original}` |
| **Product** | `public/uploads/products/` | photo1-5, video, videoposter | 5MB | `{timestamp}_{uniqid}_photo{num}.{ext}` |
| **Gift Product** | `public/uploads/products/` | photo1-5 | 5MB | `{timestamp}_{uniqid}_photo{num}.{ext}` |
| **Home Gallery** | `public/uploads/homegallery/` | photo, photo_mobile, photo_ar, photo_mobile_ar | 2MB | `{timestamp}_{original}` with prefixes |
| **Page** | `public/uploads/pages/` | photo | 2MB | `{timestamp}_{original}` |
| **Occasion** | `public/uploads/occassion/` | photo, photo_mobile | 5MB | `{timestamp}_{original}` or `{timestamp}_mobile_{original}` |

---

## Important Notes

1. **Directory Creation:** All controllers automatically create the upload directory if it doesn't exist using `mkdir($uploadPath, 0755, true)`

2. **File Deletion on Update:** When updating records, old images are automatically deleted before new ones are uploaded to prevent orphaned files

3. **File Deletion on Destroy:** When deleting records, associated image files are also deleted from the filesystem

4. **Naming Convention:** 
   - Most images use: `{timestamp}_{original_filename}`
   - Products use: `{timestamp}_{uniqid}_photo{number}.{extension}` for better uniqueness
   - Mobile versions have `_mobile_` prefix
   - Arabic versions have `_ar_` prefix

5. **File Permissions:** Directories are created with `0755` permissions

6. **Validation:** All image uploads are validated for:
   - File type (must be image)
   - MIME types (jpeg, png, jpg, gif)
   - Maximum file size

---

## Access URLs

Images can be accessed via:
```
http://your-domain.com/uploads/{module}/{filename}
```

For example:
- Category: `http://your-domain.com/uploads/category/1703123456_category_image.jpg`
- Product: `http://your-domain.com/uploads/products/1703123456_abc123_photo1.jpg`
- Home Gallery: `http://your-domain.com/uploads/homegallery/1703123456_gallery_image.jpg`

---

## Database Storage

The image filenames (not full paths) are stored in the database:
- Category: `photo`, `photo_mobile` columns
- Product: `photo1`, `photo2`, `photo3`, `photo4`, `photo5`, `video`, `videoposter` columns
- Home Gallery: `photo`, `photo_mobile`, `photo_ar`, `photo_mobile_ar` columns
- Page: `photo` column
- Occasion: `photo`, `photo_mobile` columns

