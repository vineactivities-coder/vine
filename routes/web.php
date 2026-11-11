<?php

use App\Http\Controllers\Admin\AreasController;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\DashUsersController;
use App\Http\Controllers\Admin\DriversController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LocationsController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\PaymentOptionsController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Frontend\BuyerController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\SiteController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\WebsiteInfoController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(["web"])->group(function () {
    Route::get('logout', [BuyerController::class, 'logout']);
    Route::get('wishlist', [SiteController::class, "wishlist"]);
    Route::post('wishlist', [SiteController::class, "wishlist"]);
    Route::get('bought/items', [BuyerController::class, "previousltyBoughtItems"]);
    Route::post('bought/items', [SiteController::class, "previousltyBoughtItems"]);
});

//wishlist routes
Route::post('wishlist/add', [BuyerController::class, "addToWishlist"]);

//cart routes
Route::get('checkout', [CartController::class, "checkout"]);
Route::post('checkout', [CartController::class, "checkout"]);
Route::get('cart', [CartController::class, "cart"]);
Route::get('api/cart', [CartController::class, "cartData"]);
Route::post('cart', [CartController::class, "submitCart"]);
Route::post('cart/add', [CartController::class, "add"]);
Route::post('cart/remove', [CartController::class, "remove"]);
Route::post('order/submit', [CartController::class, "submitOrder"])->name('confirmOrder');
Route::post('order/whatsapp/submit', [CartController::class, "sendWhatsappOrder"])->name('whatsappOrder');

//catalog functions
Route::get('shop', [SiteController::class, 'shop'])->name('all');
Route::get('shop/{id}', [SiteController::class, 'shop']);
Route::get("product/{id}", [SiteController::class, 'productPage']);

//User authentication routes
Route::get('/register', [BuyerController::class, 'loadRegister'])->name("register");
Route::post('/register', [BuyerController::class, 'register']);
Route::get('/forgetpass', [BuyerController::class, 'loadForgetPassPage'])->name("forgetpass");
Route::post('/forgetpass', [BuyerController::class, 'sendForgetPassMail']);
Route::get('/changepass/{encryptedID}', [BuyerController::class, 'changePassForm']);
Route::post('/changePass', [BuyerController::class, 'changePass']);
Route::get('/login', [BuyerController::class, 'loadLoginPage'])->name('login');
Route::post('/login', [BuyerController::class, 'login']);

Route::post('/googlelogin', [BuyerController::class, 'googleLogin'])->withoutMiddleware(ValidateCsrfToken::class);

Route::get('/home', [SiteController::class, 'home'])->name("home");
Route::get('/aboutus', [SiteController::class, 'aboutus'])->name("aboutus");
Route::get('/delivery', [SiteController::class, 'delivery'])->name("delivery");
Route::get('/payment', [SiteController::class, 'paymentPolicy'])->name("payment");
Route::get('/contact', [SiteController::class, 'contactus'])->name("contact");
Route::post('/contactus/sendemail', [SiteController::class, 'sendContactUsEmail']);
Route::get('/',  [SiteController::class, 'home']);



//////////////////////////////////////ADMIN ROUTES////////
Route::prefix('admin')->group(function () {

    Route::middleware(["auth:admin"])->group(function () {

        //Orders
        Route::get("orders/active", [OrdersController::class, 'active']);
        Route::get("orders/month", [OrdersController::class, 'monthly']);
        Route::get("orders/month/{id}", [OrdersController::class, 'monthly']);
        Route::get("orders/state/{id}", [OrdersController::class, 'state']);
        Route::get("orders/history/{year}/{month}", [OrdersController::class, 'history']);
        Route::get("orders/history/{year}/{month}/{state}", [OrdersController::class, 'history']);
        Route::get("orders/load/history", [OrdersController::class, 'loadHistory']);
        Route::get("orders/details/{id}", [OrdersController::class, 'details']);
        Route::get("orders/edit/details", [OrdersController::class, 'editOrderInfo']);
        Route::get("orders/set/new/{id}", [OrdersController::class, 'setNew']);
        Route::get("orders/set/ready/{id}", [OrdersController::class, 'setReady']);
        Route::get("orders/set/cancelled/{id}", [OrdersController::class, 'setCancelled']);
        Route::get("orders/set/indelivery/{id}", [OrdersController::class, 'setInDelivery']);
        Route::get("orders/set/delivered/{id}", [OrdersController::class, 'setDelivered']);
        Route::get("orders/create/return/{id}", [OrdersController::class, 'setPartiallyReturned']);
        Route::get("orders/return/{id}", [OrdersController::class, 'setFullyReturned']);
        Route::get("orders/toggle/item/{id}", [OrdersController::class, 'toggleItem']);
        Route::get("orders/delete/item/{id}", [OrdersController::class, 'deleteItem']);
        Route::post("orders/edit/details", [OrdersController::class, 'editOrderInfo']);
        Route::post("orders/collect/payment", [OrdersController::class, 'collectNormalPayment']);
        Route::post("orders/collect/delivery", [OrdersController::class, 'collectDeliveryPayment']);
        Route::post("orders/set/discount", [OrdersController::class, 'setDiscount']);
        Route::post("orders/assign/driver", [OrdersController::class, 'assignDriver']);
        Route::post("orders/add/items/{id}", [OrdersController::class, 'insertNewItems']);
        Route::get("orders/add", [OrdersController::class, 'addNew']);
        Route::post("orders/insert", [OrdersController::class, 'insert']);
        Route::post("orders/change/quantity", [OrdersController::class, 'changeQuantity']);


        //Inventory
        Route::get("inventory/new/entry", [InventoryController::class, 'entry']);
        Route::post("inventory/insert/entry", [InventoryController::class, 'insert']);
        Route::get("inventory/current/stock", [InventoryController::class, 'stock']);
        Route::get("inventory/transactions", [InventoryController::class, 'transactions']);
        Route::get("inventory/transaction/{code}", [InventoryController::class, 'transactionDetails']);
        Route::get("inventory/refresh", [InventoryController::class, 'refresh']);

        //Products 
        Route::get('products/show/all', [ProductsController::class, 'home']);
        Route::get('products/sale', [ProductsController::class, 'sale']);
        Route::get('products/new', [ProductsController::class, 'new']);
        Route::get('products/filter/category', [ProductsController::class, 'filterCategory']);
        Route::post('products/category', [ProductsController::class, 'showCategory']);
        Route::post('products/subcategory', [ProductsController::class, 'showSubCategory']);
        Route::get('products/show/catg/sub/{id}', [ProductsController::class, 'home']);
        Route::get('products/details/{id}', [ProductsController::class, 'details']);
        Route::get('products/add', [ProductsController::class, 'add']);
        Route::post('producs/add/image/{id}', [ProductsController::class, 'attachImage']);
        Route::get('products/setimage/{prodID}/{imageID}', [ProductsController::class, 'setMainImage']);
        Route::post('products/setchart/{prodID}', [ProductsController::class, 'setChartImage']);
        Route::get('products/unsetchart/{prodID}', [ProductsController::class, 'unsetChartImage']);
        Route::get('products/delete/image/{id}', [ProductsController::class, 'deleteImage']);
        Route::post('products/linktags/{id}', [ProductsController::class, 'linkTags']);
        Route::post('products/insert', [ProductsController::class, 'insert']);
        Route::get('products/edit/{id}', [ProductsController::class, 'edit']);
        Route::post('products/update', [ProductsController::class, 'update']);


        //Users
        Route::get('users/show/all', [UsersController::class, 'home']);
        Route::get('users/show/latest', [UsersController::class, 'latest']);
        Route::get('users/show/top', [UsersController::class, 'top']);
        Route::get('users/edit/{id}', [UsersController::class, 'edit']);
        Route::get('users/add', [UsersController::class, 'add']);
        Route::get('users/profile/{id}', [UsersController::class, 'profile']);
        Route::post('users/insert', [UsersController::class, 'insert']);
        Route::post('users/update', [UsersController::class, 'update']);


        //Website Info
        Route::get('website/slides', [WebsiteInfoController::class, 'slides']);
        Route::post('website/slides/add/', [WebsiteInfoController::class, 'addSlide']);
        Route::get('website/slides/delete/{id}', [WebsiteInfoController::class, 'deleteSlide']);
        Route::get('website/info', [WebsiteInfoController::class, 'loadWebsiteInfo']);
        Route::post('website/info/set', [WebsiteInfoController::class, 'setWebsiteInfo']);
        Route::get('website/aboutus', [WebsiteInfoController::class, 'loadAboutus']);
        Route::post('website/aboutus', [WebsiteInfoController::class, 'setAboutus']);
        Route::get('website/delivery_policy', [WebsiteInfoController::class, 'loadDeliveryPolicy']);
        Route::post('website/delivery_policy', [WebsiteInfoController::class, 'setDeliveryPolicy']);
        Route::get('website/payment_policy', [WebsiteInfoController::class, 'loadPaymentPolicy']);
        Route::post('website/payment_policy', [WebsiteInfoController::class, 'setPaymentPolicy']);

        //Payment Options
        Route::get('paymentoptions/show', [PaymentOptionsController::class, 'home']);
        Route::get('paymentoptions/toggle/{id}', [PaymentOptionsController::class, 'toggle']);

        //Areas
        Route::get('drivers/show', [DriversController::class, 'home']);
        Route::get('drivers/edit/{id}', [DriversController::class, 'edit']);
        Route::get('drivers/toggle/{id}', [DriversController::class, 'toggle']);
        Route::post('drivers/insert', [DriversController::class, 'insert']);
        Route::post('drivers/update', [DriversController::class, 'update']);

        //Areas
        Route::get('areas/show', [AreasController::class, 'home']);
        Route::get('areas/edit/{id}', [AreasController::class, 'edit']);
        Route::get('areas/toggle/{id}', [AreasController::class, 'toggle']);
        Route::post('areas/insert', [AreasController::class, 'insert']);
        Route::post('areas/update', [AreasController::class, 'update']);

        //Locations
        Route::get('locations/show', [LocationsController::class, 'home']);
        Route::get('locations/edit/{id}', [LocationsController::class, 'edit']);
        Route::get('locations/toggle/{id}', [LocationsController::class, 'toggle']);
        Route::post('locations/insert', [LocationsController::class, 'insert']);
        Route::post('locations/update', [LocationsController::class, 'update']);

        //Categories
        Route::get('categories/show', [CategoriesController::class, 'home']);
        Route::get('categories/edit/{id}', [CategoriesController::class, 'editCategory']);
        Route::get('subcategories/edit/{id}', [CategoriesController::class, 'editSubCategory']);
        Route::post('categories/insert', [CategoriesController::class, 'insertCategory']);
        Route::post('subcategories/insert', [CategoriesController::class, 'insertSubCategory']);
        Route::post('categories/update', [CategoriesController::class, 'updateCategory']);
        Route::post('subcategories/update', [CategoriesController::class, 'updateSubCategory']);

        //Dashboard users
        Route::get("dash/users/all", [DashUsersController::class, 'index']);
        Route::post("dash/users/insert", [DashUsersController::class, 'insert']);
        Route::get("dash/users/edit/{id}", [DashUsersController::class, 'edit']);
        Route::post("dash/users/update", [DashUsersController::class, 'update']);

        Route::get('logout', [HomeController::class, 'logout'])->name('adminLogout');
        Route::get('/home', [HomeController::class, 'index']);
        Route::get('/', [HomeController::class, 'index'])->name('adminHome');
    });

    Route::get('/login', [HomeController::class, 'login'])->name('adminLogin');
    Route::post('/login', [HomeController::class, 'authenticate']);
});
