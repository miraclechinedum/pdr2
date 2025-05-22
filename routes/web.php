<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\MyProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Users
    Route::get('/create-user', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');

    Route::get('/users/{user}/edit',   [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',        [UsersController::class, 'update'])->name('users.update');


    // AJAX data for DataTables
    Route::get('/users/data', [UsersController::class, 'data'])->name('users.data');

    // Permissions AJAX (for the modal)
    Route::get('/users/{user}/permissions', [UsersController::class, 'permissions'])
        ->name('users.permissions');

    Route::get('/users/by-phone/{phone}', function ($phone) {
        $user = \App\Models\User::where('phone_number', $phone)->first();
        return response()->json($user ? ['id' => $user->id] : []);
    });

    Route::get('/users/by-email/{email}', function ($email) {
        $user = \App\Models\User::where('email', $email)->first();
        return response()->json($user ? ['id' => $user->id] : []);
    });

    // AJAX: LGA and Branch loading
    Route::get('/state/{stateId}/lgas', [UsersController::class, 'getLgas']);
    Route::get('/business/{businessId}/branches', [UsersController::class, 'getBranches']);

    // (Optional) dynamic permissions via AJAX
    Route::get('/roles/{roleName}/permissions', [RoleController::class, 'getPermissions']);

    // Products
    // Category management
    Route::get('/categories',               [ProductCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data',          [ProductCategoryController::class, 'data'])->name('categories.data');
    Route::post('/categories',              [ProductCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}',    [ProductCategoryController::class, 'show'])->name('categories.show');
    Route::put('/categories/{category}',    [ProductCategoryController::class, 'update'])->name('categories.update');

    // Businesses
    Route::get('businesses',          [BusinessController::class, 'index'])->name('businesses.index');
    Route::get('businesses/data',     [BusinessController::class, 'data'])->name('businesses.data');
    Route::get('businesses/create',   [BusinessController::class, 'create'])->name('businesses.create');
    Route::post('businesses',         [BusinessController::class, 'store'])->name('businesses.store');
    Route::get('businesses/{uuid}/edit', [BusinessController::class, 'edit'])->name('businesses.edit');
    Route::put('businesses/{uuid}',   [BusinessController::class, 'update'])->name('businesses.update');

    // Branches
    Route::get('branches',            [BranchController::class, 'index'])->name('branches.index');
    Route::get('branches/data',       [BranchController::class, 'data'])->name('branches.data');
    Route::get('branches/create',     [BranchController::class, 'create'])->name('branches.create');
    Route::post('branches',           [BranchController::class, 'store'])->name('branches.store');
    Route::get('branches/{uuid}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('branches/{uuid}',     [BranchController::class, 'update'])->name('branches.update');

    // “My Products” list & AJAX data
    Route::get('/products/my',           [MyProductsController::class, 'index'])
        ->name('products.my');
    Route::post('/products/transfer-batch', [MyProductsController::class, 'transferBatch'])
        ->name('products.my.transfer');

    Route::get('/products/my/data',      [MyProductsController::class, 'data'])
        ->name('products.my.data');

    // Batch transfer endpoint
    Route::post('/products/transfer-batch', [MyProductsController::class, 'transferBatch'])
        ->name('products.my.transfer');

    // Products
    Route::get('/products',             [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/data',        [ProductController::class, 'data'])->name('products.data');
    Route::get('/products/create',      [ProductController::class, 'create'])->name('products.create');
    Route::post('/products',            [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{uuid}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{uuid}',      [ProductController::class, 'update'])->name('products.update');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    Route::post('/products/{product}/report', [ProductController::class, 'report'])
        ->name('products.report');

    Route::post('/products/{product}/resolve', [ProductController::class, 'resolve'])
        ->name('products.resolve');

    Route::post('/products/{product}/transfer', [ProductController::class, 'transfer'])
        ->name('products.transfer');

    Route::get('receipts',             [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('receipts/create',      [ReceiptController::class, 'create'])->name('receipts.create');
    Route::post('receipts',            [ReceiptController::class, 'store'])->name('receipts.store');
    Route::get('receipts/data',   [ReceiptController::class, 'data'])->name('receipts.data');
    Route::get('receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');

    Route::get('receipts/{receipt}/pdf', [ReceiptController::class, 'downloadPdf'])->name('receipts.pdf');


    Route::get('/users/by-nin/{nin}', [UsersController::class, 'findByNin']);
});

require __DIR__ . '/auth.php';
