# View Tables Implementation - Hide Add/Edit Buttons

## Overview
Some database tables are actually **views** (not real tables), which means they cannot be edited or deleted. This implementation automatically hides Add/Edit buttons in admin forms when the table is a view.

## Views List
The following tables are views (combined from both databases):

- `addressbook`
- `areamaster`
- `customercoupon`
- `customers`
- `customers_devices`
- `filtermaster`
- `filtervalues`
- `productpriceview`
- `stores`

**Note**: Views are read-only, so Add/Edit/Delete buttons are hidden automatically.

---

## Files Created

### 1. `app/Traits/ChecksTableView.php`
Trait that checks if a table name is a view.

**Methods:**
- `isTableView(string $tableName): bool` - Check if table is a view
- `getViewList(): array` - Get all view names

### 2. `app/Helpers/ViewHelper.php`
Helper class for Blade templates to check if a table is a view.

**Methods:**
- `isView(string $tableName): bool` - Static method for Blade usage

---

## Files Modified

### 1. `app/Providers/AppServiceProvider.php`
- Added Blade directive `@isView` (optional, not used yet)
- Registered ViewHelper for use in Blade

### 2. Blade Files Updated

#### Areas (areamaster view)
- `resources/views/admin/masters/areas.blade.php` - Hide "Add Area" button
- `resources/views/admin/masters/partials/area/areas-table.blade.php` - Hide Edit/Delete buttons

#### Customers (customers view)
- `resources/views/admin/customers.blade.php` - Hide "Add Customer" button

#### Products (productpriceview view)
- `resources/views/admin/products/index.blade.php` - Hide "Add Product" button
- `resources/views/admin/partials/products-table.blade.php` - Hide Edit/Delete buttons

#### Coupon Codes (customercoupon view)
- `resources/views/admin/masters/coupon-code.blade.php` - Hide "Add Coupon Code" button
- `resources/views/admin/masters/partials/coupon/coupon-code-table.blade.php` - Hide Edit/Delete buttons

---

## Usage in Blade Templates

### Basic Usage
```blade
@unless(\App\Helpers\ViewHelper::isView('tablename'))
    <a href="#" class="btn btn-success">Add Item</a>
@endunless
```

### In Table Actions
```blade
<div class="d-flex gap-1">
    <a href="#" class="btn btn-light view-btn">View</a>
    @unless(\App\Helpers\ViewHelper::isView('tablename'))
        <a href="#" class="btn btn-light edit-btn">Edit</a>
        <a href="#" class="btn btn-light delete-btn">Delete</a>
    @endunless
</div>
```

---

## How It Works

1. **ViewHelper** checks if the table name exists in the view list
2. If it's a view, `@unless` directive hides the Add/Edit buttons
3. View buttons remain visible (read-only access)

---

## Adding New Views

To add a new view to the list, edit `app/Traits/ChecksTableView.php`:

```php
protected static $viewList = [
    'addressbook',
    'areamaster',
    // ... existing views ...
    'new_view_name',  // Add here
];
```

Then update the relevant Blade files to use `@unless(\App\Helpers\ViewHelper::isView('new_view_name'))`.

---

## Summary

✅ **Trait Created**: `ChecksTableView` - Checks if table is a view  
✅ **Helper Created**: `ViewHelper` - Blade-friendly helper  
✅ **Blade Files Updated**: 6 files updated to hide Add/Edit buttons  
✅ **Views Protected**: 9 views are now protected from editing  

The system automatically hides Add/Edit buttons for views, preventing users from trying to edit read-only data.

