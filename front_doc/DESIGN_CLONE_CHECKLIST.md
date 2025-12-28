# Design Clone Checklist - CI to Laravel Migration

## Overview
This document tracks the design and functionality matching between the original CI project and the Laravel implementation.

## ✅ Completed Matching

### 1. Helper Functions
- ✅ `converted_value()` - Matches CI implementation exactly
- ✅ `show_number()` - Matches CI implementation exactly
- ✅ `isDecimal()` - Added for compatibility

### 2. Frontend Structure
- ✅ Routes structure with language support (`/en/`, `/ar/`)
- ✅ Base FrontendController with common functionality
- ✅ Middleware for locale and currency
- ✅ Main layout structure

### 3. Homepage View
- ✅ Hero slider with video support
- ✅ Mobile image detection
- ✅ Popular products section
- ✅ Product price formatting with currency conversion
- ✅ Product discount display
- ✅ Sold out badge
- ✅ Image URLs using `image_url` (not `resource_url`) for products

### 4. Configuration
- ✅ `resource_url` and `image_url` config added
- ✅ Default country and currency config

## ⚠️ Needs Attention

### 1. Product Model Fields
The Product model needs these fields accessible:
- ✅ `photo1` - Added to fillable
- ⚠️ `qty` - This is calculated from productsfilter table (needs to be added to query)
- ⚠️ `categorycode` - Available via relationship, needs proper loading
- ⚠️ `discount`, `sellingprice` - May need to check if these come from productpriceview

### 2. Homepage Product Query
The CI `get_popular()` method:
- Uses `productpriceview` for prices (discount, sellingprice)
- Calculates `qty` from `productsfilter` table where filtercode='size'
- Joins with category to get categorycode
- Uses complex query with subquery for qty

**Current Laravel Implementation:**
- Simple query - needs to be enhanced to match CI logic

### 3. Header Navigation
- ✅ Category menu
- ✅ Occasion menu  
- ⚠️ Gifting menu (needs `giftPackage()` function implementation)
- ✅ User menu
- ⚠️ Currency selector (needs full implementation)
- ⚠️ Search functionality

### 4. Footer
- ✅ Social media links
- ✅ App store links
- ✅ Footer navigation
- ✅ Copyright

## 📋 To Do for Complete Match

### Priority 1 - Critical for Design Match
1. **Product Query Enhancement**
   - Update HomeController to query products with proper joins
   - Add qty calculation from productsfilter
   - Use productpriceview for prices if it exists

2. **Header Navigation**
   - Implement gift package menu (if needed)
   - Complete currency selector dropdown
   - Add search overlay functionality

3. **Asset Files**
   - Copy all CSS files from CI to Laravel public/resources/styles/
   - Copy all JS files to public/resources/scripts/
   - Copy all images to public/resources/images/
   - Ensure all paths match CI structure

### Priority 2 - Important Features
4. **Product Display**
   - Ensure all product fields are accessible
   - Match exact HTML structure from CI
   - Verify image paths (thumb- prefix for product images)

5. **Price Display**
   - Verify currency conversion works correctly
   - Match price formatting exactly (3 decimals for KWD)
   - Ensure discount percentage displays correctly

6. **Mobile Responsiveness**
   - Ensure mobile image detection works
   - Verify responsive classes match CI

## 🔍 Key Differences to Watch

### URL Structure
- CI: Uses `site_url()` helper
- Laravel: Uses `route()` helper - Need to ensure URLs match exactly

### Image URLs
- **Product Images**: Use `$imageUrl` (config('app.image_url'))
- **Other Assets**: Use `$resourceUrl` (config('app.resource_url'))
- Both should point to `/resources/` directory

### Database Queries
- CI: Uses complex joins and subqueries
- Laravel: Need to replicate exact same queries using Eloquent or DB facade
- Especially important for product prices (productpriceview) and qty (productsfilter)

## ✅ Design Elements Matched

1. ✅ HTML structure for hero slider
2. ✅ HTML structure for product items
3. ✅ CSS classes match exactly
4. ✅ Image paths format
5. ✅ Price formatting
6. ✅ Discount display
7. ✅ Sold out badge
8. ✅ Mobile/desktop image switching

## Notes

- Always check CI views first before implementing Laravel views
- Match HTML structure exactly - don't change class names or structure
- Use same image URL paths as CI
- Ensure all helper functions match CI implementation
- Test on both mobile and desktop views
- Verify currency conversion matches CI behavior

---

**Last Updated:** {{ date('Y-m-d') }}
**Status:** In Progress - Core structure complete, details need refinement

