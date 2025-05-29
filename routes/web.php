<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\MyProductsController;
use App\Http\Controllers\SelfServiceController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\SgdController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Lookup Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome')->name('home');
Route::get('/lookup/{serial}', [SelfServiceController::class, 'show'])
    ->name('lookup.result');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
// Route::get('/dashboard', fn() => view('dashboard'))
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {



    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/sgd', [SgdController::class, 'index'])->name('sgd.index');
    Route::get('/sgd/data', [SgdController::class, 'data'])->name('sgd.data');

    //
    // USERS
    //
    Route::get('/create-user',            [UsersController::class, 'create'])->name('users.create');
    Route::post('/users',                 [UsersController::class, 'store'])->name('users.store');
    Route::get('/users',                  [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/data',             [UsersController::class, 'data'])->name('users.data');
    Route::get('/users/{user}/edit',      [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',           [UsersController::class, 'update'])->name('users.update');
    Route::get('/users/{user}/permissions', [UsersController::class, 'permissions'])
        ->name('users.permissions');
    Route::get('/users/by-nin/{nin}', [UsersController::class, 'findByNin']);
    Route::get('/users/by-phone/{phone}', function ($phone) {
        $user = \App\Models\User::where('phone_number', $phone)->first();
        return response()->json($user ? ['id' => $user->id] : []);
    });
    Route::get('/users/by-email/{email}', function ($email) {
        $user = \App\Models\User::where('email', $email)->first();
        return response()->json($user ? ['id' => $user->id] : []);
    });

    //
    // AJAX: LGA & Branch loading
    //
    Route::get('/state/{stateId}/lgas',           [UsersController::class, 'getLgas']);
    Route::get('/business/{businessId}/branches', [UsersController::class, 'getBranches']);

    //
    // ROLES & PERMISSIONS
    //
    Route::get('/roles/{roleName}/permissions', [RoleController::class, 'getPermissions'])
        ->name('roles.permissions');

    //
    // CATEGORIES
    //
    Route::get('/categories',            [ProductCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data',       [ProductCategoryController::class, 'data'])->name('categories.data');
    Route::post('/categories',           [ProductCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [ProductCategoryController::class, 'show'])->name('categories.show');
    Route::put('/categories/{category}', [ProductCategoryController::class, 'update'])->name('categories.update');

    //
    // BUSINESSES
    //
    Route::get('/businesses',              [BusinessController::class, 'index'])->name('businesses.index');
    Route::get('/businesses/data',         [BusinessController::class, 'data'])->name('businesses.data');
    Route::get('/businesses/create',       [BusinessController::class, 'create'])->name('businesses.create');
    Route::post('/businesses',             [BusinessController::class, 'store'])->name('businesses.store');
    Route::get('/businesses/{uuid}/edit',  [BusinessController::class, 'edit'])->name('businesses.edit');
    Route::put('/businesses/{uuid}',       [BusinessController::class, 'update'])->name('businesses.update');

    // 
    Route::get('/api/businesses/{business}/branches', function (\App\Models\Business $business) {
        abort_unless($business->owner_id === auth()->id(), 403);
        return $business->branches()->select('id', 'branch_name')->get();
    })->middleware('auth');

    //
    // BRANCHES
    //
    Route::get('/branches',               [BranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/data',          [BranchController::class, 'data'])->name('branches.data');
    Route::get('/branches/create',        [BranchController::class, 'create'])->name('branches.create');
    Route::post('/branches',              [BranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{uuid}/edit',   [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('/branches/{uuid}',        [BranchController::class, 'update'])->name('branches.update');

    //
    // MY PRODUCTS
    //
    Route::get('/products/my',            [MyProductsController::class, 'index'])->name('products.my');
    Route::get('/products/my/data',       [MyProductsController::class, 'data'])->name('products.my.data');
    Route::post('/products/transfer-batch', [MyProductsController::class, 'transferBatch'])
        ->name('products.my.transfer');

    //
    // PRODUCTS (bulk upload support must come *before* the catch‐all show route)
    //
    Route::get('/products',               [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/data',          [ProductController::class, 'data'])->name('products.data');
    Route::get('/products/create',        [ProductController::class, 'create'])->name('products.create');

    // —— Bulk upload form & submit ——
    Route::get('/products/upload',        [ProductController::class, 'uploadForm'])
        ->name('products.upload.form');
    Route::post('/products/upload',       [ProductController::class, 'upload'])
        ->name('products.upload');

    // Standard CRUD
    Route::post('/products',              [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{uuid}/edit',   [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{uuid}',        [ProductController::class, 'update'])->name('products.update');

    // Reporting / resolving / transferring
    Route::post('/products/{product}/report',  [ProductController::class, 'report'])->name('products.report');
    Route::post('/products/{product}/resolve', [ProductController::class, 'resolve'])->name('products.resolve');
    Route::post('/products/{product}/transfer', [ProductController::class, 'transfer'])->name('products.transfer');

    // Catch‐all show must come *after* /create, /upload, /{uuid}/edit, etc.
    Route::get('/products/{uuid}',        [ProductController::class, 'show'])
        ->where('uuid', '[0-9A-Fa-f\-]{36}')
        ->name('products.show');

    //
    // RECEIPTS
    //
    Route::get('/receipts',               [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/receipts/data',          [ReceiptController::class, 'data'])->name('receipts.data');
    Route::get('/receipts/create',        [ReceiptController::class, 'create'])->name('receipts.create');
    Route::post('/receipts',              [ReceiptController::class, 'store'])->name('receipts.store');
    Route::get('/receipts/{receipt}',     [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/{receipt}/pdf', [ReceiptController::class, 'downloadPdf'])->name('receipts.pdf');

    //
    // PRICING
    //
    Route::get('/config/pricing',         [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/config/pricing/data',    [PricingController::class, 'data'])->name('pricing.data');
    Route::post('/config/pricing',        [PricingController::class, 'store'])->name('pricing.store');
    Route::put('/config/pricing/{pricing}',   [PricingController::class, 'update'])->name('pricing.update');
    Route::patch('/config/pricing/{pricing}/toggle', [PricingController::class, 'toggle'])->name('pricing.toggle');

    //
    // WALLET
    //
    Route::get('/wallet',                [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/data',           [WalletController::class, 'data'])->name('wallet.data');
    Route::post('/wallet/initialize',    [WalletController::class, 'initialize'])->name('wallet.initialize');
    Route::get('/wallet/callback',       [WalletController::class, 'callback'])->name('wallet.callback');

    //
    // PROFILE
    //
    Route::get('/profile',               [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile',              [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password',     [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

require __DIR__ . '/auth.php';
