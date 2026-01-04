# Rullart API Documentation

This API documentation matches the CodeIgniter API structure from `/application/controllers/api4/`.

## Base URL

```
http://127.0.0.1:8000/{locale}/api/  (Kuwait - Port 8000)
http://127.0.0.1:9000/{locale}/api/  (Qatar - Port 9000)
```

Where `{locale}` is either `en` or `ar`.

## Response Format

All API responses follow this format:

### Success Response

```json
{
    "status": true,
    "data": { ... }
}
```

### Error Response

```json
{
    "status": false,
    "msg": "Error message"
}
```

---

## API Endpoints

### 1. Home API

#### Get Home Data

Get home gallery and popular products.

**Endpoint:** `GET /{locale}/api/home/get`

**Query Parameters:**

-   `customerid` (optional, default: 0) - Customer ID
-   `productview` (optional) - Product view type

**Example Request:**

```
GET /en/api/home/get?customerid=0
```

**Example Response:**

```json
{
    "status": true,
    "data": {
        "homegallery": [
            {
                "homegalleryid": 1,
                "link": "category-code",
                "photo": "image.jpg",
                "photo_mobile": "image-mobile.jpg",
                "videourl": "",
                "displayorder": 1,
                "title": "",
                "titleAR": "",
                "descr": "",
                "descrAR": ""
            }
        ],
        "popularproducts": [
            {
                "productid": 1,
                "title": "Product Title",
                "productcode": "PROD-001",
                "price": 100.0,
                "photo1": "thumb-image.jpg",
                "shortdescr": "Short Description",
                "categorycode": "category-code",
                "qty": 10,
                "discount": 0,
                "sellingprice": 100.0
            }
        ]
    }
}
```

#### Get Shop By Categories

Get categories for shop by menu.

**Endpoint:** `GET /{locale}/api/home/shopby`

**Query Parameters:**

-   `customerid` (optional, default: 0) - Customer ID

**Example Request:**

```
GET /en/api/home/shopby?customerid=0
```

**Example Response:**

```json
{
    "status": true,
    "data": [
        {
            "menuname": "BY CATEGORIES",
            "menulist": [
                {
                    "category": "Category Name",
                    "categoryid": 1,
                    "categorycode": "category-code",
                    "photo": "photo.jpg"
                }
            ],
            "menuid": 1
        },
        {
            "menuname": "SALE",
            "menulist": [ ... ],
            "menuid": 2
        },
        {
            "menuname": "WHATS NEW",
            "menulist": [ ... ],
            "menuid": 3
        }
    ]
}
```

---

### 2. Category API

#### Get Category Products

Get products by category with filters.

**Endpoint:** `GET /{locale}/api/category/data`

**Query Parameters:**

-   `category` (optional) - Category code (use 'all' for all products)
-   `currencycode` (optional, default: "KWD") - Currency code
-   `currencyrate` (optional, default: 1) - Currency rate
-   `occassion` (optional) - Occasion filter
-   `sortby` (optional, default: "relevance") - Sort by: relevance, lowtohigh, hightolow, name
-   `color` (optional) - Color filter (comma-separated)
-   `size` (optional) - Size filter (comma-separated)
-   `price` (optional) - Price range (format: "min-max")
-   `page` (optional, default: 1) - Page number
-   `search` (optional) - Search keyword
-   `isnew` (optional, default: 0) - Filter new products (1 = yes)
-   `issale` (optional, default: 0) - Filter sale products (1 = yes)
-   `firstload` (optional, default: 1) - First load flag
-   `main` (optional, default: 0) - Main category filter
-   `customerid` (optional, default: 0) - Customer ID

**Example Request:**

```
GET /en/api/category/data?category=wooden-sets&currencycode=KWD&currencyrate=1&page=1&customerid=0
```

**Example Response:**

```json
{
    "status": true,
    "data": {
        "products": [
            {
                "productid": 1,
                "title": "Product Title",
                "productcode": "PROD-001",
                "price": 100.00,
                "photo1": "image.jpg",
                "categorycode": "category-code",
                "qty": 10,
                "discount": 0,
                "sellingprice": 100.00
            }
        ],
        "productcnt": 50,
        "totalpage": 3,
        "category": {
            "categoryid": 1,
            "category": "Category Name",
            "categorycode": "category-code"
        },
        "subcategories": [ ... ],
        "colorsArr": [ ... ],
        "sizesArr": [ ... ],
        "pricerange": [ ... ]
    }
}
```

---

### 3. Product API

#### Get Product Details

Get detailed product information.

**Endpoint:** `GET /{locale}/api/product/{productcode}`

**URL Parameters:**

-   `productcode` - Product code (can use underscore, will be converted to dash)

**Query Parameters:**

-   `customerid` (optional, default: 0) - Customer ID

**Example Request:**

```
GET /en/api/product/product-code-123?customerid=0
```

**Example Response:**

```json
{
    "status": true,
    "data": {
        "product": {
            "productid": 1,
            "productcode": "product-code-123",
            "title": "Product Title",
            "shortdescr": "Short Description",
            "longdescr": "Long Description",
            "price": 100.00,
            "discount": 10.00,
            "sellingprice": 90.00,
            "photo1": "image1.jpg",
            "photo2": "image2.jpg",
            "photo3": "image3.jpg",
            "photo4": "image4.jpg",
            "photo5": "image5.jpg",
            "categorycode": "category-code",
            "qty": 10,
            "ratecount": 5,
            "rateavg": 4,
            "shareurl": "https://www.rullart.com/en/product/category-code/product-code-123"
        },
        "photos": [
            "detail-image1.jpg",
            "detail-image2.jpg"
        ],
        "rating": [ ... ],
        "sizes": [ ... ],
        "colors": [ ... ]
    }
}
```

---

### 4. Shopping Cart API

#### Get Cart

Get shopping cart items.

**Endpoint:** `POST /{locale}/api/shoppingcart/get`

**Request Body:**

```json
{
    "customerid": 0,
    "sessionid": "",
    "shippingcountryid": 1
}
```

**Example Response:**

```json
{
    "status": true,
    "data": {
        "cartid": 123,
        "items": [ ... ],
        "subtotal": 100.00,
        "shipping": 5.00,
        "vat": 5.00,
        "total": 110.00
    }
}
```

#### Add to Cart

Add product to cart.

**Endpoint:** `POST /{locale}/api/shoppingcart/add`

**Request Body:**

```json
{
    "customerid": 0,
    "sessionid": "",
    "productid": 1,
    "size": 5,
    "qty": 1,
    "giftproductid": 0,
    "giftproductid2": 0,
    "giftproductid3": 0,
    "giftproductid4": 0
}
```

#### Update Cart Item

Update cart item quantity.

**Endpoint:** `POST /{locale}/api/shoppingcart/update`

**Request Body:**

```json
{
    "cartitemid": 123,
    "qty": 2
}
```

#### Delete Cart Item

Remove item from cart.

**Endpoint:** `POST /{locale}/api/shoppingcart/delete`

**Request Body:**

```json
{
    "cartitemid": 123
}
```

#### Clear Cart

Clear all items from cart.

**Endpoint:** `POST /{locale}/api/shoppingcart/clear`

**Request Body:**

```json
{
    "customerid": 0,
    "sessionid": ""
}
```

---

### 5. Customer API

#### Get Customer by ID

Get customer information.

**Endpoint:** `GET /{locale}/api/customer/get_by_id`

**Query Parameters:**

-   `customerid` - Customer ID

**Example Response:**

```json
{
    "status": true,
    "data": {
        "customerid": 1,
        "firstname": "John",
        "lastname": "Doe",
        "email": "john@example.com",
        "login_type": "email"
    }
}
```

#### Customer Login

Login customer.

**Endpoint:** `POST /{locale}/api/customer/login`

**Request Body:**

```json
{
    "email": "customer@example.com",
    "password": "password123"
}
```

#### Customer Register

Register new customer.

**Endpoint:** `POST /{locale}/api/customer/register`

**Request Body:**

```json
{
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "1234567890"
}
```

#### Update Customer

Update customer information.

**Endpoint:** `POST /{locale}/api/customer/update`

**Request Body:**

```json
{
    "customerid": 1,
    "firstname": "John",
    "lastname": "Doe",
    "email": "john@example.com",
    "phone": "1234567890"
}
```

#### Forgot Password

Request password reset.

**Endpoint:** `POST /{locale}/api/customer/forgot_password`

**Request Body:**

```json
{
    "email": "customer@example.com"
}
```

---

### 6. Address Book API

#### Get Addresses

Get customer addresses.

**Endpoint:** `GET /{locale}/api/addressbook/get`

**Query Parameters:**

-   `customerid` - Customer ID

#### Add Address

Add new address.

**Endpoint:** `POST /{locale}/api/addressbook/add`

**Request Body:**

```json
{
    "customerid": 1,
    "firstname": "John",
    "lastname": "Doe",
    "address": "Street Address",
    "area": "Area Name",
    "avenue": "Avenue Name",
    "block": "Block",
    "street": "Street",
    "building": "Building",
    "floor": "Floor",
    "apartment": "Apartment",
    "phone": "1234567890",
    "countryid": 1
}
```

#### Update Address

Update existing address.

**Endpoint:** `POST /{locale}/api/addressbook/update`

#### Delete Address

Delete address.

**Endpoint:** `POST /{locale}/api/addressbook/delete`

**Request Body:**

```json
{
    "addressbookid": 1
}
```

---

### 7. Areas API

#### Get Areas

Get list of areas.

**Endpoint:** `GET /{locale}/api/areas/get`

**Query Parameters:**

-   `countryid` (optional) - Country ID

---

### 8. Checkout API

#### Process Checkout

Process order checkout.

**Endpoint:** `POST /{locale}/api/checkout/process`

**Request Body:**

```json
{
    "customerid": 1,
    "sessionid": "",
    "shippingaddressid": 1,
    "billingaddressid": 1,
    "paymentmethod": "Knet",
    "shippingcountryid": 1
}
```

---

### 9. My Orders API

#### Get Orders

Get customer orders.

**Endpoint:** `GET /{locale}/api/myorders/get`

**Query Parameters:**

-   `customerid` - Customer ID

#### Get Order by ID

Get order details.

**Endpoint:** `GET /{locale}/api/myorders/get_by_id`

**Query Parameters:**

-   `customerid` - Customer ID
-   `orderid` - Order ID

---

### 10. My Profile API

#### Get Profile

Get customer profile.

**Endpoint:** `GET /{locale}/api/myprofile/get`

**Query Parameters:**

-   `customerid` - Customer ID

#### Update Profile

Update customer profile.

**Endpoint:** `POST /{locale}/api/myprofile/update`

---

### 11. My Addresses API

#### Get Addresses

Get customer addresses.

**Endpoint:** `GET /{locale}/api/myaddresses/get`

**Query Parameters:**

-   `customerid` - Customer ID

---

### 12. Search API

#### Search Products

Search for products.

**Endpoint:** `GET /{locale}/api/search/data`

**Query Parameters:**

-   `search` - Search keyword
-   `categorycode` (optional) - Category filter
-   `currencycode` (optional) - Currency code
-   `currencyrate` (optional) - Currency rate
-   `color` (optional) - Color filter
-   `size` (optional) - Size filter
-   `price` (optional) - Price range
-   `sortby` (optional) - Sort by
-   `page` (optional) - Page number
-   `customerid` (optional) - Customer ID

---

### 13. Wishlist API

#### Get Wishlist

Get customer wishlist.

**Endpoint:** `GET /{locale}/api/wishlist/get`

**Query Parameters:**

-   `customerid` - Customer ID

#### Add to Wishlist

Add product to wishlist.

**Endpoint:** `POST /{locale}/api/wishlist/add`

**Request Body:**

```json
{
    "customerid": 1,
    "productid": 1
}
```

#### Remove from Wishlist

Remove product from wishlist.

**Endpoint:** `POST /{locale}/api/wishlist/delete`

**Request Body:**

```json
{
    "customerid": 1,
    "productid": 1
}
```

---

### 14. Occasion API

#### Get Occasions

Get list of occasions.

**Endpoint:** `GET /{locale}/api/occassion/get`

---

### 15. Payment API

#### Process Payment

Process payment.

**Endpoint:** `POST /{locale}/api/payment/process`

---

### 16. Order Complete API

#### Process Order Complete

Process order completion.

**Endpoint:** `POST /{locale}/api/ordercomplete/process`

---

### 17. Thank You API

#### Get Thank You Data

Get thank you page data.

**Endpoint:** `GET /{locale}/api/thankyou/get`

**Query Parameters:**

-   `orderid` - Order ID

---

### 18. Page API

#### Get Page Content

Get page content.

**Endpoint:** `GET /{locale}/api/page/get`

**Query Parameters:**

-   `pagecode` - Page code

---

### 19. Gift Items API

#### Get Gift Items

Get gift items.

**Endpoint:** `GET /{locale}/api/giftitems/get`

---

### 20. Gift Titles API

#### Get Gift Titles

Get gift titles.

**Endpoint:** `GET /{locale}/api/gifttitles/get`

---

### 21. Product Rate API

#### Add Product Rating

Add product rating.

**Endpoint:** `POST /{locale}/api/productrate/add`

**Request Body:**

```json
{
    "customerid": 1,
    "productid": 1,
    "rate": 5,
    "comment": "Great product!"
}
```

---

### 22. Device API

#### Register Device

Register mobile device.

**Endpoint:** `POST /{locale}/api/device/register`

**Request Body:**

```json
{
    "customerid": 1,
    "deviceid": "device-unique-id",
    "devicetype": "ios",
    "devicetoken": "push-token"
}
```

---

### 23. Autocomplete API

#### Get Autocomplete Suggestions

Get autocomplete suggestions.

**Endpoint:** `GET /{locale}/api/autocomplete/get`

**Query Parameters:**

-   `keyword` - Search keyword

---

### 24. Avenue API

#### Get Avenues

Get list of avenues.

**Endpoint:** `GET /{locale}/api/avenue/get`

**Query Parameters:**

-   `areaid` (optional) - Area ID

---

### 25. Tabby Pay API

#### Process Tabby Payment

Process Tabby payment.

**Endpoint:** `POST /{locale}/api/tabbypay/process`

---

## Error Codes

All errors return HTTP 200 with `status: false`:

-   **Invalid customer ID or session ID** - Cart/Order operations
-   **No products found** - Product/Category searches
-   **Invalid product code** - Product details
-   **Authentication failed** - Customer login
-   **Validation errors** - Form submissions

## Notes

1. All endpoints support both `en` and `ar` locales
2. Customer ID of `0` means guest user
3. Session ID is used for guest cart tracking
4. Currency conversion is handled via `currencycode` and `currencyrate` parameters
5. Image paths are relative to `/storage/upload/` directory
6. Product images use `thumb-` prefix for thumbnails and `detail-` prefix for detail images

## Multi-Tenant Support

The API automatically switches databases based on the port:

-   **Port 8000** → Kuwait database (`rullart_rullart_kuwaitbeta`)
-   **Port 9000** → Qatar database (`rullart_rullart_qatarbeta`)

This is handled automatically by the `AppServiceProvider`.
