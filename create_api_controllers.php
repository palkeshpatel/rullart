<?php

/**
 * Create API Controller Stubs
 * This script creates stub controllers for all API endpoints
 */

$controllers = [
    'Product' => ['index'],
    'Shoppingcart' => ['get', 'add', 'update', 'delete', 'clear'],
    'Customer' => ['getById', 'login', 'register', 'update', 'forgotPassword'],
    'Addressbook' => ['get', 'add', 'update', 'delete'],
    'Areas' => ['get'],
    'Checkout' => ['process'],
    'Myorders' => ['get', 'getById'],
    'Myprofile' => ['get', 'update'],
    'Myaddresses' => ['get'],
    'Search' => ['data'],
    'Wishlist' => ['get', 'add', 'delete'],
    'Occassion' => ['get'],
    'Payment' => ['process'],
    'Ordercomplete' => ['process'],
    'Thankyou' => ['get'],
    'Page' => ['get'],
    'Giftitems' => ['get'],
    'Gifttitles' => ['get'],
    'Productrate' => ['add'],
    'Device' => ['register'],
    'Autocomplete' => ['get'],
    'Avenue' => ['get'],
    'Tabbypay' => ['process'],
];

$basePath = __DIR__ . '/app/Http/Controllers/Api/';

foreach ($controllers as $controllerName => $methods) {
    $className = $controllerName . 'Controller';
    $filePath = $basePath . $className . '.php';
    
    // Skip if file already exists
    if (file_exists($filePath)) {
        echo "Skipping {$className} - already exists\n";
        continue;
    }
    
    $methodsCode = '';
    foreach ($methods as $method) {
        $methodName = lcfirst($method);
        $methodsCode .= "    /**\n";
        $methodsCode .= "     * TODO: Implement this method\n";
        $methodsCode .= "     * Reference: /ruralt-ci/application/controllers/api4/{$controllerName}.php\n";
        $methodsCode .= "     */\n";
        $methodsCode .= "    public function {$methodName}(Request \$request)\n";
        $methodsCode .= "    {\n";
        $methodsCode .= "        return \$this->error('Not implemented yet. Please implement based on CI {$controllerName} controller.');\n";
        $methodsCode .= "    }\n\n";
    }
    
    $content = "<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class {$className} extends ApiController
{
{$methodsCode}
}
";
    
    file_put_contents($filePath, $content);
    echo "Created {$className}\n";
}

echo "\nDone! All stub controllers created.\n";

