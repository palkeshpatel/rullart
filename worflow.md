# Laravel Project File Structure

## Backend Structure (Admin)

### Controllers

```
app/Http/Controllers/
├── Controller.php (Base Controller)
├── RoutingController.php
│
├── Admin/
│   ├── AdminLoginController.php
│   ├── AreaController.php
│   ├── CategoryController.php
│   ├── ColorController.php
│   ├── CountryController.php
│   ├── CouponCodeController.php
│   ├── CourierCompanyController.php
│   ├── CustomerController.php
│   ├── DashboardController.php
│   ├── DiscountController.php
│   ├── GiftMessageController.php
│   ├── GiftProductController.php
│   ├── HomeGalleryController.php
│   ├── MobileDeviceController.php
│   ├── OccassionController.php
│   ├── OrderController.php
│   ├── PageController.php
│   ├── ProductController.php
│   ├── ProductRatingController.php
│   ├── ReturnRequestController.php
│   ├── SettingsController.php
│   ├── ShoppingCartController.php
│   ├── SizeController.php
│   ├── WishlistController.php
│   ├── Concerns/ (Empty)
│   └── Reports/
│       └── SalesReportController.php
│
└── Auth/ (Laravel Breeze Authentication)
    ├── AuthenticatedSessionController.php
    ├── ConfirmablePasswordController.php
    ├── EmailVerificationNotificationController.php
    ├── EmailVerificationPromptController.php
    ├── NewPasswordController.php
    ├── PasswordController.php
    ├── PasswordResetLinkController.php
    ├── RegisteredUserController.php
    └── VerifyEmailController.php
```

### Blade Views (Admin)

```
resources/views/admin/
├── dashboard.blade.php
├── login.blade.php
├── settings.blade.php
│
├── category/
│   ├── index.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── category-form.blade.php
│       ├── category-view.blade.php
│       └── modal.blade.php
│
├── occassion/
│   ├── index.blade.php
│   └── partials/
│       ├── occassion-form.blade.php
│       └── occassion-view.blade.php
│
├── products/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── partials/
│       ├── product-form-fields.blade.php
│       ├── product-form.blade.php
│       └── product-view.blade.php
│
├── gift-products/
│   ├── index.blade.php
│   └── partials/
│       ├── gift-product-form.blade.php
│       └── gift-product-view.blade.php
│
├── orders/
│   ├── index.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── modal.blade.php
│       └── table.blade.php
│
├── orders-not-process/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── modal.blade.php
│       └── table.blade.php
│
├── customers.blade.php
│
├── masters/
│   ├── areas.blade.php
│   ├── colors.blade.php
│   ├── countries.blade.php
│   ├── coupon-code.blade.php
│   ├── courier-company.blade.php
│   ├── discounts.blade.php
│   ├── messages.blade.php
│   ├── sizes.blade.php
│   └── partials/
│       ├── area/
│       │   ├── area-form.blade.php
│       │   ├── area-view.blade.php
│       │   └── areas-table.blade.php
│       ├── color/
│       │   ├── color-form.blade.php
│       │   ├── color-view.blade.php
│       │   └── colors-table.blade.php
│       ├── countries/
│       │   ├── country-form.blade.php
│       │   ├── country-view.blade.php
│       │   └── countries-table.blade.php
│       ├── coupon/
│       │   ├── coupon-code-form.blade.php
│       │   ├── coupon-code-table.blade.php
│       │   └── coupon-code-view.blade.php
│       ├── courier/
│       │   ├── courier-company-form.blade.php
│       │   ├── courier-company-table.blade.php
│       │   └── courier-company-view.blade.php
│       ├── discounts/
│       │   ├── discount-form.blade.php
│       │   ├── discount-view.blade.php
│       │   └── discounts-table.blade.php
│       ├── messages/
│       │   ├── message-form.blade.php
│       │   ├── message-view.blade.php
│       │   └── messages-table.blade.php
│       └── sizes/
│           ├── size-form.blade.php
│           ├── size-view.blade.php
│           └── sizes-table.blade.php
│
├── pages/
│   ├── home.blade.php
│   ├── aboutus.blade.php
│   ├── contactus.blade.php
│   ├── corporate-gift.blade.php
│   ├── franchises.blade.php
│   ├── shipping.blade.php
│   ├── terms.blade.php
│   ├── newsletter.blade.php
│   ├── home-gallery.blade.php
│   └── partials/
│       └── home-gallery/
│           ├── home-gallery-form.blade.php
│           ├── home-gallery-table.blade.php
│           └── home-gallery-view.blade.php
│
├── reports/
│   ├── sales-report-date.blade.php
│   ├── sales-report-month.blade.php
│   ├── sales-report-year.blade.php
│   ├── sales-report-customer.blade.php
│   ├── top-product-month.blade.php
│   ├── top-product-rate.blade.php
│   ├── partials/
│   │   ├── datewise-table.blade.php
│   │   ├── monthwise-table.blade.php
│   │   ├── yearwise-table.blade.php
│   │   ├── customerwise-table.blade.php
│   │   ├── top-product-month-table.blade.php
│   │   └── top-product-rate-table.blade.php
│   └── pdf/
│       ├── print.blade.php
│       └── report.blade.php
│
├── product-rate/
│   ├── index.blade.php
│   └── partials/
│       └── table.blade.php
│
├── return-request/
│   ├── index.blade.php
│   └── partials/
│       └── table.blade.php
│
├── mobile-device/
│   ├── index.blade.php
│   └── partials/
│       └── table.blade.php
│
├── wishlist/
│   ├── index.blade.php
│   └── partials/
│       └── table.blade.php
│
└── partials/
    ├── categories-table.blade.php
    ├── customers-table.blade.php
    ├── customer-form.blade.php
    ├── customer-view.blade.php
    ├── gift-products-table.blade.php
    ├── occassions-table.blade.php
    ├── orders-table.blade.php
    ├── products-table.blade.php
    ├── pagination.blade.php
    └── pdf-loader.blade.php
```

---

## Frontend Structure

### Controllers

```
app/Http/Controllers/Frontend/
├── CategoryController.php
├── CheckoutController.php
├── FrontendController.php
├── HomeController.php
├── KnetResponseController.php
├── LoginController.php
├── MyAddressesController.php
├── MyOrdersController.php
├── MyProfileController.php
├── OrderErrorController.php
├── PageController.php
├── PaymentController.php
├── ProductController.php
├── SearchController.php
├── ShoppingCartController.php
└── WishlistController.php
```

### Blade Views (Frontend)

```
resources/views/frontend/
├── layouts/
│   ├── app.blade.php
│   └── partials/
│       ├── header.blade.php
│       └── footer.blade.php
│
├── home/
│   └── index.blade.php
│
├── category/
│   ├── index.blade.php
│   └── sidefilter.blade.php
│
├── product/
│   └── show.blade.php
│
├── search/
│   └── index.blade.php
│
├── shoppingcart/
│   ├── index.blade.php
│   ├── content.blade.php
│   └── content-refactored.blade.php
│
├── wishlist/
│   └── index.blade.php
│
├── checkout/
│   └── index.blade.php
│
├── payment/
│   └── index.blade.php
│
├── ordererror/
│   └── index.blade.php
│
├── login/
│   ├── index.blade.php
│   └── register.blade.php
│
├── myprofile/
│   └── index.blade.php
│
├── myorders/
│   └── index.blade.php
│
├── myaddresses/
│   ├── index.blade.php
│   └── add.blade.php
│
├── page/
│   └── show.blade.php
│
└── errors/ (Error pages)
```

---

## Shared Layouts

```
resources/views/layouts/
├── base.blade.php
├── vertical.blade.php
├── horizontal.blade.php
└── partials/
    ├── customizer.blade.php
    ├── footer-scripts.blade.php
    ├── footer.blade.php
    ├── head-css.blade.php
    ├── horizontal-nav.blade.php
    ├── menu.blade.php
    ├── page-title.blade.php
    ├── sidenav.blade.php
    ├── title-meta.blade.php
    └── topbar.blade.php
```

---

## Summary

### Backend (Admin)

- **Controllers**: 25+ admin controllers + 1 reports controller
- **Main Sections**:
  - Category Management
  - Occasion Management
  - Product Management
  - Gift Product Management
  - Order Management
  - Customer Management
  - Master Data (Areas, Colors, Countries, Coupons, Courier, Discounts, Messages, Sizes)
  - Pages Management
  - Reports
  - Settings
  - Product Ratings
  - Return Requests
  - Mobile Devices
  - Wishlist

### Frontend

- **Controllers**: 15 frontend controllers
- **Main Sections**:
  - Home
  - Category Listing
  - Product Details
  - Search
  - Shopping Cart
  - Wishlist
  - Checkout
  - Payment
  - Order Error
  - Authentication (Login/Register)
  - User Account (Profile, Orders, Addresses)
  - Dynamic Pages

### Architecture Pattern

- **MVC Architecture**: Controllers handle logic, Models handle data, Views handle presentation
- **Partial Views**: Reusable components (forms, tables, modals) stored in `partials/` directories
- **Resource Controllers**: RESTful routing for CRUD operations
- **AJAX Integration**: Dynamic content loading and form submissions
- **Blade Templating**: Laravel's templating engine for views
